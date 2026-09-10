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
    local expect="${3:-200}"
    local code

    code="$(curl -sf -o /dev/null -w '%{http_code}' --max-time 10 "$url" 2>/dev/null || echo '000')"

    if [[ "$code" == "$expect" ]]; then
        echo "[OK]   $label (HTTP $code)"
    else
        echo "[FAIL] $label (expected $expect, got $code)"
        FAIL=1
    fi
}

echo "Health probe: $BASE_URL"
echo "────────────────────────────────────────"

check "Laravel /up endpoint"   "$BASE_URL/up"   "200"
check "Login page loads"       "$BASE_URL/login" "200"
check "Setup wizard accessible" "$BASE_URL/setup" "200"

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
