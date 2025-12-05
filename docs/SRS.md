# **Software Requirements Specification (SRS) v2.0**

# **Comprehensive ERP SaaS Application**

## **1. Introduction**

### **1.1 Purpose**

This document specifies the functional and non-functional requirements for a comprehensive ERP SaaS application. The system has evolved from basic Accounting/HR to a full-featured enterprise resource planning suite with multi-tenant architecture.

### **1.2 Scope**

The system is an online, multi-tenant SaaS application with modular architecture supporting:

-   Core Accounting & Financial Management
-   Human Resources & Payroll
-   Inventory & Store Management
-   Membership Management
-   Organization & Multi-tenancy Management
-   Customer & Invoice Management
-   Employee/Manager Self-Service Portals

The application features a web-based interface using Laravel Livewire with RESTful API endpoints for integration.

### **1.3 Definitions and Acronyms**

-   **ERP:** Enterprise Resource Planning
-   **SaaS:** Software as a Service
-   **API:** Application Programming Interface
-   **Voucher:** A document representing a financial transaction
-   **Livewire:** Full-stack framework for Laravel for dynamic interfaces
-   **Jetstream:** Application starter kit for Laravel
-   **Tenant:** An organization/business using the SaaS application
-   **Organization Unit:** Hierarchical division within a tenant
-   **RBAC:** Role-Based Access Control

## **2. Overall Description**

### **2.1 Product Perspective**

This is a standalone multi-tenant ERP SaaS application. The system follows modular architecture allowing features to be enabled/disabled per tenant based on subscription.

### **2.2 User Characteristics**

The system serves:

-   **System Administrators:** Manage tenants, users, and system-wide configurations
-   **Organization Administrators:** Configure tenant settings, organization structure, user permissions
-   **Accounts Department:** Manage financial operations, vouchers, reporting
-   **HR Department:** Manage employees, payroll, leave, compensation
-   **Inventory Managers:** Oversee stock, stores, transfers, inventory reporting
-   **Store Clerks:** Daily inventory operations, stock counts, transactions
-   **Membership Administrators:** Manage members, subscriptions, fees, cards
-   **General Employees:** View payslips, apply for leave, check attendance via portal
-   **Department Managers:** Team oversight via manager portal
-   **Customers (Future):** Self-service portal for invoices, payments (not in current scope)

### **2.3 Constraints - UPDATED**

-   **Technology Stack:**

    -   Backend: Laravel 12+ (current implementation shows Laravel framework)
    -   Frontend: Livewire 3+, Alpine.js, Tailwind CSS
    -   Database: SQLite (development), MySQL/PostgreSQL (production)
    -   Testing: Pest PHP
    -   PDF Generation: Custom HTML-to-PDF system with theme support
    -   Authentication: Laravel Jetstream with teams for multi-tenancy
    -   API: RESTful API layer within application (not external dependency)

-   **Architectural Constraints:**

    -   Multi-tenant with data isolation via organization scopes
    -   Modular design with clear separation of concerns
    -   Service-oriented architecture with dedicated service classes
    -   Comprehensive testing required for all features
    -   All data exports must support CSV format
    -   PDF generation must support custom theming

-   **Business Constraints:**
    -   Tenancy features can be hidden for single-organization deployments
    -   Focus on robustness over feature richness
    -   Critical integration points between modules must be maintained
    -   System must support data backup/restore via CSV files

### **2.4 Assumptions and Dependencies**

-   Users have stable internet connectivity
-   Infrastructure supports horizontal scaling
-   Business logic remains consistent across tenants
-   CSV import/export format is standardized across modules
-   PDF generation works with major browsers
-   Modular architecture allows feature toggling per tenant

## **3. Specific Requirements - COMPREHENSIVE**

### **3.1 Core Platform Requirements**

#### **3.1.1 Multi-Tenancy & Organization Management**

-   **REQ-PLT-001:** The system shall support multiple organizations (tenants) with data isolation
-   **REQ-PLT-002:** Tenancy features shall be configurable (visible/hidden) per deployment
-   **REQ-PLT-003:** The system shall support hierarchical organization units (tree structure)
-   **REQ-PLT-004:** Users shall be assignable to specific organization units
-   **REQ-PLT-005:** The system shall include organization-level dashboards
-   **REQ-PLT-006:** Tenant onboarding shall include guided setup wizard

#### **3.1.2 User Management & Authentication**

-   **REQ-PLT-007:** The system shall use Laravel Jetstream for authentication
-   **REQ-PLT-008:** The system shall support role-based access control (RBAC)
-   **REQ-PLT-009:** Granular permissions shall be definable per module
-   **REQ-PLT-010:** The system shall include comprehensive user profiles
-   **REQ-PLT-011:** Users shall switch between organizations (if multi-tenant enabled)

#### **3.1.3 Data Management**

-   **REQ-PLT-012:** All modules shall support CSV export of data
-   **REQ-PLT-013:** Critical data shall be importable via CSV (with validation)
-   **REQ-PLT-014:** The system shall support data backup via structured CSV files
-   **REQ-PLT-015:** Data shall be restorable from CSV backups

### **3.2 Accounting & Financial Management**

#### **3.2.1 Voucher Management** (Enhanced)

-   **REQ-AC-001:** The system shall allow creation of vouchers (Sales, Purchase, Salary, Expense, etc.)
-   **REQ-AC-002:** The system shall support voucher editing, posting, and finalization
-   **REQ-AC-003:** Specialized voucher forms shall be provided (Cash Receipts, Cash Payments)
-   **REQ-AC-004:** The system shall manage Sales and Sales Return vouchers
-   **REQ-AC-005:** The system shall manage Purchase and Purchase Return vouchers
-   **REQ-AC-006:** The system shall handle salary, expense, and depreciation vouchers

#### **3.2.2 Cash & Bank Management** (New)

-   **REQ-AC-007:** The system shall manage bank accounts
-   **REQ-AC-008:** The system shall import and process bank statements
-   **REQ-AC-009:** The system shall support bank reconciliation
-   **REQ-AC-010:** The system shall track bank transactions
-   **REQ-AC-011:** The system shall manage cash receipts and payments

#### **3.2.3 Fixed Assets Management** (New)

-   **REQ-AC-012:** The system shall register and track fixed assets
-   **REQ-AC-013:** The system shall calculate and post depreciation
-   **REQ-AC-014:** The system shall handle asset disposals
-   **REQ-AC-015:** The system shall manage asset transfers between units
-   **REQ-AC-016:** The system shall track asset maintenance

#### **3.2.4 Tax Management** (New)

-   **REQ-AC-017:** The system shall configure tax rates and jurisdictions
-   **REQ-AC-018:** The system shall manage tax exemptions
-   **REQ-AC-019:** The system shall perform tax calculations
-   **REQ-AC-020:** The system shall support tax filings
-   **REQ-AC-021:** The system shall generate tax reports

#### **3.2.5 Financial Operations** (Enhanced)

-   **REQ-AC-022:** The system shall manage Chart of Accounts
-   **REQ-AC-023:** The system shall handle journal entries and ledger posting
-   **REQ-AC-024:** The system shall manage financial year opening/closing
-   **REQ-AC-025:** The system shall handle opening balances
-   **REQ-AC-026:** The system shall support closing entries

#### **3.2.6 Financial Reporting**

-   **REQ-AC-027:** The system shall generate Trial Balance reports
-   **REQ-AC-028:** The system shall generate Balance Sheet
-   **REQ-AC-029:** The system shall generate Profit and Loss/Income Statement
-   **REQ-AC-030:** The system shall generate Outstanding Statements (Receivables/Payables)
-   **REQ-AC-031:** The system shall generate Bank Statements
-   **REQ-AC-032:** All financial reports shall be exportable as PDF

### **3.3 Human Resources & Payroll**

#### **3.3.1 Employee Management** (Enhanced)

-   **REQ-HR-001:** The system shall maintain comprehensive employee database
-   **REQ-HR-002:** The system shall manage job positions and organizational hierarchy
-   **REQ-HR-003:** The system shall manage work shifts
-   **REQ-HR-004:** The system shall track attendance records
-   **REQ-HR-005:** The system shall manage leave requests and balances

#### **3.3.2 Payroll & Compensation** (Enhanced)

-   **REQ-HR-006:** The system shall calculate payroll with allowances/deductions
-   **REQ-HR-007:** The system shall manage employee increments
-   **REQ-HR-008:** The system shall handle employee loans
-   **REQ-HR-009:** The system shall manage salary advances
-   **REQ-HR-010:** The system shall generate pay slips
-   **REQ-HR-011:** The system shall configure payroll tax brackets

#### **3.3.3 Payroll Processing**

-   **REQ-HR-012:** The system shall support payroll runs (period processing)
-   **REQ-HR-013:** The system shall generate payroll slips
-   **REQ-HR-014:** The system shall track payroll entries
-   **REQ-HR-015:** The system shall generate payroll reports and analytics

### **3.4 Inventory Management System** (New Module)

#### **3.4.1 Store & Item Management**

-   **REQ-INV-001:** The system shall manage multiple inventory stores/locations
-   **REQ-INV-002:** The system shall categorize items using inventory heads
-   **REQ-INV-003:** The system shall track item details (SKU, description, pricing, reorder levels)

#### **3.4.2 Inventory Operations**

-   **REQ-INV-004:** The system shall record inventory transactions (receipts, issues, transfers)
-   **REQ-INV-005:** The system shall support stock adjustments
-   **REQ-INV-006:** The system shall handle stock counting
-   **REQ-INV-007:** The system shall track stock levels across stores

#### **3.4.3 Inventory Reporting**

-   **REQ-INV-008:** The system shall generate stock level reports
-   **REQ-INV-009:** The system shall generate inventory movement reports
-   **REQ-INV-010:** The system shall generate low stock alerts
-   **REQ-INV-011:** All inventory reports shall be exportable as PDF

### **3.5 Membership Management System** (New Module)

#### **3.5.1 Member Management**

-   **REQ-MEM-001:** The system shall maintain member records with family members
-   **REQ-MEM-002:** The system shall manage member subscriptions to plans
-   **REQ-MEM-003:** The system shall track membership fees and payments

#### **3.5.2 Membership Operations**

-   **REQ-MEM-004:** The system shall support barcode scanning for member verification
-   **REQ-MEM-005:** The system shall design and print membership cards
-   **REQ-MEM-006:** The system shall handle batch card printing
-   **REQ-MEM-007:** The system shall manage subscription plans and pricing

### **3.6 Customer & Invoice Management** (New Module)

#### **3.6.1 Customer Management**

-   **REQ-CUS-001:** The system shall maintain customer records
-   **REQ-CUS-002:** The system shall track customer interactions and history

#### **3.6.2 Invoice Management**

-   **REQ-CUS-003:** The system shall create and manage invoices
-   **REQ-CUS-004:** The system shall track invoice items and pricing
-   **REQ-CUS-005:** The system shall record invoice payments
-   **REQ-CUS-006:** The system shall generate invoice reports

### **3.7 Portal Systems** (New Module)

#### **3.7.1 Employee Portal**

-   **REQ-PTL-001:** Employees shall view their payslips and download as PDF
-   **REQ-PTL-002:** Employees shall view attendance records
-   **REQ-PTL-003:** Employees shall apply for leave requests
-   **REQ-PTL-004:** Employees shall view leave balances and history

#### **3.7.2 Manager Portal**

-   **REQ-PTL-005:** Managers shall view team attendance
-   **REQ-PTL-006:** Managers shall approve/reject leave requests
-   **REQ-PTL-007:** Managers shall access team reports
-   **REQ-PTL-008:** Managers shall view department analytics

### **3.8 Setup & Configuration** (Enhanced)

#### **3.8.1 System Setup**

-   **REQ-CFG-001:** The system shall provide multi-step setup wizard
-   **REQ-CFG-002:** The system shall guide organization setup
-   **REQ-CFG-003:** The system shall assist in Chart of Accounts setup
-   **REQ-CFG-004:** The system shall help configure inventory stores
-   **REQ-CFG-005:** The system shall support initial data import via CSV

### **3.9 Integration Requirements** (CRITICAL)

#### **3.9.1 Cross-Module Data Flow**

-   **REQ-INT-001:** Payroll system shall generate salary vouchers in Accounting module
-   **REQ-INT-002:** Inventory transactions affecting stock value shall post to Accounting
-   **REQ-INT-003:** Membership fee payments shall record as cash receipts in Accounting
-   **REQ-INT-004:** Customer invoice payments shall update accounts receivable
-   **REQ-INT-005:** Employee loans/advances shall reflect in both HR and Accounting
-   **REQ-INT-006:** All financial transactions shall respect the active financial year

#### **3.9.2 Data Consistency**

-   **REQ-INT-007:** Organization units shall be synchronized across all modules
-   **REQ-INT-008:** User permissions shall apply consistently module-to-module
-   **REQ-INT-009:** All date-based operations shall use system-configured timezone
-   **REQ-INT-010:** Currency and decimal formatting shall be consistent

## **4. Non-functional Requirements** (Enhanced)

### **4.1 Performance**

-   Page loads: 95% under 2 seconds (broadband connection)
-   API responses: 95% under 1 second
-   PDF generation: Under 5 seconds for standard reports
-   CSV export: Under 10 seconds for up to 10,000 records

### **4.2 Reliability & Availability**

-   System uptime: 99.9% monthly availability
-   Data backup: Automated daily backups
-   Recovery: System restorable from backup within 4 hours
-   Error rate: Less than 0.1% of transactions

### **4.3 Security**

-   Data isolation between tenants enforced at database level
-   RBAC with granular permissions
-   All sensitive operations logged
-   Password policies enforced
-   SQL injection and XSS protection
-   CSRF protection on all forms

### **4.4 Scalability**

-   Support 1000+ concurrent users
-   Support 100+ tenants
-   Database performance maintained with 1M+ records per major table
-   Modular architecture allows horizontal scaling

### **4.5 Maintainability**

-   Code coverage: Minimum 80% test coverage for critical paths
-   Documentation: All public APIs and complex business logic documented
-   Modular design: Clear separation between modules
-   Configuration: All tenant-specific settings configurable via UI
-   Updates: System updatable without downtime for tenants

### **4.6 Usability**

-   Responsive design: Works on desktop, tablet, mobile
-   Consistent UI patterns across modules
-   Intuitive navigation with breadcrumbs
-   Comprehensive search across major entities
-   Keyboard shortcuts for power users
-   Accessible following WCAG 2.1 Level AA

### **4.7 Data Portability**

-   All major data entities exportable as CSV
-   Export includes relational data integrity
-   Import supports validation and error reporting
-   Backup/restore via CSV files possible

## **5. User Interaction and Workflows** (Key Additions)

### **5.1 Multi-Tenant Onboarding**

1. **Super Admin** creates new tenant organization
2. **Setup Wizard** guides through initial configuration
3. **Organization Structure** defined (units, departments)
4. **Users Invited** with appropriate roles
5. **Module Activation** based on subscription
6. **Initial Data Import** via CSV templates

### **5.2 Integrated Payroll-to-Accounting**

1. **HR Manager** processes payroll run
2. **System calculates** salaries with allowances/deductions
3. **Payroll entries** created in HR module
4. **Salary vouchers** automatically generated in Accounting
5. **Vouchers posted** to general ledger
6. **Payslips generated** for employees
7. **Employees access** payslips via portal

### **5.3 Inventory Stock Transfer**

1. **Store Manager** initiates transfer between stores
2. **System validates** available stock
3. **Transfer transaction** recorded in Inventory
4. **Accounting entries** created for stock movement (if configured)
5. **Stock levels updated** in both stores
6. **Notifications sent** to relevant personnel

### **5.4 Membership Fee Collection**

1. **Member** subscription due or fee payable
2. **Membership Admin** records payment
3. **System creates** cash receipt in Accounting
4. **Member status updated** (active/paid)
5. **Receipt generated** for member
6. **Accounting ledger** updated

### **5.5 Bank Reconciliation**

1. **Accountant imports** bank statement (CSV)
2. **System matches** transactions with existing entries
3. **Unmatched items** flagged for review
4. **Accountant reconciles** manually where needed
5. **Reconciliation report** generated
6. **Journal entries** created for adjustments

## **6. Development Priority & QoL Features**

### **6.1 High Priority (Robustness)**

1. **Comprehensive error handling** across all modules
2. **Data validation** at API and UI levels
3. **Transaction integrity** for financial operations
4. **Audit logging** for all critical operations
5. **Backup/restore system** using CSV exports
6. **Integration testing** between modules

### **6.2 Medium Priority (Developer QoL)**

1. **CSV import/export system** with templates and validation
2. **Improved setup wizard** with progress tracking
3. **Reusable UI components** library
4. **API documentation** generation
5. **Development utilities** (data generators, test helpers)

### **6.3 Future Consideration**

1. **SEO-optimized marketing pages** for subscription plans
2. **Module marketplace architecture** (WordPress-like)
3. **Webhook system** for external integrations
4. **Mobile apps** for specific roles (inventory clerks, etc.)
5. **Advanced analytics** and BI integration

## **7. Appendices**

### **7.1 Module Interdependencies**

```
Accounting ← Payroll (salary vouchers)
Accounting ← Inventory (stock valuation)
Accounting ← Membership (fee payments)
Accounting ← Customers (invoice payments)
HR → Portal (employee data)
Inventory → Portal (stock alerts for managers)
All Modules → Organization (tenancy scope)
```

### **7.2 CSV Export Specifications**

-   All exports include tenant identifier
-   Date formats standardized (YYYY-MM-DD)
-   Currency amounts include ISO currency code
-   Relationships preserved via foreign keys in export
-   Metadata included in separate manifest file

### **7.3 Testing Requirements**

-   Unit tests for all services and models
-   Feature tests for all user workflows
-   Integration tests for module interactions
-   Performance tests for critical operations
-   Security tests for permission enforcement

### **7.4 Deployment Considerations**

-   Tenancy can be hidden for single-organization deployments
-   Modules can be disabled/enabled per tenant
-   White-labeling support for resellers
-   Multi-language support (future)
-   Regional compliance (tax laws, data protection)

---

**Document Status:** Current as of implementation analysis. This SRS reflects the evolved state of the ERP SaaS application with focus on robustness, integration integrity, and developer quality of life while maintaining modular architecture for future expansion.
