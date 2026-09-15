# Documentation Task — Employee & Department Workflows

## 📋 Task Overview

**Project**: HRM Laravel Base ERP System
**Status**: ⏳ PENDING — assigned to a documentation agent
**Assigned by**: Session (onboarding / employee + org department work)
**Depends on**: code listed below is already implemented, tested, and verified on the live server.

This is a **documentation-only** task. No code changes are required. The goal is to bring the
project's documentation in line with the recently completed and verified employee/org changes, and to
produce the deliverables a documentation agent can hand back for review.

---

## 🎯 Objective

Document, end-to-end, the following working features so that a new developer (or support agent) can
reproduce every screen and flow:

1. **Employee lifecycle** — create, edit, view (show), list.
2. **Position & Shift dropdown "add new + return"** flow.
3. **Department (Organization Unit) management** — list, create, edit, delete, hierarchy.
4. The **default Head Office / Accounting Department** setup created by the wizard.

Reference the canonical workflow doc `docs/sdlc-workflows.md` (already partially updated) and the
`docs/features/complete/` modules. Do **not** duplicate — link and extend.

---

## ✅ Source-of-Truth Code (already implemented & verified)

| Concern | Files |
|---------|-------|
| Employee controller | `app/Http/Controllers/HR/EmployeeController.php` |
| Employee forms | `resources/views/hr/employees/create.blade.php`, `edit.blade.php`, `show.blade.php`, `index.blade.php` |
| Position (add-new + return_to) | `app/Http/Controllers/HR/JobPositionController.php`, `resources/views/hr/positions/create.blade.php` |
| Shift (add-new + return_to) | `app/Http/Controllers/HR/ShiftController.php`, `resources/views/hr/shifts/create.blade.php` |
| Department management | `app/Http/Controllers/OrganizationUnitController.php`, `resources/views/organizations/units/{index,create,edit}.blade.php` |
| Department routes | `routes/organization.php` |
| Structure link | `resources/views/organizations/structure.blade.php` |

Key verified behaviors to document:
- Employee **edit** salary field now reads `Employee.basic_salary` (was a blank `salary_per_month`).
- Create and edit forms are aligned: same `position_id` / `shift_id` dropdowns, `roles[]`, pay frequency.
- `+ Add new position / shift` links carry `?return_to=` and **return to the employee form** after save.
- Department routes use the `{department}` parameter (NOT `{unit}` — avoids the global
  `Route::bind('unit', ...)` in `AppServiceProvider` used by the legacy API).
- Department queries are org-scoped; cross-org units are not resolvable (404).

---

## 📄 Deliverables

### D1. `docs/sdlc-workflows.md`
- [x] Employee Step 5 already updated (create/edit consistency note, add-new flow).
- [ ] Review and polish — ensure tables/screens match current UI exactly.
- [ ] Confirm `Screen → Route` reference table reflects the new `organization.units.*` routes.

### D2. `docs/features/complete/employee-lifecycle.md` (new)
- Employee create / edit / view / list walkthrough (screens, validation, DB rows created).
- Salary mapping `salary_per_month` → `basic_salary`.
- The "add new position / shift, return to form" pattern.

### D3. `docs/features/complete/department-management.md` (new)
- Access path: Organization → Structure → Manage Departments.
- Department management screen capabilities, routes, hierarchy (Head Office as root, Accounting under it).
- Tenant-scoping note and the `{department}` route-parameter rationale.

### D4. Update `docs/list of screens.md`, `docs/sdlc-feature-index.md`, `docs/sdlc-interfaces-spec.md`
- Add Department Management screen + routes.
- Add/refresh Employee create/edit screen entries.
- Note the `return_to` query param on `hr.positions.create` / `hr.shifts.create`.

### D5. Update interface/API docs (if applicable)
- Document the web form routes only (these are server-rendered Blade forms, not JSON APIs).
- Leave the legacy nested API (`api/organizations/{organization}/units/{unit}`) untouched.

---

## 🧪 Verification for the Doc Agent

Re-run the relevant test suites to confirm documented behavior is still true before finalizing docs:

```bash
php artisan test tests/Feature/HR/EmployeeManagementTest.php
php artisan test tests/Feature/HR/JobPositionControllerTest.php
php artisan test tests/Feature/HR/ShiftControllerTest.php
php artisan test tests/Feature/Organization/DepartmentManagementTest.php
```

Manual smoke (dev server on `http://localhost:8000`):
- `GET /hr/employees/{id}/edit` → salary pre-filled.
- `GET /hr/employees/create` → "+ Add new position/shift" links present.
- `GET /organization/units` → lists Head Office + Accounting Department; create/edit/delete work.

---

## ⏭️ Follow-up / Not In Scope Now
- Payroll Tax Configuration screen (`/payroll/tax`) still throws a missing-view error
  ("View [payroll.tax-configuration] not found") — pre-existing, separate module.
- Next session's implementation task (see `docs/features/plans/job-positions-roles-shifts.md`).

*(Created <DATE> — pending documentation-agent assignment.)*
