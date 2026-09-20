# MVP Rollout Plan — Running on Blue/Green (Org Mgmt → Employee Enrollment)

## Objective
Take the existing HRM Laravel Base ERP from single-server, in-place deploys to a **production-grade Blue/Green rollout** supporting continued development. **Phase 1 MVP scope** is **Organization Management through Employee Enrollment** (provision org → set up org structure → enroll employees + grant login access). Attendance, Payroll, Inventory, and Accounting remain available in the codebase but are **expanded later** as post-MVP phases.

Guiding constraint: **zero-downtime, instant-rollback deployments** using a real Blue/Green strategy, while the team keeps shipping on `main` via CI.

---

## Current State (Verified 2026-09-01)

### What runs today
- **Runtime**: Laravel 12.68, PHP 8.4/8.5, Livewire 3, Jetstream+Fortify, Sanctum. Frontend built with Vite (hashed manifest in `public/build` — good for atomic swaps).
- **DB**: SQLite in dev/test; **MySQL 8.0 fully supported** and proven by the `dusk-mysql` CI job.
- **Tenancy**: Row-scoped, single-DB multi-tenancy. `OrganizationScope` global scope + `BelongsToOrganization` trait (stamps `organization_id` on create) on ~50 models. Tenant context = persistent `users.current_organization_id` column (not session/subdomain).
- **Org setup flow**: `SetupController` -> `storeOrganization` (org + root unit + `inventory_admin` pivot), optional stores/accounts.
- **Employee enrollment**: `EmployeeController` `store()` creates `User` + `Employee` + `OrganizationUser` pivot (roles merged from position `default_roles`); also `storeWithoutUser()` (record-only) + `grantSystemAccess()`.

### Deployment gaps (from research)
| Area | Today | Required for Blue/Green |
|---|---|---|
| Deploy | `scripts/deploy-production.sh` git-pulls into live `/var/www/...` in-place | Release dir + atomic **symlink swap** |
| Rollback | Pre-deploy tarball only | **Keep previous symlink target**; flip back in seconds |
| CI | Only `dusk.yml` (2 Dusk jobs) | Add PHPUnit + Pint + `npm run build` gate; deploy job |
| Containers | None (Sail present but uninitialized) | Optional; release dir approach works without |
| Migrations | Run in-place on the live app | Run on the **inactive (green)** release before swap; use `--pretend` / pre-deploy review |
| Health | Only Laravel `/up` | Add green-vs-blue **readiness probe** gate |
| Queues | DB driver, not supervised | Supervise workers per release (systemd/supervisord) |
| Scheduler | None defined | Add per-release `schedule:run` |
| Env | No `.env.production` | Secret-managed per-release env |

---

## Target Architecture — Blue/Green Release Model

### Directory layout (per server, Nginx + PHP-FPM + MySQL + Queue worker)
```
/opt/hrm/
├── releases/
│   ├── 2026-09-14_1300Z/     # a release checkout (read-only code + vendor + build)
│   └── 2026-09-15_0900Z/
├── shared/
│   ├── .env                  # production secrets (symlinked into every release) - NOT in git
│   ├── storage/              # storage/app, logs (persist across swap)
│   └── public/build/         # optional if built per-release
└── current -> releases/2026-09-15_0900Z   # symlink Nginx/FPM points at
```

### The swap (zero downtime)
1. CI builds & tests on push to `main`.
2. Deploy process checks out the new commit into a fresh `releases/<ts>/`, installs deps, runs `npm run build`, writes `.env`/storage symlinks.
3. Run `php artisan migrate` **while `current` still points at the old release** (green release, blue live).
4. Run health/readiness probe against the **green** release (PHP-FPM on an internal port).
5. **Atomic flip**: `ln -sfn releases/<new> current` — one operation (Nginx/FPM resolves `/opt/hrm/current` each request, so the swap is instantaneous with **no downtime**).
6. Run post-swap smoke tests (login, org dashboard, employee enrollment) against `current`.
7. Keep the previous release symlinked as **rollback target** for the observation window; flip back on any error.
8. After N minutes clean: run migrations/`schedule:run` on the now-live release; **garbage-collect** releases older than ~1 week (keep last 2 for rollback).

---

## Phase 1 MVP — Scoped Deliverables (3 days)

### Scope: Org Management → Employee Enrollment
Deliver a production **Blue/Green pipeline** and validate the **core vertical slice** end-to-end:
1. **Tenant onboarding** (org mgmt): provision a new organization, root unit, admin user with org roles.
2. **Org structure**: organization units/departments, job positions (+ default roles), shifts.
3. **Employee enrollment**: create employee (with/without login), assign position + org unit + roles/shift; grant system access.
4. **Deploy**: Blue/Green release pipeline with automated migration + rollback + health gate, proven against this slice.

**Out of MVP scope (post-MVP phases):** Attendance clock-in/out, Payroll processing/loans/advances, full Inventory, Accounting vouchers/statements. These stay in the codebase/tests but are **not** part of Day-1 production validation.

### Day 1 — Release Model + CI gate (foundation)
- [x] Introduce **release-directory layout** (`/opt/hrm/releases|shared|current`) replacing the in-place git-pull in `scripts/deploy-production.sh`. Keep the old script as `scripts/deploy-production-legacy.sh` (rollback escape hatch).
  - Old script renamed via `git mv` → `scripts/deploy-production-legacy.sh`; release layout implemented inside `scripts/deploy-bluegreen.sh` (`/opt/hrm/{releases,shared,current}`, atomic `ln -sfn`).
- [x] Add **CI gate** to `.github/workflows/` (new `ci.yml`, keep `dusk.yml`):
  - [x] `composer install --no-interaction` and run **PHPUnit** (`php artisan test`) on MySQL (`phpunit.mysql.xml` service container).
  - [x] `vendor/bin/pint --test` lint gate.
  - [x] `npm ci && npm run build` (fail if Vite manifest unbuildable — job verifies `public/build/manifest.json`).
- [x] Add a **release tag/version** mechanism (git tag like `v2026.09.14.<build>`) recorded into each release dir for traceability (`RELEASE` marker written by `deploy-bluegreen.sh prepare`).
- [x] Write `.env.production.example` (committable) documenting required vars — keep real secrets out of git (`.env.production` stays ignored).

**Verification** *(requires staging box — see runbook)*: delete local release, run a local Blue/Green swap of the existing app twice; confirm the webroot symlink flip and that `/up` + a smoke login succeed before flush.

### Day 2 — Blue/Green orchestration + migration & rollback
- [x] Write **`scripts/deploy-bluegreen.sh`**:
  - [x] `prepare` -> clone/checkout commit into `releases/<ts>/`; `composer install --no-dev --optimize-autoloader`; `npm ci && npm run build`; symlink `shared/.env`, `shared/storage`, `shared/public/build`.
  - [x] `migrate` -> run `php artisan migrate --force` on the **green** release (blue still live).
  - [x] `healthcheck` -> probe green (PHP-FPM internal socket): `/up`, `artisan about`, `artisan up --check`. **Abort on failure = no swap.**
  - [x] `swap` -> `ln -sfn` flip; `queue:restart`; sends success/failure notification via hook.
  - [x] `smoke` -> post-swap checks against live `current` (login, setup/org onboarding, `/up`, Vite manifest).
  - [x] `rollback` -> `ln -sfn` back to previous release + restart workers; **no auto DOWN** on rollback (forward-only policy kept).
- [x] **Queue worker management**: systemd unit `scripts/hrm-queue.service` runs `artisan queue:work` from `current`; on swap, `queue:restart`. Scheduler cron documented in runbook.
- [x] **Migrations**: **forward-only** rule documented in runbook + `deploy-bluegreen.sh`; never auto-rollback on deploy failure.
- [x] **Database migration ordering note**: column-alter migration checklist recorded in runbook §5.

**Verification** *(requires staging box — see runbook)*: run a full blue→green deploy while a browser session stays open; confirm **the open session is never logged out** and no 500s occur during the flip. Then test rollback: introduce a deliberate deploy failure and confirm instant symlink back.

### Day 3 — MVP vertical slice hardening + Go/No-Go
- [x] **Tenant isolation checks** for the MVP slice — `tests/Feature/Organization/MvpVerticalSliceIsolationTest.php` (passed on SQLite; runs on MySQL via `phpunit.mysql.xml`):
  - [x] a new org must not see another org's employees/positions/units.
  - [x] cross-org employee `position_id`/`shift_id`/`organization_unit_id` must be rejected.
- [x] **Automated pipeline tests** that exercise the MVP slice:
  - [x] org provisioning -> root unit + admin (isolated per tenant).
  - [x] create department, job position (+ `default_roles`), shift (isolation-verified).
  - [x] employee enrollment with login, without login, and `grantSystemAccess`.
  - [x] role/permission propagation from position onto the `organization_user` pivot.
- [x] **Health/monitoring** for production: `scripts/health-check-probe.sh` probes `/up`, `/login`, `/setup`, release marker; wired into `deploy-bluegreen.sh healthcheck`; runbook §6 documents usage.
- [x] **Runbook / Go-No-Go checklist** — see `erp-saas-blue-green-rollout-runbook.md`; rollback drill documented in runbook §7.
- [ ] (Optional) Add `laravel/envoy` to `require-dev` — **skipped**; bash `scripts/deploy-bluegreen.sh` chosen as the orchestrator.

**Go/No-Go (Day 3 end):** Green if: (a) CI gate is green, (b) full Blue/Green swap + rollback drill pass on a staging box, (c) MVP vertical slice tests pass on MySQL, (d) an empty second tenant can onboard + enroll without seeing tenant A's data. Otherwise red and re-scope.

---

## Post-MVP Expansion (future phases — attendance/payroll etc.)
| Phase | Scope added | Enabling work now |
|---|---|---|
| Phase 2 | **Attendance** (clock-in/out, shifts schedules, leave) | Already in repo; add its flows to the CI smoke + swap of the same pipeline |
| Phase 3 | **Payroll** (processing, loans, advances, increments, payslips) | Add `schedule:run` cron + queue tie-ins; ensure long-running payroll jobs survive swap via `queue:restart` |
| Phase 4 | **Inventory + Accounting** | Same pipeline; add per-release migrations audit for accounting (vouchers, chart of accounts) |

Each phase = *narrow the validation slice, keep the same deploy pipeline*. No new deploy machinery needed.

---

## Security & Tenancy Appendix (ensure MVP does not leak orgs)
The multi-tenant scope is the single biggest production risk. Verified weaknesses to address in MVP slice (not necessarily in this 3-day window — flagged for the tenant-hardening follow-up):
1. **No middleware-level tenant guarantee** — scoping depends on models opting into `BelongsToOrganization` and on `Auth::check()`. Add a tenancy middleware to set org context deterministically.
2. **`current_organization_id` is mutable user state, not session state** — introduces drift; admin "fix" dashboard exists because of it. Consider an in-app org switch writing to session.
3. **Cross-org permission union** — `User::hasPermission()` → `getAllPermissions()` unions across ALL orgs the user belongs to. For strict tenant isolation, restrict to `current_organization_id`.
4. **`inventory_stores` has no `organization_id`** — isolated indirectly via unit; be careful with raw/eager-load paths.
5. **Queue/console/unauth paths bypass the scope** and the trait's create-fallback assigns the **first org** in the table — dangerous. Ensure jobs/services explicitly pass `organization_id`, and never rely on the fallback.
6. **Provisioning is manual** — no billing/plan hook and no tenant lifecycle (suspend/archive) beyond `organizations.is_active`. Phase-3 hardening.

MVP Go/No-Go already includes the "two tenants don't cross" test (Day 3).

---

## Runbook / Go-No-Go Checklist
> Full runbook: `docs/features/plans/erp-saas-blue-green-rollout-runbook.md`

- [x] `composer test` green (Memory=1G; 1473 passed / 0 failed as of `8a6d3d2`).
- [x] `vendor/bin/pint --test` passes (gated in `ci.yml` — verified after formatting fix).
- [x] `npm run build` produces hashed manifest; `ci.yml` fails if `manifest.json` missing.
- [ ] Blue/Green swap exercised 2× with **no logged-out sessions** and **no 500s** during flip *(staging box — pending)*.
- [ ] Rollback drill: forced failure -> flipped back in <60s, data intact (forward-only migration policy honored) *(staging box — pending)*.
- [x] Migrations forward-only; review step confirms column-alter migrations re-declare full column attributes (runbook §5).
- [x] Two-tenant isolation vertical slice passes (`MvpVerticalSliceIsolationTest` — 8 tests, verified).
- [x] Health probe (`scripts/health-check-probe.sh`) reachable; workers supervised (`scripts/hrm-queue.service`); `.env.production` secret-managed, not in git.
- [x] `deploy-production-legacy.sh` retained as emergency escape hatch.
- [x] CI test job made **deploy-blocking** (`continue-on-error: false` in `ci.yml`); suite green so no longer a non-blocking signal.

## Files Created/Referenced
- **New (this task):** `docs/features/plans/erp-saas-blue-green-rollout-mvp.md`, `docs/features/plans/erp-saas-blue-green-rollout-runbook.md`
- **New (Day 1–3 implementation):** `.github/workflows/ci.yml`, `phpunit.mysql.xml`, `scripts/deploy-bluegreen.sh`, `scripts/health-check-probe.sh`, `scripts/hrm-queue.service`, `.env.production.example`, `tests/Feature/Organization/MvpVerticalSliceIsolationTest.php`, `scripts/deploy-production.sh` → renamed `scripts/deploy-production-legacy.sh`.
- **Existing (already in repo, reused):** `scripts/deploy-production-legacy.sh` (emergency escape hatch), `.github/workflows/dusk.yml`, `routes/setup.php` + `SetupController` (provisioning), `HR/EmployeeController.php` + `OrganizationUser` pivot (enrollment), `OrganizationScope` + `BelongsToOrganization` (tenancy), Vite manifest (`public/build`), Laravel `/up` health endpoint.
