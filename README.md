# Pharma HRM + Accounts System

A comprehensive Laravel-based Human Resource Management and Accounting system designed specifically for pharmaceutical manufacturing companies.

## 🏗️ System Architecture

### Core Foundation

-   **Organizational Hierarchy**: Departments → Teams → Positions → Employees
-   **Multi-dimensional Accounting**: Cost centers, projects, departments
-   **Double-Entry Bookkeeping**: Full GAAP-compliant accounting

### Technology Stack

-   **Backend**: Laravel 12 + PHP 8.2+
-   **Database**: MySQL/PostgreSQL with transactions
-   **Testing**: PHPUnit with 100% test coverage
-   **Authentication**: Laravel Sanctum for API
-   **Frontend**: Livewire + Alpine.js
-

## 📦 Modules Under Implementation

### 🚧 Organizational Structure Base Module

-   Department, team, position, and employee management
-   Organizational chart visualization
-   Role-based access control
-   Multi-dimensional organizational structure

### ✅ Accounting Module (Complete)

-   Chart of Accounts management
-   Double-entry ledger system
-   Journal entries with approval workflow
-   Financial reporting:
    -   Trial Balance
    -   Balance Sheet
    -   Income Statement
-   Organizational dimension tracking
-   Sequence-based numbering system
-   API for Accounting Service

### 🚧 Upcoming Modules

-   Human Resources Management
-   Sales & Invoicing
-   Procurement & Inventory
-   Manufacturing & Production
-   Quality Control

## 🚀 Quick Start

### Prerequisites

```bash
php >= 8.2
composer
mysql >= 8.0
node.js >= 18
```

### Installation

```bash
# Clone repository
git clone <repository-url>
cd hrm-laravel-base

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Configure database in .env
DB_CONNECTION=sqlite
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=pharma_hrm
# DB_USERNAME=your_username
# DB_PASSWORD=your_password

# Run migrations and seeders
php artisan migrate --seed

# Start development server
composer run dev
```

### Testing

```bash
# Run all tests
php artisan test

# Run specific test suites
php artisan test --testsuite=Unit --filter=Accounting
php artisan test --testsuite=Feature --filter=FinancialReports
```

## 🧪 Testing Data

The system comes with comprehensive seeders:

```bash
# Seed sample data
php artisan db:seed

# Sample credentials
Email: test@example.com
Password: password
```

## 🔧 Development Guide

### Code Structure

```
app/
├── Models/
│   ├── Accounting/          # Accounting entities
│   ├── HR/                 # Human Resources
│   └── Inventory/          # Inventory management
├── Services/
│   ├── AccountingService.php
│   ├── SequenceService.php
│   └── ReportingService.php
└── Exceptions/             # Custom exceptions
```

### Development Workflow

1. **Start with Tests**

```bash
php artisan make:test Unit/ModuleName/FeatureTest
```

2. **Implement Core Logic**

```bash
php artisan make:model ModuleName/EntityName -mf
```

3. **Create Services**

```bash
# manually create the service class
# php artisan make:service ModuleNameService
```

4. **Run Tests Continuously**

```bash
php artisan test --filter=ModuleName
```

### Key Patterns

-   **Repository Pattern**: Data access abstraction
-   **Service Layer**: Business logic encapsulation
-   **DTOs**: Data transfer objects for APIs
-   **Observer Pattern**: Event-driven side effects
-   **Factory Pattern**: Test data generation

## 📊 Database Schema

### Core Tables

```sql
-- Organizational structure
departments, teams, positions, employees

-- Accounting foundation
chart_of_accounts, ledger_entries, journal_entries

-- Dimension tracking
dimensions, dimensionables

-- Sequence management
sequences
```

### Relationships

-   **Polymorphic Relations**: Transactions → Ledger Entries
-   **Many-to-Many**: Dimensions ↔ Ledger Entries
-   **Hierarchical**: Departments → Teams → Employees

## 🔐 Security Features

-   Role-Based Access Control (RBAC)
-   API token authentication
-   Transaction auditing...?
-   Data encryption at rest...?
-   SQL injection prevention...?
-   XSS protection...?

## 📈 Reporting Capabilities

### Financial Reports

-   **Real-time Trial Balance**
-   **Balance Sheet** (Assets = Liabilities + Equity)
-   **Income Statement** (Revenue - Expenses)
-   **Departmental P&L** reports
-   **Budget vs Actual** analysis

### HR Reports

-   Employee headcount by department
-   Salary expenditure reports
-   Attendance and leave tracking
-   Performance metrics

## 🔄 API Endpoints

### Accounting API

```http
GET    /api/accounts          # List chart of accounts
POST   /api/journal-entries   # Create journal entry
GET    /api/reports/balance-sheet
GET    /api/reports/income-statement
```

### HR API

```http
GET    /api/employees         # List employees
POST   /api/employees         # Create employee
GET    /api/departments       # Organizational structure
```

## 🚀 Deployment

### Production Setup

```bash
# Optimize for production
composer install --optimize-autoloader --no-dev
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Environment setup
APP_ENV=production
APP_DEBUG=false
```

### Docker Support

```dockerfile
# Coming soon: Docker containerization
# Multi-stage build for production
```

## 📋 Development Roadmap

### Phase 1A: Core Accounting

-   [ ] Double-entry bookkeeping system
-   [ ] Financial reporting engine
-   [ ] Organizational dimension tracking
-   [ ] Sequence-based numbering

### Phase 1B: Core Organizational Structure

-   [ ] Double-entry bookkeeping system
-   [ ] Financial reporting engine
-   [ ] Organizational dimension tracking
-   [ ] Sequence-based numbering

### Phase 2: HR Management (Next)

-   Employee management
-   Payroll processing
-   Attendance tracking
-   Performance reviews

### Phase 3: Sales & Procurement

-   Customer management
-   Invoice generation
-   Purchase orders
-   Inventory management

### Phase 4: Manufacturing

-   Production planning
-   Quality control
-   Batch tracking
-   Compliance reporting

## 🛠️ Customization

### Adding New Account Types

```php
// config/accounting.php
return [
    'account_types' => [
        'asset', 'liability', 'equity', 'revenue', 'expense',
        // Add custom types here
    ],
];
```

### Custom Dimensions

```php
// app/Providers/AppServiceProvider.php
Dimension::create([
    'name' => 'Research Projects',
    'code' => 'PROJ-RES',
    'type' => 'project'
]);
```

## 🆘 Support

### Common Issues

1. **Sequence gaps**: Use `SequenceService::reserve()` pattern
2. **Transaction errors**: Ensure database supports transactions
3. **Test failures**: Run `php artisan optimize:clear`

### Debug Mode

```bash
APP_DEBUG=true php artisan serve
```

## 📄 License

Proprietary Software - © 2024 Pharma Solutions Inc.

---

**Next Steps**:

1. Set up development environment
2. Run initial migrations and seeders
3. Explore accounting module functionality
4. Begin HR module development

## 🎉 Congratulations! You've successfully installed Pharma HRM + Accounts System.
