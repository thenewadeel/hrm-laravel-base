# Complete Route Documentation

*Generated: November 19, 2025*  
*Total Routes: 224*  
*Framework: Laravel 12*

---

## **Route Overview**

The HRM Laravel Base system provides comprehensive RESTful API routes and web routes for a full-featured ERP system. All routes are protected by authentication and organization-based multi-tenancy.

---

## **1. Core Application Routes**

### **Root & Dashboard**
| Method | URI | Name | Controller | Middleware |
|--------|-----|------|------------|-------------|
| GET/HEAD | `/` | generated::lgLnEbIM1AFkXhsb | - | web |
| GET/HEAD | `dashboard` | dashboard | DashboardController@index | web,auth |

### **Authentication (Laravel Fortify)**
| Method | URI | Name | Controller | Middleware |
|--------|-----|------|------------|-------------|
| GET/HEAD | `login` | login | AuthenticatedSessionController@create | web,guest |
| POST | `login` | login.store | AuthenticatedSessionController@store | web,guest |
| POST | `logout` | logout | AuthenticatedSessionController@destroy | web,auth |
| GET/HEAD | `register` | register | RegisteredUserController@create | web,guest |
| POST | `register` | register.store | RegisteredUserController@store | web,guest |
| GET/HEAD | `forgot-password` | password.request | PasswordResetLinkController@create | web,guest |
| POST | `forgot-password` | password.email | PasswordResetLinkController@store | web,guest |
| GET/HEAD | `reset-password/{token}` | password.reset | NewPasswordController@create | web,guest |
| POST | `reset-password` | password.update | NewPasswordController@store | web,guest |
| GET/HEAD | `two-factor-challenge` | two-factor.login | TwoFactorAuthenticatedSessionController@create | web,guest |
| POST | `two-factor-challenge` | two-factor.login.store | TwoFactorAuthenticatedSessionController@store | web,guest |

---

## **2. Organization Management**

### **Web Routes**
| Method | URI | Name | Controller | Middleware |
|--------|-----|------|------------|-------------|
| GET/HEAD | `organizations` | organization.index | Api\OrganizationController@index | web,auth |
| GET/HEAD | `organizations/analytics` | organization.analytics | OrganizationController@analytics | web,auth |
| GET/HEAD | `organizations/dashboard` | organization.dashboard | OrganizationController@dashboard | web,auth |
| GET/HEAD | `organizations/structure` | organization.structure | OrganizationController@structure | web,auth |

### **API Routes**
| Method | URI | Name | Controller | Middleware |
|--------|-----|------|------------|-------------|
| GET/HEAD | `api/organizations` | organizations.index | Api\OrganizationController@index | api,auth:sanctum |
| POST | `api/organizations` | organizations.store | Api\OrganizationController@store | api,auth:sanctum |
| GET/HEAD | `api/organizations/{organization}` | organizations.show | Api\OrganizationController@show | api,auth:sanctum |
| PUT/PATCH | `api/organizations/{organization}` | organizations.update | Api\OrganizationController@update | api,auth:sanctum |
| DELETE | `api/organizations/{organization}` | organizations.destroy | Api\OrganizationController@destroy | api,auth:sanctum |
| GET/HEAD | `api/organizations/{organization}/members` | generated::PvDSi1x | - | api,auth:sanctum |
| POST | `api/organizations/{organization}/invitations` | generated::oHF | - | api,auth:sanctum |
| GET/HEAD | `api/organizations/{organization}/units` | generated::Szy6XmfDd | - | api,auth:sanctum |
| POST | `api/organizations/{organization}/units` | generated::hAeWQWYNN | - | api,auth:sanctum |
| GET/HEAD | `api/organizations/{organization}/units/{unit}` | generated::0k | - | api,auth:sanctum |
| PUT | `api/organizations/{organization}/units/{unit}` | generated::GL | - | api,auth:sanctum |
| DELETE | `api/organizations/{organization}/units/{unit}` | generated::Wo | - | api,auth:sanctum |
| PUT | `api/organizations/{organization}/units/{unit}/assign` | generated:: | - | api,auth:sanctum |
| POST | `api/organizations/{organization}/units/{unit}/bulk-assign` | generated:: | - | api,auth:sanctum |
| GET/HEAD | `api/organizations/{organization}/units/{unit}/hierarchy` | generated:: | - | api,auth:sanctum |
| GET/HEAD | `api/organizations/{organization}/units/{unit}/members` | generated:: | - | api,auth:sanctum |

---

## **3. Financial Management**

### **Accounting Web Routes**
| Method | URI | Name | Controller | Middleware |
|--------|-----|------|------------|-------------|
| GET/HEAD | `accounts` | accounting.index | AccountsController@index | web,auth |

### **Accounting API Routes**
| Method | URI | Name | Controller | Middleware |
|--------|-----|------|------------|-------------|
| GET/HEAD | `api/accounts` | accounts.index | Api\Accounting\ChartOfAccountsController@index | api,auth:sanctum |
| POST | `api/accounts` | accounts.store | Api\Accounting\ChartOfAccountsController@store | api,auth:sanctum |
| GET/HEAD | `api/accounts/{account}` | accounts.show | Api\Accounting\ChartOfAccountsController@show | api,auth:sanctum |
| PUT/PATCH | `api/accounts/{account}` | accounts.update | Api\Accounting\ChartOfAccountsController@update | api,auth:sanctum |
| DELETE | `api/accounts/{account}` | accounts.destroy | Api\Accounting\ChartOfAccountsController@destroy | api,auth:sanctum |

### **Journal Entries API**
| Method | URI | Name | Controller | Middleware |
|--------|-----|------|------------|-------------|
| GET/HEAD | `api/journal-entries` | journal-entries.index | Api\Accounting\JournalEntryController@index | api,auth:sanctum |
| POST | `api/journal-entries` | journal-entries.store | Api\Accounting\JournalEntryController@store | api,auth:sanctum |
| GET/HEAD | `api/journal-entries/{journal_entry}` | journal-entries.show | Api\Accounting\JournalEntryController@show | api,auth:sanctum |
| PUT/PATCH | `api/journal-entries/{journal_entry}` | journal-entries.update | Api\Accounting\JournalEntryController@update | api,auth:sanctum |
| DELETE | `api/journal-entries/{journal_entry}` | journal-entries.destroy | Api\Accounting\JournalEntryController@destroy | api,auth:sanctum |
| PUT | `api/journal-entries/{journal_entry}/post` | generated::gG081tw | - | api,auth:sanctum |
| PUT | `api/journal-entries/{journal_entry}/void` | generated::a3xDbT6 | - | api,auth:sanctum |

### **Voucher System API**
| Method | URI | Name | Controller | Middleware |
|--------|-----|------|------------|-------------|
| GET/HEAD | `api/vouchers` | vouchers.index | Api\VoucherController@index | api,auth:sanctum |
| POST | `api/vouchers` | vouchers.store | Api\VoucherController@store | api,auth:sanctum |
| POST | `api/vouchers/expense` | generated::Ufl8CjLhRImIcuyq | Api\VoucherController@expense | api,auth:sanctum |
| POST | `api/vouchers/purchase` | generated::iNpU0gNtbPP3WYV | Api\VoucherController@purchase | api,auth:sanctum |
| POST | `api/vouchers/salary` | generated::qDoRW5TfLo8gQh7y | Api\VoucherController@salary | api,auth:sanctum |
| POST | `api/vouchers/sales` | generated::uc9q31P4cls1awIf | Api\VoucherController@sales | api,auth:sanctum |
| GET/HEAD | `api/vouchers/{voucher}` | vouchers.show | Api\VoucherController@show | api,auth:sanctum |
| PUT/PATCH | `api/vouchers/{voucher}` | vouchers.update | Api\VoucherController@update | api,auth:sanctum |
| DELETE | `api/vouchers/{voucher}` | vouchers.destroy | Api\VoucherController@destroy | api,auth:sanctum |
| PUT | `api/vouchers/{voucher}/post` | generated::go82ilheJ4Exlz5v | - | api,auth:sanctum |
| PUT | `api/vouchers/{voucher}/void` | generated::JcoopEbIzo6Plcsz | - | api,auth:sanctum |

### **Financial Reports API**
| Method | URI | Name | Controller | Middleware |
|--------|-----|------|------------|-------------|
| GET/HEAD | `api/reports/trial-balance` | generated::be3MX8gJudsMo8FJ | Api\Accounting\ReportsController@trialBalance | api,auth:sanctum |
| GET/HEAD | `api/reports/balance-sheet` | generated::OHlKYWIpJzAS7QIv | Api\Accounting\ReportsController@balanceSheet | api,auth:sanctum |
| GET/HEAD | `api/reports/income-statement` | generated::i8MnW7UOnvgcMmWC | Api\Accounting\ReportsController@incomeStatement | api,auth:sanctum |

### **Outstandings API**
| Method | URI | Name | Controller | Middleware |
|--------|-----|------|------------|-------------|
| GET/HEAD | `api/outstanding/customers/summary` | generated::HSOitO84a4APii | - | api,auth:sanctum |
| GET/HEAD | `api/outstanding/receivables/aging` | generated::jKjVA2DfzpkRJT | - | api,auth:sanctum |
| GET/HEAD | `api/outstanding/payables/aging` | generated::knvIsK3L7gBm4ySv | - | api,auth:sanctum |
| GET/HEAD | `api/outstanding/vendors/summary` | generated::YmqHw7szcd3fw6bK | - | api,auth:sanctum |

---

## **4. Inventory Management**

### **Inventory Web Routes**
| Method | URI | Name | Controller | Middleware |
|--------|-----|------|------------|-------------|
| GET/HEAD | `inventory` | inventory.index | Inventory\InventoryController@index | web,auth |
| GET/HEAD | `inventory/items` | inventory.items.index | Inventory\InventoryItemController@index | web,auth |
| GET/HEAD | `inventory/items/create` | inventory.items.create | Inventory\InventoryItemController@create | web,auth |
| GET/HEAD | `inventory/items/{item}` | inventory.items.show | Inventory\InventoryItemController@show | web,auth |
| GET/HEAD | `inventory/items/{item}/edit` | inventory.items.edit | Inventory\InventoryItemController@edit | web,auth |
| PUT | `inventory/items/{item}` | inventory.items.update | Inventory\InventoryItemController@update | web,auth |
| DELETE | `inventory/items/{item}` | inventory.items.destroy | Inventory\InventoryItemController@destroy | web,auth |
| POST | `inventory/items` | inventory.items.store | Inventory\InventoryItemController@store | web,auth |
| GET/HEAD | `inventory/reports` | inventory.reports.index | Inventory\InventoryReportController@index | web,auth |
| GET/HEAD | `inventory/reports/low-stock` | inventory.reports.low-stock | Inventory\InventoryReportController@lowStock | web,auth |
| GET/HEAD | `inventory/reports/movement` | inventory.reports.movement | Inventory\InventoryReportController@movement | web,auth |
| GET/HEAD | `inventory/reports/stock-levels` | inventory.reports.stock-levels | Inventory\InventoryReportController@stockLevels | web,auth |
| GET/HEAD | `inventory/stock/adjustment` | inventory.stock.adjustment | Inventory\InventoryStockController@adjustment | web,auth |
| POST | `inventory/stock/adjustment` | inventory.stock.process-adjustment | Inventory\InventoryStockController@processAdjustment | web,auth |
| GET/HEAD | `inventory/stock/count` | inventory.stock.count | Inventory\InventoryStockController@count | web,auth |
| POST | `inventory/stock/count` | inventory.stock.process-count | Inventory\InventoryStockController@processCount | web,auth |
| GET/HEAD | `inventory/stock/transfer` | inventory.stock.transfer | Inventory\InventoryStockController@transfer | web,auth |
| POST | `inventory/stock/transfer` | inventory.stock.process-transfer | Inventory\InventoryStockController@processTransfer | web,auth |
| GET/HEAD | `inventory/stores` | inventory.stores.index | Inventory\InventoryStoreController@index | web,auth |
| GET/HEAD | `inventory/stores/create` | inventory.stores.create | Inventory\InventoryStoreController@create | web,auth |
| GET/HEAD | `inventory/stores/{store}` | inventory.stores.show | Inventory\InventoryStoreController@show | web,auth |
| GET/HEAD | `inventory/stores/{store}/edit` | inventory.stores.edit | Inventory\InventoryStoreController@edit | web,auth |
| PUT | `inventory/stores/{store}` | inventory.stores.update | Inventory\InventoryStoreController@update | web,auth |
| DELETE | `inventory/stores/{store}` | inventory.stores.destroy | Inventory\InventoryStoreController@destroy | web,auth |
| POST | `inventory/stores` | inventory.stores.store | Inventory\InventoryStoreController@store | web,auth |
| GET/HEAD | `inventory/transactions` | inventory.transactions.index | Inventory\InventoryTransactionController@index | web,auth |
| GET/HEAD | `inventory/transactions/create` | inventory.transactions.create | Inventory\InventoryTransactionController@create | web,auth |
| GET/HEAD | `inventory/transactions/wizard` | inventory.transactions.wizard | Inventory\InventoryTransactionController@wizard | web,auth |
| GET/HEAD | `inventory/transactions/{transaction}` | inventory.transactions.show | Inventory\InventoryTransactionController@show | web,auth |
| POST | `inventory/transactions` | inventory.transactions.store | Inventory\InventoryTransactionController@store | web,auth |

### **Inventory API Routes**
| Method | URI | Name | Controller | Middleware |
|--------|-----|------|------------|-------------|
| GET/HEAD | `api/inventory/items` | items.index | Api\Inventory\ItemController@index | api,auth:sanctum |
| POST | `api/inventory/items` | items.store | Api\Inventory\ItemController@store | api,auth:sanctum |
| GET/HEAD | `api/inventory/items/low-stock` | items.low-stock | Api\Inventory\ItemController@lowStock | api,auth:sanctum |
| GET/HEAD | `api/inventory/items/out-of-stock` | items.out-of-stock | Api\Inventory\ItemController@outOfStock | api,auth:sanctum |
| GET/HEAD | `api/inventory/items/{item}` | items.show | Api\Inventory\ItemController@show | api,auth:sanctum |
| PUT/PATCH | `api/inventory/items/{item}` | items.update | Api\Inventory\ItemController@update | api,auth:sanctum |
| DELETE | `api/inventory/items/{item}` | items.destroy | Api\Inventory\ItemController@destroy | api,auth:sanctum |
| GET/HEAD | `api/inventory/items/{item}/availability` | items.availability | Api\Inventory\ItemController@availability | api,auth:sanctum |
| GET/HEAD | `api/inventory/stores` | stores.index | Api\Inventory\StoreController@index | api,auth:sanctum |
| POST | `api/inventory/stores` | stores.store | Api\Inventory\StoreController@store | api,auth:sanctum |
| GET/HEAD | `api/inventory/stores/{store}` | stores.show | Api\Inventory\StoreController@show | api,auth:sanctum |
| PUT/PATCH | `api/inventory/stores/{store}` | stores.update | Api\Inventory\StoreController@update | api,auth:sanctum |
| DELETE | `api/inventory/stores/{store}` | stores.destroy | Api\Inventory\StoreController@destroy | api,auth:sanctum |
| PUT | `api/inventory/stores/{store}/inventory` | stores.inventory.update | Api\Inventory\StoreController@updateInventory | api,auth:sanctum |
| POST | `api/inventory/stores/{store}/items` | stores.items.add | Api\Inventory\StoreController@addItems | api,auth:sanctum |
| PUT | `api/inventory/stores/{store}/items/{item}` | stores.items.update | Api\Inventory\StoreController@updateItem | api,auth:sanctum |
| DELETE | `api/inventory/stores/{store}/items/{item}` | stores.items.remove | Api\Inventory\StoreController@removeItem | api,auth:sanctum |
| GET/HEAD | `api/inventory/transactions` | transactions.index | Api\Inventory\TransactionController@index | api,auth:sanctum |
| POST | `api/inventory/transactions` | transactions.store | Api\Inventory\TransactionController@store | api,auth:sanctum |
| GET/HEAD | `api/inventory/transactions/{transaction}` | transactions.show | Api\Inventory\TransactionController@show | api,auth:sanctum |
| PUT/PATCH | `api/inventory/transactions/{transaction}` | transactions.update | Api\Inventory\TransactionController@update | api,auth:sanctum |
| DELETE | `api/inventory/transactions/{transaction}` | transactions.destroy | Api\Inventory\TransactionController@destroy | api,auth:sanctum |
| PUT | `api/inventory/transactions/{transaction}/cancel` | transaction.cancel | Api\Inventory\TransactionController@cancel | api,auth:sanctum |
| PUT | `api/inventory/transactions/{transaction}/finalize` | transaction.finalize | Api\Inventory\TransactionController@finalize | api,auth:sanctum |
| POST | `api/inventory/transactions/{transaction}/items` | transactions.items | Api\Inventory\TransactionController@addItems | api,auth:sanctum |

---

## **5. Human Resources Management**

### **HR Web Routes**
| Method | URI | Name | Controller | Middleware |
|--------|-----|------|------------|-------------|
| GET/HEAD | `hr/employees` | hr.employees.index | HR\EmployeeController@index | web,auth |
| POST | `hr/employees` | hr.employees.store | HR\EmployeeController@store | web,auth |
| GET/HEAD | `hr/employees/create` | hr.employees.create | HR\EmployeeController@create | web,auth |
| GET/HEAD | `hr/employees/{employee}` | hr.employees.show | HR\EmployeeController@show | web,auth |
| GET/HEAD | `hr/employees/{employee}/edit` | hr.employees.edit | HR\EmployeeController@edit | web,auth |
| PUT/PATCH | `hr/employees/{employee}` | hr.employees.update | HR\EmployeeController@update | web,auth |
| DELETE | `hr/employees/{employee}` | hr.employees.destroy | HR\EmployeeController@destroy | web,auth |
| POST | `hr/employees/without-user` | hr.employees.store-without-user | HR\EmployeeController@storeWithoutUser | web,auth |
| PUT | `hr/employees/{employee}/biometric` | hr.employees.update-biometric | HR\EmployeeController@updateBiometric | web,auth |
| POST | `hr/employees/{employee}/grant-access` | hr.employees.grant-access | HR\EmployeeController@grantAccess | web,auth |
| GET/HEAD | `hr/positions` | hr.positions.index | HR\JobPositionController@index | web,auth |
| POST | `hr/positions` | hr.positions.store | HR\JobPositionController@store | web,auth |
| GET/HEAD | `hr/positions/create` | hr.positions.create | HR\JobPositionController@create | web,auth |
| GET/HEAD | `hr/positions/{position}` | hr.positions.show | HR\JobPositionController@show | web,auth |
| GET/HEAD | `hr/positions/{position}/edit` | hr.positions.edit | HR\JobPositionController@edit | web,auth |
| PUT/PATCH | `hr/positions/{position}` | hr.positions.update | HR\JobPositionController@update | web,auth |
| DELETE | `hr/positions/{position}` | hr.positions.destroy | HR\JobPositionController@destroy | web,auth |
| GET/HEAD | `hr/shifts` | hr.shifts.index | HR\ShiftController@index | web,auth |
| POST | `hr/shifts` | hr.shifts.store | HR\ShiftController@store | web,auth |
| GET/HEAD | `hr/shifts/create` | hr.shifts.create | HR\ShiftController@create | web,auth |
| GET/HEAD | `hr/shifts/{shift}` | hr.shifts.show | HR\ShiftController@show | web,auth |
| GET/HEAD | `hr/shifts/{shift}/edit` | hr.shifts.edit | HR\ShiftController@edit | web,auth |
| PUT/PATCH | `hr/shifts/{shift}` | hr.shifts.update | HR\ShiftController@update | web,auth |
| DELETE | `hr/shifts/{shift}` | hr.shifts.destroy | HR\ShiftController@destroy | web,auth |

### **HRM Dashboard**
| Method | URI | Name | Controller | Middleware |
|--------|-----|------|------------|-------------|
| GET/HEAD | `hrm/dashboard` | hrm.dashboard | HrmDashboardController@index | web,auth |

---

## **6. Attendance & Leave Management**

### **Attendance Web Routes**
| Method | URI | Name | Controller | Middleware |
|--------|-----|------|------------|-------------|
| GET/HEAD | `attendance/dashboard` | attendance.dashboard | Attendance\AttendanceController@dashboard | web,auth |
| POST | `attendance/apply-leave/{id}` | attendance.apply-leave | Attendance\AttendanceController@applyLeave | web,auth |
| POST | `attendance/regularize/{id}` | attendance.regularize | Attendance\AttendanceController@regularize | web,auth |
| POST | `attendance/sync-biometric` | attendance.biometric-sync | Attendance\AttendanceController@biometricSync | web,auth |
| GET/HEAD | `attendance/export-payroll` | attendance.export-payroll | Attendance\AttendanceController@exportPayroll | web,auth |

---

## **7. Portal System**

### **Employee Portal**
| Method | URI | Name | Controller | Middleware |
|--------|-----|------|------------|-------------|
| GET/HEAD | `portal/employee/dashboard` | portal.employee.dashboard | Portal\EmployeeController@dashboard | web,auth |
| POST | `portal/employee/clock-in` | portal.employee.clock-in | Attendance\AttendanceController@clockIn | web,auth |
| POST | `portal/employee/clock-out` | portal.employee.clock-out | Attendance\AttendanceController@clockOut | web,auth |
| GET/HEAD | `portal/employee/attendance` | portal.employee.attendance | Portal\EmployeeController@attendance | web,auth |
| GET/HEAD | `portal/employee/leave` | portal.employee.leave | Portal\EmployeeController@leave | web,auth |
| POST | `portal/employee/leave` | portal.employee.leave.store | Portal\EmployeeController@storeLeave | web,auth |
| GET/HEAD | `portal/employee/leave/create` | portal.employee.leave.create | Portal\EmployeeController@createLeave | web,auth |
| GET/HEAD | `portal/employee/payslips` | portal.employee.payslips | Portal\EmployeeController@payslips | web,auth |
| GET/HEAD | `portal/employee/payslips/{payslip}` | portal.employee.payslips.show | Portal\EmployeeController@showPayslip | web,auth |
| GET/HEAD | `portal/employee/payslips/{payslip}/download` | portal.employee.payslips.download | Portal\EmployeeController@downloadPayslip | web,auth |
| GET/HEAD | `portal/employee/setup` | portal.employee.setup | Portal\EmployeeController@setup | web,auth |
| POST | `portal/employee/setup` | portal.employee.complete-setup | Portal\EmployeeController@completeSetup | web,auth |

### **Manager Portal**
| Method | URI | Name | Controller | Middleware |
|--------|-----|------|------------|-------------|
| GET/HEAD | `portal/manager/dashboard` | portal.manager.dashboard | Portal\ManagerController@dashboard | web,auth |
| GET/HEAD | `portal/manager/reports` | portal.manager.reports | Portal\ManagerController@reports | web,auth |
| GET/HEAD | `portal/manager/team-attendance` | portal.manager.team-attendance | Portal\ManagerController@teamAttendance | web,auth |
| POST | `portal/manager/leave/{leaveRequest}/approve` | portal.manager.leave.approve | Portal\ManagerController@approveLeave | web,auth |
| POST | `portal/manager/leave/{leaveRequest}/reject` | portal.manager.leave.reject | Portal\ManagerController@rejectLeave | web,auth |

---

## **8. Payroll Management**

### **Payroll Web Routes**
| Method | URI | Name | Controller | Middleware |
|--------|-----|------|------------|-------------|
| GET/HEAD | `payroll/processing` | payroll.processing | Payroll\PayrollController@processing | web,auth |

---

## **9. Setup Wizard**

### **Setup Routes**
| Method | URI | Name | Controller | Middleware |
|--------|-----|------|------------|-------------|
| GET/HEAD | `setup` | setup.organization | SetupController@organization | web,auth |
| POST | `setup/organization` | setup.organization.store | SetupController@storeOrganization | web,auth |
| GET/HEAD | `setup/accounts` | setup.accounts | SetupController@accounts | web,auth |
| POST | `setup/accounts` | setup.accounts.store | SetupController@storeAccounts | web,auth |
| GET/HEAD | `setup/stores` | setup.stores | SetupController@stores | web,auth |
| POST | `setup/stores` | setup.stores.store | SetupController@storeStores | web,auth |

---

## **10. User Management & Profile**

### **User Profile Routes (Laravel Jetstream)**
| Method | URI | Name | Controller | Middleware |
|--------|-----|------|------------|-------------|
| GET/HEAD | `user/profile` | profile.show | UserProfileController@show | web,auth |
| PUT | `user/profile-information` | user-profile-information.update | UserProfileController@update | web,auth |
| PUT | `user/password` | user-password.update | PasswordController@update | web,auth |
| GET/HEAD | `user/confirm-password` | password.confirm | ConfirmablePasswordController@show | web,auth |
| POST | `user/confirm-password` | password.confirm.store | ConfirmablePasswordController@store | web,auth |
| GET/HEAD | `user/api-tokens` | api-tokens.index | ApiTokenController@index | web,auth |
| GET/HEAD | `user/two-factor-authentication` | two-factor.enable | TwoFactorAuthenticationController@show | web,auth |
| POST | `user/two-factor-authentication` | two-factor.enable | TwoFactorAuthenticationController@store | web,auth |
| DELETE | `user/two-factor-authentication` | two-factor.disable | TwoFactorAuthenticationController@destroy | web,auth |
| GET/HEAD | `user/two-factor-qr-code` | two-factor.qr-code | TwoFactorQrCodeController@show | web,auth |
| GET/HEAD | `user/two-factor-recovery-codes` | two-factor.recovery-codes | TwoFactorRecoveryCodeController@index | web,auth |
| POST | `user/two-factor-recovery-codes` | two-factor.recovery-codes.regenerate | TwoFactorRecoveryCodeController@store | web,auth |

### **Team Management**
| Method | URI | Name | Controller | Middleware |
|--------|-----|------|------------|-------------|
| GET/HEAD | `teams/create` | teams.create | TeamController@create | web,auth |
| GET/HEAD | `teams/{team}` | teams.show | TeamController@show | web,auth |
| POST | `team-invitations/{invitation}/accept` | team-invitations.accept | TeamInvitationController@accept | web,auth |
| PUT | `current-team` | current-team.update | CurrentTeamController@update | web,auth |

---

## **11. API Utility Routes**

### **Debug & Development**
| Method | URI | Name | Controller | Middleware |
|--------|-----|------|------------|-------------|
| GET/HEAD | `api/debug/test-connection` | generated::m7WRBi9XZmdAFWOv | - | api |
| GET/HEAD | `api/user` | generated::P0hG2NL8U5SOO2h1 | - | api,auth:sanctum |
| GET/HEAD | `api/users/me/organizations` | generated::b9OckcIyvJqTK45X | - | api,auth:sanctum |

---

## **12. Storage & Assets**

### **File Storage**
| Method | URI | Name | Controller | Middleware |
|--------|-----|------|------------|-------------|
| GET/HEAD | `storage/{path}` | storage.local | - | web |

---

## **13. System & Debug Routes**

### **Development Tools**
| Method | URI | Name | Controller | Middleware |
|--------|-----|------|------------|-------------|
| GET/HEAD | `debug/api-config` | generated::xSvIwTB9CmEnr5M1 | - | web |
| GET/HEAD | `debug/journal-entries` | generated::IdPRmoPofrrWePdK | - | web |
| GET/HEAD | `debug/orgs` | generated::oSKCqOdwXQOcifwf | - | web |
| GET/HEAD | `debug/reports/all` | generated::SajLsbBas5kgyiMM | - | web |
| GET/HEAD | `debug/test-sequence` | generated::N833Ea5ecWAFpQzQ | - | web |

### **Livewire Routes**
| Method | URI | Name | Controller | Middleware |
|--------|-----|------|------------|-------------|
| GET/HEAD | `livewire/livewire.js` | generated::C1VXbk52miGIG1YJ | - | web |
| GET/HEAD | `livewire/livewire.min.js.map` | generated::HvCTOuTqRXiHttIO | - | web |
| POST | `livewire/update` | livewire.update | Livewire\Mechanisms\HandleRequests | web |
| POST | `livewire/upload-file` | livewire.upload-file | Livewire\Features\SupportFileUploads | web |
| GET/HEAD | `livewire/preview-file/{filename}` | livewire.preview-file | Livewire\Features\SupportFileUploads | web |

---

## **14. Third-Party Package Routes**

### **Laravel Sanctum**
| Method | URI | Name | Controller | Middleware |
|--------|-----|------|------------|-------------|
| GET/HEAD | `sanctum/csrf-cookie` | sanctum.csrf-cookie | - | web |

### **Debug Tools**
| Method | URI | Name | Controller | Middleware |
|--------|-----|------|------------|-------------|
| GET/HEAD | `_debugbar/assets/javascript` | debugbar.assets.js | - | web |
| GET/HEAD | `_debugbar/assets/stylesheets` | debugbar.assets.css | - | web |
| DELETE | `_debugbar/cache/{key}/{tags?}` | debugbar.cache.delete | - | web |
| GET/HEAD | `_debugbar/clockwork/{id}` | debugbar.clockwork | - | web |
| GET/HEAD | `_debugbar/open` | debugbar.openhandler | - | web |
| POST | `_debugbar/queries/explain` | debugbar.queries.explain | - | web |

### **Clockwork Integration**
| Method | URI | Name | Controller | Middleware |
|--------|-----|------|------------|-------------|
| GET/HEAD | `clockwork` | generated::LcCnSl8r9eKzFwCm | - | web |
| GET/HEAD | `clockwork/app` | generated::Kqb9hKwx2M75NvST | - | web |
| GET/HEAD | `clockwork/{path}` | generated::Mmn79H4UhtE7wBkV | - | web |
| POST | `clockwork/auth` | generated::gfCTsyWVtJimvYh0 | - | web |
| PUT | `clockwork/{id}` | generated::1gxSbjQ1e7N1QYLE | - | web |
| GET/HEAD | `clockwork/{id}/extended` | generated::gmEGNYAiFAmConDl | - | web |
| GET/HEAD | `clockwork/{id}/{direction?}/{count?}` | generated::H4fD7puoP | - | web |

### **Laravel Boost**
| Method | URI | Name | Controller | Middleware |
|--------|-----|------|------------|-------------|
| POST | `_boost/browser-logs` | boost.browser-logs | - | web |

---

## **15. Route Middleware Stack**

### **Standard Middleware Groups**

#### **web**
- Session management
- CSRF protection
- Cookie encryption
- Route model binding

#### **api**
- Stateless API
- Rate limiting
- CORS handling

#### **auth:sanctum**
- API token authentication
- Organization scoping
- Permission validation

---

## **16. Route Patterns & Conventions**

### **RESTful API Design**
- `GET /api/resource` - List resources
- `POST /api/resource` - Create resource
- `GET /api/resource/{id}` - Show resource
- `PUT/PATCH /api/resource/{id}` - Update resource
- `DELETE /api/resource/{id}` - Delete resource

### **Nested Resources**
- `GET /api/organizations/{org}/units` - Organization units
- `GET /api/inventory/stores/{store}/items` - Store items
- `POST /api/inventory/transactions/{id}/items` - Transaction items

### **Action Routes**
- `POST /api/vouchers/expense` - Create expense voucher
- `POST /api/vouchers/sales` - Create sales voucher
- `PUT /api/journal-entries/{id}/post` - Post journal entry
- `PUT /api/inventory/transactions/{id}/finalize` - Finalize transaction

---

## **17. Authentication & Authorization**

### **Route Protection**
- All web routes require `web` middleware
- All API routes require `api` middleware
- Protected routes require `auth` or `auth:sanctum`
- Organization-based data isolation enforced

### **Permission-Based Access**
- Role-based permissions checked in controllers
- Organization membership validated
- Resource-level authorization policies

---

## **18. API Versioning & Future-Proofing**

### **Current Version**
- API version: v1 (implicit)
- Consistent naming patterns
- Backward compatibility maintained

### **Scalability Design**
- RESTful conventions
- Resource-based routing
- Plural resource names
- HTTP verb compliance

---

*This route documentation reflects the current application state as of November 19, 2025. The system provides comprehensive REST API coverage for all ERP modules with proper authentication and authorization.*