# Database Schema Documentation (ERD)

*Generated: November 19, 2025*  
*Database Engine: SQLite*  
*Total Tables: 32*

---

## **Core Architecture**

The HRM Laravel Base system uses a **multi-tenant architecture** with organization-based data isolation. The database schema supports comprehensive ERP functionality including Financial Management, HR, Inventory, and advanced business operations.

---

## **1. Core Tables**

### **organizations**
Multi-tenant organization management with hierarchical structure.

| Column | Type | Constraints | Description |
|---------|------|-------------|-------------|
| id | integer | PRIMARY KEY | Unique identifier |
| name | varchar | UNIQUE | Organization name |
| description | text | NULLABLE | Organization description |
| is_active | tinyint | DEFAULT 1 | Active status |
| deleted_at | datetime | NULLABLE | Soft delete timestamp |
| created_at | datetime | | Creation timestamp |
| updated_at | datetime | | Last update timestamp |

**Relationships:**
- Has many: users, employees, chart_of_accounts, inventory_stores, etc.

---

### **users**
Laravel Jetstream-based user authentication with multi-organization support.

| Column | Type | Constraints | Description |
|---------|------|-------------|-------------|
| id | integer | PRIMARY KEY | User ID |
| name | varchar | | Full name |
| email | varchar | UNIQUE | Email address |
| email_verified_at | datetime | NULLABLE | Email verification |
| password | varchar | | Hashed password |
| current_organization_id | integer | FOREIGN KEY → organizations.id | Active organization |
| two_factor_secret | text | NULLABLE | 2FA secret |
| two_factor_recovery_codes | text | NULLABLE | 2FA recovery codes |
| two_factor_confirmed_at | datetime | NULLABLE | 2FA confirmation |
| remember_token | varchar | NULLABLE | Remember me token |
| current_team_id | integer | FOREIGN KEY → teams.id | Current team |
| profile_photo_path | varchar | NULLABLE | Profile photo |
| created_at | datetime | | Creation |
| updated_at | datetime | | Last update |

---

## **2. HR Management Tables**

### **employees**
Comprehensive employee management with organizational structure integration.

| Column | Type | Constraints | Description |
|---------|------|-------------|-------------|
| id | integer | PRIMARY KEY | Employee ID |
| user_id | integer | FOREIGN KEY → users.id | Linked user account |
| organization_id | integer | FOREIGN KEY → organizations.id | Organization |
| organization_unit_id | integer | FOREIGN KEY → organization_units.id | Department/Unit |
| position_id | integer | FOREIGN KEY → job_positions.id | Job position |
| shift_id | integer | FOREIGN KEY → shifts.id | Work shift |
| biometric_id | varchar | UNIQUE | Biometric device ID |
| first_name | varchar | | First name |
| last_name | varchar | | Last name |
| middle_name | varchar | NULLABLE | Middle name |
| date_of_birth | date | | Birth date |
| gender | varchar | | Gender |
| email | varchar | INDEX | Email address |
| phone | varchar | | Phone number |
| address | text | | Full address |
| city | varchar | | City |
| state | varchar | | State |
| country | varchar | | Country |
| zip_code | varchar | | Postal code |
| photo | varchar | NULLABLE | Photo path |
| is_active | tinyint | DEFAULT 1 | Active status |
| is_admin | tinyint | DEFAULT 0 | Admin status |
| deleted_at | datetime | NULLABLE | Soft delete |
| created_at | datetime | | Creation |
| updated_at | datetime | | Last update |

**Indexes:**
- UNIQUE: biometric_id
- UNIQUE: user_id + organization_id
- INDEX: email, organization_id + is_active

### **job_positions**
Job position and role management.

| Column | Type | Constraints | Description |
|---------|------|-------------|-------------|
| id | integer | PRIMARY KEY | Position ID |
| organization_id | integer | FOREIGN KEY → organizations.id | Organization |
| organization_unit_id | integer | FOREIGN KEY → organization_units.id | Unit |
| title | varchar | | Position title |
| code | varchar | UNIQUE | Position code |
| description | text | NULLABLE | Description |
| min_salary | numeric | NULLABLE | Minimum salary |
| max_salary | numeric | NULLABLE | Maximum salary |
| requirements | text | NULLABLE | Job requirements |
| is_active | tinyint | DEFAULT 1 | Active status |
| deleted_at | datetime | NULLABLE | Soft delete |
| created_at | datetime | | Creation |
| updated_at | datetime | | Last update |

### **shifts**
Work shift management with flexible scheduling.

| Column | Type | Constraints | Description |
|---------|------|-------------|-------------|
| id | integer | PRIMARY KEY | Shift ID |
| organization_id | integer | FOREIGN KEY → organizations.id | Organization |
| name | varchar | | Shift name |
| code | varchar | UNIQUE | Shift code |
| start_time | time | | Start time |
| end_time | time | | End time |
| days_of_week | text | | JSON days array |
| working_hours | integer | | Hours per shift |
| description | text | NULLABLE | Description |
| is_active | tinyint | DEFAULT 1 | Active status |
| deleted_at | datetime | NULLABLE | Soft delete |
| created_at | datetime | | Creation |
| updated_at | datetime | | Last update |

### **attendance_records**
Biometric-integrated attendance tracking.

| Column | Type | Constraints | Description |
|---------|------|-------------|-------------|
| id | integer | PRIMARY KEY | Record ID |
| employee_id | integer | FOREIGN KEY → employees.id | Employee |
| organization_id | integer | FOREIGN KEY → organizations.id | Organization |
| record_date | date | | Attendance date |
| punch_in | datetime | NULLABLE | Clock in time |
| punch_out | datetime | NULLABLE | Clock out time |
| total_hours | numeric | NULLABLE | Total hours worked |
| status | varchar | INDEX | Attendance status |
| biometric_id | varchar | | Biometric device ID |
| device_serial_no | varchar | | Device serial |
| late_minutes | numeric | NULLABLE | Late minutes |
| overtime_minutes | numeric | NULLABLE | Overtime minutes |
| notes | text | NULLABLE | Notes |
| created_at | datetime | | Creation |
| updated_at | datetime | | Last update |

**Indexes:**
- UNIQUE: employee_id + record_date
- INDEX: organization_id + record_date, status

### **leave_requests**
Leave management with approval workflows.

| Column | Type | Constraints | Description |
|---------|------|-------------|-------------|
| id | integer | PRIMARY KEY | Request ID |
| employee_id | integer | FOREIGN KEY → employees.id | Employee |
| organization_id | integer | FOREIGN KEY → organizations.id | Organization |
| leave_type | varchar | INDEX | Type of leave |
| start_date | date | | Leave start |
| end_date | date | | Leave end |
| total_days | integer | | Total days |
| reason | text | | Leave reason |
| status | varchar | INDEX | Request status |
| approved_by | integer | FOREIGN KEY → users.id | Approver |
| rejected_by | integer | FOREIGN KEY → users.id | Rejecter |
| approved_at | datetime | NULLABLE | Approval time |
| rejected_at | datetime | NULLABLE | Rejection time |
| rejection_reason | text | NULLABLE | Rejection reason |
| applied_at | datetime | | Application time |
| created_at | datetime | | Creation |
| updated_at | datetime | | Last update |

---

## **3. Financial Management Tables**

### **chart_of_accounts**
Double-entry accounting chart of accounts.

| Column | Type | Constraints | Description |
|---------|------|-------------|-------------|
| id | integer | PRIMARY KEY | Account ID |
| organization_id | integer | FOREIGN KEY → organizations.id | Organization |
| code | varchar | | Account code |
| name | varchar | | Account name |
| type | varchar | | Account type |
| description | text | NULLABLE | Description |
| created_at | datetime | | Creation |
| updated_at | datetime | | Last update |

**Indexes:**
- UNIQUE: organization_id + code

### **journal_entries**
Financial transaction management.

| Column | Type | Constraints | Description |
|---------|------|-------------|-------------|
| id | integer | PRIMARY KEY | Entry ID |
| organization_id | integer | FOREIGN KEY → organizations.id | Organization |
| reference_number | varchar | UNIQUE | Reference number |
| entry_date | date | | Entry date |
| description | text | | Description |
| status | varchar | | Entry status |
| created_by | integer | FOREIGN KEY → users.id | Creator |
| approved_by | integer | FOREIGN KEY → users.id | Approver |
| posted_at | datetime | NULLABLE | Posting time |
| voucher_type | varchar | | Voucher type |
| customer_id | integer | FOREIGN KEY → customers.id | Customer |
| vendor_id | integer | FOREIGN KEY → vendors.id | Vendor |
| total_amount | numeric | NULLABLE | Total amount |
| tax_amount | numeric | NULLABLE | Tax amount |
| invoice_number | varchar | NULLABLE | Invoice number |
| due_date | date | NULLABLE | Due date |
| created_at | datetime | | Creation |
| updated_at | datetime | | Last update |

### **ledger_entries**
Detailed ledger entry lines for double-entry accounting.

| Column | Type | Constraints | Description |
|---------|------|-------------|-------------|
| id | integer | PRIMARY KEY | Entry ID |
| entry_date | date | | Entry date |
| chart_of_account_id | integer | FOREIGN KEY → chart_of_accounts.id | Account |
| type | varchar | | Debit/Credit |
| amount | numeric | | Entry amount |
| description | text | NULLABLE | Description |
| transactionable_type | varchar | INDEX | Related model type |
| transactionable_id | integer | INDEX | Related model ID |
| created_at | datetime | | Creation |
| updated_at | datetime | | Last update |

**Indexes:**
- INDEX: transactionable_type + transactionable_id

### **customers & vendors**
Customer and vendor management for AR/AP.

| Column | Type | Constraints | Description |
|---------|------|-------------|-------------|
| id | integer | PRIMARY KEY | ID |
| organization_id | integer | FOREIGN KEY → organizations.id | Organization |
| name | varchar | | Name |
| email | varchar | NULLABLE | Email |
| phone | varchar | NULLABLE | Phone |
| address | text | NULLABLE | Address |
| tax_number | varchar | NULLABLE | Tax ID |
| customer_type/vendor_type | varchar | NULLABLE | Type classification |
| credit_limit/payment_terms | varchar | NULLABLE | Credit terms |
| status | varchar | | Status |
| created_at | datetime | | Creation |
| updated_at | datetime | | Last update |

---

## **4. Inventory Management Tables**

### **inventory_stores**
Multi-store inventory management.

| Column | Type | Constraints | Description |
|---------|------|-------------|-------------|
| id | integer | PRIMARY KEY | Store ID |
| organization_id | integer | FOREIGN KEY → organizations.id | Organization |
| name | varchar | | Store name |
| code | varchar | UNIQUE | Store code |
| location | varchar | NULLABLE | Location |
| manager_id | integer | FOREIGN KEY → employees.id | Store manager |
| is_active | tinyint | DEFAULT 1 | Active status |
| created_at | datetime | | Creation |
| updated_at | datetime | | Last update |

### **inventory_items**
Item catalog and product management.

| Column | Type | Constraints | Description |
|---------|------|-------------|-------------|
| id | integer | PRIMARY KEY | Item ID |
| organization_id | integer | FOREIGN KEY → organizations.id | Organization |
| name | varchar | | Item name |
| code | varchar | UNIQUE | Item code/SKU |
| description | text | NULLABLE | Description |
| category | varchar | NULLABLE | Category |
| unit | varchar | NULLABLE | Unit of measure |
| cost_price | numeric | NULLABLE | Cost price |
| selling_price | numeric | NULLABLE | Selling price |
| min_stock_level | numeric | NULLABLE | Minimum stock |
| max_stock_level | numeric | NULLABLE | Maximum stock |
| is_active | tinyint | DEFAULT 1 | Active status |
| created_at | datetime | | Creation |
| updated_at | datetime | | Last update |

### **inventory_transactions**
Stock movement tracking.

| Column | Type | Constraints | Description |
|---------|------|-------------|-------------|
| id | integer | PRIMARY KEY | Transaction ID |
| organization_id | integer | FOREIGN KEY → organizations.id | Organization |
| store_id | integer | FOREIGN KEY → inventory_stores.id | Store |
| transaction_type | varchar | | IN/OUT/TRANSFER/ADJUST |
| reference_number | varchar | UNIQUE | Reference |
| transaction_date | datetime | | Transaction date |
| notes | text | NULLABLE | Notes |
| status | varchar | | Status |
| created_by | integer | FOREIGN KEY → users.id | Creator |
| approved_by | integer | FOREIGN KEY → users.id | Approver |
| created_at | datetime | | Creation |
| updated_at | datetime | | Last update |

### **inventory_transaction_items**
Detailed transaction line items.

| Column | Type | Constraints | Description |
|---------|------|-------------|-------------|
| id | integer | PRIMARY KEY | Line ID |
| transaction_id | integer | FOREIGN KEY → inventory_transactions.id | Transaction |
| item_id | integer | FOREIGN KEY → inventory_items.id | Item |
| quantity | numeric | | Quantity |
| unit_cost | numeric | NULLABLE | Unit cost |
| total_cost | numeric | NULLABLE | Total cost |
| batch_number | varchar | NULLABLE | Batch number |
| expiry_date | date | NULLABLE | Expiry date |
| created_at | datetime | | Creation |
| updated_at | datetime | | Last update |

---

## **5. Organizational Structure Tables**

### **organization_units**
Hierarchical organizational structure.

| Column | Type | Constraints | Description |
|---------|------|-------------|-------------|
| id | integer | PRIMARY KEY | Unit ID |
| organization_id | integer | FOREIGN KEY → organizations.id | Organization |
| name | varchar | | Unit name |
| type | varchar | | Unit type |
| parent_id | integer | FOREIGN KEY → organization_units.id | Parent unit |
| custom_fields | text | NULLABLE | JSON custom fields |
| deleted_at | datetime | NULLABLE | Soft delete |
| created_at | datetime | | Creation |
| updated_at | datetime | | Last update |

### **organization_user**
User-organization membership with roles.

| Column | Type | Constraints | Description |
|---------|------|-------------|-------------|
| id | integer | PRIMARY KEY | Membership ID |
| organization_id | integer | FOREIGN KEY → organizations.id | Organization |
| user_id | integer | FOREIGN KEY → users.id | User |
| organization_unit_id | integer | FOREIGN KEY → organization_units.id | Unit |
| roles | text | NULLABLE | JSON roles array |
| permissions | text | NULLABLE | JSON permissions |
| position | varchar | NULLABLE | Position |
| created_at | datetime | | Creation |
| updated_at | datetime | | Last update |

---

## **6. Payroll Management Tables**

### **payroll_runs**
Payroll processing batches.

| Column | Type | Constraints | Description |
|---------|------|-------------|-------------|
| id | integer | PRIMARY KEY | Run ID |
| organization_id | integer | FOREIGN KEY → organizations.id | Organization |
| period | varchar | | Pay period |
| start_date | date | | Period start |
| end_date | date | | Period end |
| status | varchar | | Run status |
| total_gross | numeric | NULLABLE | Total gross pay |
| total_net | numeric | NULLABLE | Total net pay |
| journal_entry_id | integer | FOREIGN KEY → journal_entries.id | Accounting entry |
| created_at | datetime | | Creation |
| updated_at | datetime | | Last update |

### **payroll_entries**
Individual employee payroll calculations.

| Column | Type | Constraints | Description |
|---------|------|-------------|-------------|
| id | integer | PRIMARY KEY | Entry ID |
| employee_id | integer | FOREIGN KEY → employees.id | Employee |
| organization_id | integer | FOREIGN KEY → organizations.id | Organization |
| period | varchar | | Pay period |
| basic_salary | numeric | NULLABLE | Basic salary |
| housing_allowance | numeric | NULLABLE | Housing allowance |
| transport_allowance | numeric | NULLABLE | Transport allowance |
| overtime_pay | numeric | NULLABLE | Overtime pay |
| bonus | numeric | NULLABLE | Bonus |
| gross_pay | numeric | NULLABLE | Gross pay |
| tax_deduction | numeric | NULLABLE | Tax deduction |
| insurance_deduction | numeric | NULLABLE | Insurance deduction |
| other_deductions | numeric | NULLABLE | Other deductions |
| total_deductions | numeric | NULLABLE | Total deductions |
| net_pay | numeric | NULLABLE | Net pay |
| status | varchar | INDEX | Status |
| paid_at | datetime | NULLABLE | Payment date |
| created_at | datetime | | Creation |
| updated_at | datetime | | Last update |

**Indexes:**
- UNIQUE: employee_id + period
- INDEX: organization_id + period, status

---

## **7. System & Support Tables**

### **sequences**
Sequence number generation for documents.

| Column | Type | Constraints | Description |
|---------|------|-------------|-------------|
| name | varchar | PRIMARY KEY | Sequence name |
| last_value | integer | | Last value |
| increment_by | integer | DEFAULT 1 | Increment |
| prefix | varchar | NULLABLE | Number prefix |
| suffix | varchar | NULLABLE | Number suffix |
| pad_length | integer | NULLABLE | Zero padding |
| created_at | datetime | | Creation |
| updated_at | datetime | | Last update |

### **dimensions & dimensionables**
Business dimension tracking for reporting.

| Column | Type | Constraints | Description |
|---------|------|-------------|-------------|
| id | integer | PRIMARY KEY | Dimension ID |
| name | varchar | | Dimension name |
| code | varchar | UNIQUE | Dimension code |
| type | varchar | INDEX | Dimension type |
| description | text | NULLABLE | Description |
| created_at | datetime | | Creation |
| updated_at | datetime | | Last update |

### **dimensionables**
Polymorphic relationship for dimensions.

| Column | Type | Constraints | Description |
|---------|------|-------------|-------------|
| id | integer | PRIMARY KEY | ID |
| dimension_id | integer | FOREIGN KEY → dimensions.id | Dimension |
| dimensionable_type | varchar | | Related model type |
| dimensionable_id | integer | | Related model ID |
| created_at | datetime | | Creation |
| updated_at | datetime | | Last update |

**Indexes:**
- UNIQUE: dimension_id + dimensionable_id + dimensionable_type
- INDEX: dimensionable_type + dimensionable_id

---

## **8. Laravel Framework Tables**

Standard Laravel tables for authentication, teams, queues, etc.:

- **teams** - Jetstream team management
- **team_user** - Team memberships
- **team_invitations** - Team invitations
- **personal_access_tokens** - Sanctum API tokens
- **sessions** - User sessions
- **failed_jobs** - Failed queue jobs
- **jobs** - Queue jobs
- **job_batches** - Job batches
- **cache** & **cache_locks** - Cache storage
- **password_reset_tokens** - Password reset tokens
- **migrations** - Database migrations

---

## **9. Key Relationships & Data Flow**

### **Multi-Tenant Data Isolation**
- All business tables have `organization_id` foreign keys
- Data access is scoped by user's current organization
- Cross-organization data access is prevented at model level

### **Core Business Flows**

1. **HR Flow**: organizations → organization_units → employees → attendance_records → payroll_entries
2. **Accounting Flow**: chart_of_accounts → journal_entries → ledger_entries → financial_reports
3. **Inventory Flow**: inventory_stores → inventory_items → inventory_transactions → stock_levels
4. **User Management**: users → organization_user → permissions → role_based_access

### **Integration Points**

- **Payroll → Accounting**: payroll_runs → journal_entries (automatic posting)
- **Inventory → Accounting**: inventory_transactions → ledger_entries (COGS calculation)
- **HR → Payroll**: employees + attendance_records → payroll_entries (salary calculation)

---

## **10. Performance Optimizations**

### **Strategic Indexes**
- Composite indexes for common queries (organization + status, employee + date)
- Unique constraints for data integrity
- Foreign key indexes for join performance

### **Query Optimization**
- Eager loading for related data
- Database-level scoping for multi-tenancy
- Efficient pagination for large datasets

---

## **11. Security & Compliance**

### **Data Protection**
- Soft deletes for audit trails
- User activity logging through timestamps
- Role-based access control through organization_user

### **Audit Trail**
- created_at/updated_at on all tables
- created_by/approved_by for critical operations
- Status tracking for workflow processes

---

## **12. Scalability Considerations**

### **Multi-Organization Support**
- Schema designed for horizontal scaling
- Organization-based data partitioning
- Efficient cross-organization reporting

### **High Volume Tables**
- Optimized for high transaction volumes
- Efficient indexing strategies
- Bulk operation support

---

*This ERD documentation reflects the current production database schema as of November 19, 2025. The schema supports comprehensive ERP functionality with multi-tenant architecture.*