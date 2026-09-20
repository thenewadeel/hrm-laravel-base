#!/bin/bash
# =============================================================================
# health-check-probe.sh — External readiness probe for HRM Laravel Base
# =============================================================================
#
# Usage:
#   scripts/health-check-probe.sh [BASE_URL]
#
# Exit codes:
#   0  All checks passed
#   1  One or more checks failed
#
# Intended for external uptime monitoring (Uptime Kuma, Cronitor, etc.),
# systemd watchdog, or load-balancer health targets.
# =============================================================================

set -euo pipefail

BASE_URL="${1:-${APP_URL:-http://localhost}}"
FAIL=0

check() {
    local label="$1"
    local url="$2"
    shift 2
    local expected="$*"

    local code
    code="$(curl -sf -o /dev/null -w '%{http_code}' --max-time 10 "$url" 2>/dev/null || echo '000')"

    local ok=false
    for expect in "$@"; do
        [[ "$code" == "$expect" ]] && ok=true
    done

    if $ok; then
        echo "[OK]   $label (HTTP $code)"
    else
        echo "[FAIL] $label (expected [$expected], got $code)"
        FAIL=1
    fi
}

echo "Health probe: $BASE_URL"
echo "────────────────────────────────────────"

check "Laravel /up endpoint"    "$BASE_URL/up"     "200"
check "Login page loads"        "$BASE_URL/login"  "200"
check "Setup wizard accessible" "$BASE_URL/setup"  "200" "302"

# Livewire script — must reach PHP, not a missing static file (vhost try_files).
# The debug build serves livewire.js, production serves livewire.min.js.
lw_js="$BASE_URL/livewire/livewire.js"
if ! curl -sf -o /dev/null "$lw_js" 2>/dev/null; then
    lw_js="$BASE_URL/livewire/livewire.min.js"
fi
check "Livewire script loads"   "$lw_js"             "200"

# Vite manifest
if [[ -f "/opt/hrm/current/public/build/manifest.json" ]]; then
    echo "[OK]   Vite manifest present in current release"
else
    echo "[WARN] Vite manifest not found in current release"
fi

# Release marker
if [[ -f "/opt/hrm/shared/last_release" ]]; then
    RELEASE="$(cat /opt/hrm/shared/last_release)"
    echo "[OK]   Active release: $RELEASE"
else
    echo "[WARN] No active release marker found"
fi

echo "────────────────────────────────────────"

if [[ "$FAIL" -eq 0 ]]; then
    echo "Result: HEALTHY"
    exit 0
else
    echo "Result: UNHEALTHY"
    exit 1
fi
