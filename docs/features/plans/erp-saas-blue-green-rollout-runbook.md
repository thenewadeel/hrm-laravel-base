# HRM Laravel Base — Blue/Green Deployment Runbook

**Scope:** Phase 1 MVP rollout: Organization Management → Employee Enrollment.
**Owner:** DevOps / Release engineer
**Slack channel / notification hooks:** `NOTIFY_WEBHOOK_URL` (see `.env.production.example`)

---

## 0. Prerequisites

- Server running MySQL 8.0, PHP 8.4+, PHP-FPM (Unix socket or TCP pool), Nginx, Node 20+.
- `scripts/deploy-bluegreen.sh` present and executable; `scripts/hrm-queue-worker.service`
  installed and enabled.
- Git access to the repository (shallow clone capability) and deploy key configured.
- Production secrets in `/opt/hrm/shared/.env` (copied from `.env.production.example`,
  generated via `php artisan key:generate --force`). **Never** commit `.env.production`.

---

## 1. Install the queue worker (systemd)

```bash
sudo cp scripts/hrm-queue-worker.service /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable --now hrm-queue-worker.service
sudo systemctl status hrm-queue-worker   # verify running
```

Scheduler (one per server): add a cron entry to run the live release's scheduler.

```cron
* * * * * www-data cd /opt/hrm/current && php artisan schedule:run >> /dev/null 2>&1
```

---

## 2. Standard deploy — Main branch

```bash
scripts/deploy-bluegreen.sh prepare main          # build release, composer, npm, caches
scripts/deploy-bluegreen.sh migrate               # run DB migrations on GREEN (blue still live)
scripts/deploy-bluegreen.sh healthcheck           # probe GREEN (/up, artisan about); abort on failure
scripts/deploy-bluegreen.sh swap                  # atomic symlink flip — zero downtime
scripts/deploy-bluegreen.sh smoke                 # post-swap verification (login, setup, /up, manifest)
```

Expected outcomes:
- `healthcheck` prints `Healthcheck PASSED` — otherwise no swap occurs (script exits non-zero).
- `swap` is a single `ln -sfn` — sessions are NOT terminated, no 500s during the flip.
- `smoke` verifies `/up`, `/login`, `/setup`, and the Vite manifest.

Observation window: keep the previous release (target of `previous_release`) untouched for at
least N minutes (e.g. 30). Roll back immediately if monitoring alerts.

---

## 3. Rollback (instant)

```bash
scripts/deploy-bluegreen.sh rollback
```

- Flips `current` back to `previous_release`; restarts queue workers.
- **No automatic migration down.** The blueprint is forward-only. If the aborted release
  introduced schema changes, a human must review and author a manual DOWN (see §5).

---

## 4. Release traceability

Every release directory contains a `RELEASE` marker:

```
RELEASE_TS=2026-09-15T090000Z
GIT_REF=main
GIT_SHA=a1b2c3d
```

CI tags builds as `vYYYY.MM.DD.<build>`; pass the tag to `prepare` for pinned releases.

---

## 5. Migrations policy (forward-only)

- `up()` / `down()` must be idempotent and reversible **by hand**.
- When modifying a column, re-declare **all existing column attributes** in the migration or
  they will be dropped (see checklist below).
- Never auto-rollback on a failed deploy. Keep the failed release directory and investigate
  before writing a manual DOWN.
- Migration review checklist:
  - [ ] Column-alter migrations include every existing attribute (type, nullable, default, index).
  - [ ] New tables/fks have explicit `organization_id` for tenant scoping.
  - [ ] `up()` runs successfully on both an empty DB and the current production schema.

---

## 6. External monitoring

`scripts/health-check-probe.sh` — drop-in readiness probe for uptime monitors / load balancers:

```bash
scripts/health-check-probe.sh https://hrm.example.com   # exit 0 = healthy, exit 1 = unhealthy
```

Checks: `/up`, `/login`, `/setup` (200s), Vite manifest presence, active release marker.

---

## 7. Rollback drill (performed Day 3, staging)

1. Deploy a deliberately broken commit (e.g. remove a route) via `prepare` + `migrate` + `swap`.
2. Confirm browser/users get errors on `current`.
3. Run `scripts/deploy-bluegreen.sh rollback`.
4. Confirm instant recovery (expected < 60 s), data intact, sessions preserved.

---

## 8. Go/No-Go for MVP production release

| # | Check | Result |
|---|-------|--------|
| 1 | `composer test` green (PHPUnit on MySQL, memory=1G) | ☐ |
| 2 | `vendor/bin/pint --test` passes | ☐ |
| 3 | `npm run build` produces hashed manifest; `@vite` pages render post-swap | ☐ |
| 4 | Blue/Green swap exercised 2× with no logged-out sessions and no 500s during flip | ☐ |
| 5 | Rollback drill: forced failure → flipped back in <60s, data intact | ☐ |
| 6 | Migrations forward-only; column-alter migrations re-declare full attributes | ☐ |
| 7 | Two-tenant isolation vertical slice passes (see `MvpVerticalSliceIsolationTest`) | ☐ |
| 8 | Health probe reachable from monitoring; queue worker supervised; `.env.production` secret-managed | ☐ |
| 9 | `deploy-production-legacy.sh` retained as emergency escape hatch | ☐ |

**Go:** all boxes checked. **No-Go:** any box unchecked — re-scope and re-run the affected check before release.