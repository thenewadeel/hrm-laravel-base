# Implementation Plan — Job Positions, Roles & Shifts

## 📋 Project Overview

**Project**: HRM Laravel Base ERP System
**Status**: ✅ COMPLETE — implemented & tested
**Goal**: Make **job positions, roles, and shifts** fully working and properly **linked** to
employees and organization access.

Current state per the closing session:
- Positions (`JobPosition`), shifts (`Shift`), and roles exist as models and CRUD screens.
- Employee create/edit assign `position_id` / `shift_id` and `roles[]`.
- **Known gaps:** linkage is inconsistent — `Employee.position_id` vs a free-text
  `OrganizationUser.position` are two separate concepts; role handling on edit is partial; positions
  and shifts are not fully validated/tied to payroll and permissions. This session makes them coherent.

---

## 🎯 Objective

Unify how **position**, **roles**, and **shift** are defined and linked, so:

1. A position is a first-class entity with an optional default shift and a role mapping.
2. An employee references a position and a shift; the position implies default roles.
3. Roles/permissions used by the app come from a single canonical definition.
4. Positions & shifts are scoped to organization, active/inactive, and drive payroll/attendance defaults.

---

## 🗺️ Suggested Investigation (start here)

- `app/Models/JobPosition.php`, `app/Models/Shift.php`, `app/Models/Employee.php`
- `app/Models/OrganizationUser.php` (`position` string + `roles` cast) + pivot `organization_user`
- `app/Http/Controllers/HR/{JobPositionController,ShiftController,EmployeeController}.php`
- Role definitions: `app/Roles/InventoryRoles.php`, `OrganizationRoles`, etc.; how `hasRole`/`hasPermission`
  work on `User` and `OrganizationUser`.
- How payroll uses position/roles: `app/Services/PayrollCalculationService.php`.
- Routes: `routes/hrm.php`.

---

## 🧩 Decisions Made (session)

1. **Position ↔ Role mapping** — Added `default_roles` (JSON) to `job_positions`. Assigning a position to an employee auto-grants those roles (merged with manually selected roles). Manual override still allowed.
2. **Shift business rules** — Added `required_daily_hours` + `pay_frequency` columns to `employees` (previously validated but never persisted). Shift's `working_hours` pre-fills `required_daily_hours` on the employee form via JS and as a server-side default.
3. **Free-text vs FK** — Added `position_id` FK to `organization_user` (linked to `JobPosition`); kept the denormalized `position` string for display/legacy-API back-compat; `position` string is sourced from the job position title where available.
4. **Add/edit UX** — Kept the existing "+ add new position/shift, return to employee form" pattern as-is.

## ✅ Acceptance Criteria

- [x] Positions, roles, and shifts each work consistently across create/edit both standalone screens and the employee form.
- [x] Assigning a position to an employee produces correct roles (position `default_roles` merged into `organization_user.roles`).
- [x] Shifts feed attendance/payroll defaults (`required_daily_hours` + `pay_frequency` are now persisted; shift `working_hours` is the default).
- [x] No blind 500s on positions/shifts screens (added missing `positions/edit`, `positions/show`, `shifts/edit` views).
- [x] Positions & shifts are org-scoped (active-only, cross-org rejected with validation errors).
- [x] Tests added/updated (see `tests/Feature/HR/EmployeePositionShiftIntegrationTest.php`, `JobPositionControllerTest`, `JobPositionControllerTestSimple`) and passing.

## 🧪 Tests
- Position→default-roles propagation test; shift→default hours test; payroll-default persistence test.
- Cross-organization isolation for positions (rejects other-org position & other-org department).
- Inactive position/shift now rejected (active-only validation).

*(Created <DATE> — to be picked up in the next session. See also the documentation task:
`docs/features/plans/documentation-task-employee-departments.md`.)*
