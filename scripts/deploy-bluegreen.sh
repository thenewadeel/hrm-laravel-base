#!/bin/bash
# =============================================================================
# deploy-bluegreen.sh — Blue/Green release orchestrator for HRM Laravel Base
# =============================================================================
#
# Release-directory layout:
#   /opt/hrm/
#     ├── releases/<timestamp>/       ← immutable checkout per deploy
#     ├── shared/.env                 ← production secrets (symlinked into releases)
#     ├── shared/storage/             ← logs, uploaded files (persist across swaps)
#     ├── shared/public/build/        ← Vite manifest (optional, built per-release)
#     └── current                     ← symlink Nginx/PHP-FPM points at
#
# Subcommands:
#   prepare <ref>   Build a new release from a git ref (branch, tag, SHA)
#   migrate         Run `artisan migrate --force` on the GREEN (unreleased) copy
#   healthcheck     Probe the GREEN release for readiness — abort on failure
#   swap            Atomic symlink flip from BLUE → GREEN
#   smoke           Post-swap verification against live `current`
#   rollback        Instant symlink flip back to the previous release
#
# All paths are overridable via env vars (see header of each function).
# =============================================================================

set -euo pipefail

# ─── Configuration ──────────────────────────────────────────────────────────────
: "${BASE_DIR:=/opt/hrm}"
: "${SHARED_DIR:=$BASE_DIR/shared}"
: "${RELEASES_DIR:=$BASE_DIR/releases}"
: "${CURRENT_LINK:=$BASE_DIR/current}"
: "${GREEN_HEALTH_URL:=}"
: "${APP_USER:=nginx}"
: "${PHP_BIN:=php}"
: "${KEEP_RELEASES:=5}"

RELEASE_TS="$(date -u +%Y-%m-%dT%H%M%SZ)"
RELEASE_DIR="$RELEASES_DIR/$RELEASE_TS"

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

log()   { echo -e "${BLUE}[$(date -u +%H:%M:%S)]${NC} $*"; }
ok()    { echo -e "${GREEN}[OK]${NC} $*"; }
warn()  { echo -e "${YELLOW}[WARN]${NC} $*"; }
err()   { echo -e "${RED}[ERR]${NC} $*"; exit 1; }

# ─── env validation ─────────────────────────────────────────────────────────────
# Preflight a release's .env for the settings that historically broke blue/green
# deploys: session persistence across swaps, cookie/domain mismatch (419), and
# debug leftovers. Pass the path to the release .env (which is a symlink to
# shared/.env in practice). Purely advisory for env values that are legitimate
# in some setups — we only hard-fail on the ones that always break.
validate_env() {
    local env_file="${1:?Usage: validate_env <release/.env>}"
    [[ -f "$env_file" ]] || { warn "No .env found at $env_file; skipping env validation"; return 1; }

    local pass=true
    local val

    # APP_KEY must be a real generated key (never the placeholder)
    val="$(grep -E '^APP_KEY=' "$env_file" | head -1 | cut -d= -f2- || true)"
    if [[ -z "$val" ]]; then
        warn "APP_KEY is missing — run: php artisan key:generate"
        pass=false
    elif [[ "$val" == *"YOUR_GENERATED_APP_KEY_HERE"* ]]; then
        warn "APP_KEY is still the placeholder — run: php artisan key:generate"
        pass=false
    fi

    # APP_DEBUG must be false in production (leaks stack traces + dev assets)
    val="$(grep -E '^APP_DEBUG=' "$env_file" | head -1 | cut -d= -f2- || true)"
    if [[ "$val" == "true" ]]; then
        warn "APP_DEBUG=true in production — set APP_DEBUG=false"
        pass=false
    fi

    # SESSION_DRIVER must be database so sessions survive release swaps
    val="$(grep -E '^SESSION_DRIVER=' "$env_file" | head -1 | cut -d= -f2- || true)"
    if [[ "$val" != "database" ]]; then
        warn "SESSION_DRIVER=$val (expected database) — file/cookie sessions cause random logouts after swap"
        pass=false
    fi

    # SESSION_DOMAIN=null for a single host; if set it must match APP_URL host
    local domain app_url
    domain="$(grep -E '^SESSION_DOMAIN=' "$env_file" | head -1 | cut -d= -f2- || true)"
    app_url="$(grep -E '^APP_URL=' "$env_file" | head -1 | cut -d= -f2- || true)"
    if [[ -n "$domain" && "$domain" != "null" ]]; then
        local app_host
        app_host="$(printf '%s' "$app_url" | sed -E 's#^https?://([^:/]+).*#\1#')"
        if [[ "$domain" != "$app_host" ]]; then
            warn "SESSION_DOMAIN=$domain does not match APP_URL host ($app_host) — sessions break (HTTP 419). Set SESSION_DOMAIN=null or keep them in sync."
            pass=false
        fi
    fi

    if [[ "$pass" == "true" ]]; then
        ok "Env preflight PASSED ($env_file)"
    else
        warn "Env preflight found issues in $env_file — review the warnings above before going live"
    fi
}

# ─── prepare ────────────────────────────────────────────────────────────────────
prepare() {
    local ref="${1:?Usage: $0 prepare <git-ref>}"
    local release="$RELEASE_DIR"

    log "Preparing release $RELEASE_TS from ref: $ref"

    # Ensure base dirs exist
    mkdir -p "$RELEASES_DIR" "$SHARED_DIR/storage"
    mkdir -p "$SHARED_DIR/storage/framework/{sessions,views,cache}"
    mkdir -p "$SHARED_DIR/storage/logs"

    # Checkout the full repo into the new release directory
    git clone --depth 1 --branch "$ref" . "$release" 2>/dev/null \
        || git clone --depth 1 --branch "$ref" --origin origin . "$release" 2>/dev/null \
        || {
            # Fallback: shallow clone using current remote origin
            git remote get-url origin > /dev/null 2>&1 || err "No git remote origin found"
            local origin
            origin="$(git remote get-url origin)"
            git clone --depth 1 --branch "$ref" "$origin" "$release"
        }

    # Write release version marker for traceability
    local short_sha
    short_sha="$(cd "$release" && git rev-parse --short HEAD 2>/dev/null || echo 'unknown')"
    cat > "$release/RELEASE" <<EOF
RELEASE_TS=$RELEASE_TS
GIT_REF=$ref
GIT_SHA=$short_sha
EOF
    log "Release $RELEASE_TS ($short_sha) checked out"

    # Composer install (production)
    log "Installing Composer dependencies (--no-dev --optimize-autoloader)"
    (cd "$release" && composer install \
        --no-dev \
        --optimize-autoloader \
        --no-interaction \
        --no-progress \
        --prefer-dist)

    # Node dependencies + Vite build
    log "Installing Node dependencies and building assets"
    (cd "$release" && npm ci --prefer-offline 2>/dev/null || npm install --prefer-offline)
    (cd "$release" && npm run build)

    # Symlink shared resources into the new release
    log "Symlinking shared resources"
    ln -sfn "$SHARED_DIR/.env"          "$release/.env"
    ln -sfn "$SHARED_DIR/storage"       "$release/storage"

    # Preflight the shared env before building/caching anything
    validate_env "$release/.env"

    # Persist the freshly-built Vite assets into the shared build dir, then
    # symlink it so every release serves the same hashed manifest + assets.
    # (Without the copy, a first deploy would delete the built assets and leave
    #  only an empty shared dir → missing manifest.json / no CSS or JS.)
    mkdir -p "$SHARED_DIR/public/build"
    cp -a "$release/public/build/." "$SHARED_DIR/public/build/"
    rm -rf "$release/public/build"
    ln -sfn "$SHARED_DIR/public/build"  "$release/public/build"

    # Storage link
    (cd "$release" && $PHP_BIN artisan storage:link --force 2>/dev/null) || true

    # Cache bootstrap (config, routes, events, views)
    log "Optimizing application"
    (cd "$release" && $PHP_BIN artisan config:cache)
    (cd "$release" && $PHP_BIN artisan route:cache)
    (cd "$release" && $PHP_BIN artisan event:cache)
    (cd "$release" && $PHP_BIN artisan view:cache 2>/dev/null) || true

    # Set ownership
    chown -R "$APP_USER:$APP_USER" "$release" "$SHARED_DIR"

    ok "Release $RELEASE_TS prepared at $release"

    # Garbage-collect old releases (keep last $KEEP_RELEASES + current)
    gc_old_releases
}

# ─── migrate ────────────────────────────────────────────────────────────────────
migrate() {
    local green_release
    green_release="$(find_latest_release)"

    [[ -z "$green_release" ]] && err "No release found. Run 'prepare' first."

    log "Running migrations on green release: $green_release"
    log "(Blue release is still live at $CURRENT_LINK)"

    (cd "$green_release" && $PHP_BIN artisan migrate --force)

    ok "Migrations complete on green release"
}

# ─── healthcheck ────────────────────────────────────────────────────────────────
healthcheck() {
    local green_release
    green_release="$(find_latest_release)"
    [[ -z "$green_release" ]] && err "No release found."

    log "Running healthcheck against green release: $green_release"

    local pass=true

    # 0. Env preflight (session/domain/debug — the classic 419 & asset breakers)
    validate_env "$green_release/.env"

    # 1. Maintenance-mode check (Laravel has no `artisan up --check`; the flag
    #    is the presence of storage/framework/down)
    if [[ -f "$green_release/storage/framework/down" ]]; then
        warn "Maintenance mode is ACTIVE (storage/framework/down) — clearing"
        if ! (cd "$green_release" && $PHP_BIN artisan up) > /dev/null 2>&1; then
            warn "Failed to disable maintenance mode"
            pass=false
        fi
    else
        ok "Maintenance mode: not active"
    fi

    # 2. Check PHP-FPM / Nginx health endpoint on the green URL
    local green_url http_code
    green_url="$(resolve_green_url "$green_release")"
    http_code="$(curl -sf -o /dev/null -w '%{http_code}' "$green_url/up" 2>/dev/null || echo '000')"
    if [[ "$http_code" == "200" ]]; then
        ok "Green health probe ($green_url/up): HTTP $http_code"
    else
        warn "Green health probe ($green_url/up): HTTP $http_code — FAIL"
        pass=false
    fi

    # 3. artisan about — confirms DB connection
    if (cd "$green_release" && $PHP_BIN artisan about) > /dev/null 2>&1; then
        ok "artisan about: passed"
    else
        warn "artisan about: FAILED"
        pass=false
    fi

    # 4. Livewire script — must route through PHP (nginx try_files fallback),
    #    otherwise the dashboard has no JS/Alpine (blank interactions)
    local lw_url lw_code
    lw_url="$green_url/livewire/livewire.js"
    if ! curl -sf -o /dev/null "$lw_url" 2>/dev/null; then
        lw_code="$(curl -sf -o /dev/null -w '%{http_code}' "$green_url/livewire/livewire.min.js" 2>/dev/null || echo '000')"
        lw_url="$green_url/livewire/livewire.min.js"
    else
        lw_code=200
    fi
    if [[ "$lw_code" == "200" ]]; then
        ok "Livewire script ($lw_url): HTTP $lw_code"
    else
        warn "Livewire script ($lw_url): HTTP $lw_code — check vhost try_files fallback → /index.php"
        pass=false
    fi

    if [[ "$pass" == "true" ]]; then
        ok "Healthcheck PASSED — green is ready to go live"
    else
        err "Healthcheck FAILED — aborting swap. Review the green release before retrying."
    fi
}

# ─── swap ───────────────────────────────────────────────────────────────────────
swap() {
    local new_release
    new_release="$(find_latest_release)"
    [[ -z "$new_release" ]] && err "No release found."

    local old_release
    old_release="$(readlink -f "$CURRENT_LINK" 2>/dev/null || echo '')"

    log "Swapping current → $new_release"
    log "(previous live: ${old_release:-none})"

    # Atomic symlink swap (Nginx/PHP-FPM resolves $CURRENT_LINK per request)
    ln -sfn "$new_release" "$CURRENT_LINK"

    ok "Swap complete — current now points to $new_release"

    # Restart queue workers so they pick up the new code
    log "Restarting queue workers"
    (cd "$new_release" && $PHP_BIN artisan queue:restart) 2>/dev/null || warn "queue:restart failed (workers may need manual restart)"

    # Touch release marker in shared for rollback visibility
    echo "$new_release" > "$SHARED_DIR/last_release"
    [[ -n "$old_release" ]] && echo "$old_release" > "$SHARED_DIR/previous_release"

    ok "Swap done. Previous release kept for rollback."

    chown -R "$APP_USER:$APP_USER" "$SHARED_DIR" "$CURRENT_LINK"
}

# ─── smoke ──────────────────────────────────────────────────────────────────────
smoke() {
    log "Running post-swap smoke tests against $CURRENT_LINK"

    local base_url
    base_url="$(resolve_app_url "$CURRENT_LINK/.env")"
    local pass=true

    # 1. /up health endpoint
    local http_code
    http_code="$(curl -sf -o /dev/null -w '%{http_code}' "$base_url/up" 2>/dev/null || echo '000')"
    if [[ "$http_code" == "200" ]]; then
        ok "/up: HTTP $http_code"
    else
        warn "/up: HTTP $http_code"
        pass=false
    fi

    # 2. Login page loads
    http_code="$(curl -sf -o /dev/null -w '%{http_code}' "$base_url/login" 2>/dev/null || echo '000')"
    if [[ "$http_code" == "200" ]]; then
        ok "/login: HTTP $http_code"
    else
        warn "/login: HTTP $http_code"
        pass=false
    fi

    # 3. Setup page loads (org onboarding — part of MVP slice)
    http_code="$(curl -sf -o /dev/null -w '%{http_code}' "$base_url/setup" 2>/dev/null || echo '000')"
    if [[ "$http_code" =~ ^(200|302)$ ]]; then
        ok "/setup: HTTP $http_code"
    else
        warn "/setup: HTTP $http_code"
        pass=false
    fi

    # 4. Livewire script serves through PHP (nginx try_files fallback)
    local lw_url lw_code
    lw_url="$base_url/livewire/livewire.js"
    if ! curl -sf -o /dev/null "$lw_url" 2>/dev/null; then
        lw_url="$base_url/livewire/livewire.min.js"
    fi
    lw_code="$(curl -sf -o /dev/null -w '%{http_code}' "$lw_url" 2>/dev/null || echo '000')"
    if [[ "$lw_code" == "200" ]]; then
        ok "Livewire script ($lw_url): HTTP $lw_code"
    else
        warn "Livewire script ($lw_url): HTTP $lw_code — check vhost try_files fallback"
        pass=false
    fi

    # 5. Vite manifest is accessible
    if [[ -f "$CURRENT_LINK/public/build/manifest.json" ]]; then
        ok "Vite manifest exists in current release"
    else
        warn "Vite manifest missing in current release"
        pass=false
    fi

    if [[ "$pass" == "true" ]]; then
        ok "Smoke tests PASSED"
    else
        warn "Some smoke tests failed — review before keeping the release live"
    fi
}

# ─── rollback ───────────────────────────────────────────────────────────────────
rollback() {
    local previous
    previous="$(cat "$SHARED_DIR/previous_release" 2>/dev/null || echo '')"

    [[ -z "$previous" ]] && err "No previous release found. Cannot rollback."
    [[ ! -d "$previous" ]] && err "Previous release dir does not exist: $previous"

    log "ROLLING BACK: current → $previous"

    ln -sfn "$previous" "$CURRENT_LINK"

    ok "Rollback complete — current now points to $previous"

    # Restart queue workers on the rolled-back release
    (cd "$previous" && $PHP_BIN artisan queue:restart) 2>/dev/null || warn "queue:restart failed"

    echo "$previous" > "$SHARED_DIR/last_release"
    chown -R "$APP_USER:$APP_USER" "$SHARED_DIR" "$CURRENT_LINK"

    ok "Rollback done. The failed release is still in $RELEASES_DIR for investigation."
}

# ─── helpers ────────────────────────────────────────────────────────────────────
find_latest_release() {
    ls -dt "$RELEASES_DIR"/*/  2>/dev/null | head -1 | sed 's:/$::'
}

# Resolve the site base URL for smoke tests: $APP_URL env → <release>/.env
# APP_URL → http://localhost. This lets `sudo -u nginx ./deploy-bluegreen.sh
# smoke` work without exporting APP_URL on the box.
resolve_app_url() {
    [[ -n "${APP_URL:-}" ]] && { printf '%s' "$APP_URL"; return; }
    local env_file="${1:-$CURRENT_LINK/.env}"
    if [[ -f "$env_file" ]]; then
        local url
        url="$(grep -E '^APP_URL=' "$env_file" 2>/dev/null | head -1 | cut -d= -f2- | tr -d '"\' || true)"
        [[ -n "${url:-}" ]] && { printf '%s' "$url"; return; }
    fi
    printf '%s' "http://localhost"
}

# Resolve the URL to probe for green readiness: explicit $GREEN_HEALTH_URL →
# the app's own URL (env or .env) → the 8081 fallback.
resolve_green_url() {
    local url
    if [[ -n "${GREEN_HEALTH_URL:-}" ]]; then
        printf '%s' "$GREEN_HEALTH_URL"
        return
    fi
    url="$(resolve_app_url "${1:-$CURRENT_LINK/.env}")"
    if [[ -n "$url" && "$url" != "http://localhost" ]]; then
        printf '%s' "$url"
    else
        printf '%s' "http://127.0.0.1:8081"
    fi
}

gc_old_releases() {
    local count
    count="$(ls -d "$RELEASES_DIR"/*/ 2>/dev/null | wc -l)"

    if [[ "$count" -le "$KEEP_RELEASES" ]]; then
        return
    fi

    log "Garbage-collecting old releases (keeping last $KEEP_RELEASES)"
    ls -dt "$RELEASES_DIR"/*/ | tail -n +"$((KEEP_RELEASES + 1))" | while read -r dir; do
        [[ -d "$dir" ]] || continue
        # Never delete the currently live release
        [[ "$(readlink -f "$CURRENT_LINK")" == "$(readlink -f "$dir")" ]] && continue
        log "Removing old release: $dir"
        rm -rf "$dir"
    done
}

# ─── main ───────────────────────────────────────────────────────────────────────
usage() {
    cat <<EOF
Usage: $0 <command> [args]

Commands:
  prepare <git-ref>   Check out <ref> into releases/<timestamp>, build, cache, link shared resources
  migrate             Run artisan migrate --force on the green (latest) release
  healthcheck         Probe green release for readiness — aborts on any failure
  swap                Atomic symlink flip: current → latest release (zero-downtime)
  smoke               Post-swap verification (login, setup, /up, Vite manifest)
  rollback            Flip current back to previous_release; restart queue workers

Environment variables:
  BASE_DIR            Root deploy dir               (default: /opt/hrm)
  APP_URL             Live site URL for smoke tests (default: from release/.env, else http://localhost)
  GREEN_HEALTH_URL    Health probe URL for green    (default: APP_URL/up, else http://127.0.0.1:8081)
  APP_USER            File ownership                (default: nginx)
  KEEP_RELEASES       Releases to retain            (default: 5)

Example deploy sequence:
  $0 prepare main
  $0 migrate
  $0 healthcheck
  $0 swap
  $0 smoke

Rollback:
  $0 rollback
EOF
    exit 1
}

[[ $# -lt 1 ]] && usage

case "$1" in
    prepare)   prepare "${2:-}" ;;
    migrate)   migrate ;;
    healthcheck) healthcheck ;;
    swap)      swap ;;
    smoke)     smoke ;;
    rollback)  rollback ;;
    *)         usage ;;
esac
