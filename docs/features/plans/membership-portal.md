# Membership Portal Implementation Plan

## 📋 Project Overview

**Project**: HRM Laravel Base ERP System - Membership Portal Module  
**Current State**: Core ERP modules complete (Financial, HR, Inventory, Organization)  
**Goal**: Implement comprehensive membership management with family tracking, card printing, barcode scanning, subscription management, and financial integration

## 🎯 Phase 1: Database Schema & Models

### 1.1 Core Database Tables

```sql
-- Members table
CREATE TABLE members (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    organization_id BIGINT NOT NULL,
    membership_number VARCHAR(50) UNIQUE NOT NULL,
    title VARCHAR(10),
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other'),
    email VARCHAR(255),
    phone VARCHAR(50),
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    postal_code VARCHAR(20),
    country VARCHAR(100),
    barcode_number VARCHAR(100) UNIQUE NOT NULL,
    photo_path VARCHAR(255),
    status ENUM('active', 'inactive', 'suspended', 'expired') DEFAULT 'active',
    join_date DATE NOT NULL,
    expiry_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (organization_id) REFERENCES organizations(id)
);

-- Family Members table
CREATE TABLE family_members (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    organization_id BIGINT NOT NULL,
    primary_member_id BIGINT NOT NULL,
    relationship VARCHAR(50) NOT NULL,
    title VARCHAR(10),
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other'),
    barcode_number VARCHAR(100) UNIQUE NOT NULL,
    photo_path VARCHAR(255),
    status ENUM('active', 'inactive', 'suspended', 'expired') DEFAULT 'active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (organization_id) REFERENCES organizations(id),
    FOREIGN KEY (primary_member_id) REFERENCES members(id)
);

-- Subscription Plans table
CREATE TABLE subscription_plans (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    organization_id BIGINT NOT NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    plan_type ENUM('individual', 'family', 'corporate') NOT NULL,
    billing_frequency ENUM('monthly', 'quarterly', 'semi_annually', 'annually') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    family_members_included INT DEFAULT 0,
    additional_family_member_fee DECIMAL(10,2) DEFAULT 0.00,
    benefits JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (organization_id) REFERENCES organizations(id)
);

-- Member Subscriptions table
CREATE TABLE member_subscriptions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    organization_id BIGINT NOT NULL,
    member_id BIGINT NOT NULL,
    subscription_plan_id BIGINT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('active', 'expired', 'cancelled', 'suspended') DEFAULT 'active',
    total_amount DECIMAL(10,2) NOT NULL,
    paid_amount DECIMAL(10,2) DEFAULT 0.00,
    auto_renew BOOLEAN DEFAULT FALSE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (organization_id) REFERENCES organizations(id),
    FOREIGN KEY (member_id) REFERENCES members(id),
    FOREIGN KEY (subscription_plan_id) REFERENCES subscription_plans(id)
);

-- Member Fees table
CREATE TABLE member_fees (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    organization_id BIGINT NOT NULL,
    member_id BIGINT NOT NULL,
    fee_type ENUM('subscription', 'late_fee', 'penalty', 'additional_service') NOT NULL,
    description VARCHAR(255),
    amount DECIMAL(10,2) NOT NULL,
    due_date DATE NOT NULL,
    paid_date DATE NULL,
    status ENUM('pending', 'paid', 'waived', 'overdue') DEFAULT 'pending',
    payment_method VARCHAR(50),
    payment_reference VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (organization_id) REFERENCES organizations(id),
    FOREIGN KEY (member_id) REFERENCES members(id)
);
```

### 1.2 Model Implementation Tasks

-   [ ] Create `App\Models\Membership\Member` model with relationships
-   [ ] Create `App\Models\Membership\FamilyMember` model with relationships
-   [ ] Create `App\Models\Membership\SubscriptionPlan` model
-   [ ] Create `App\Models\Membership\MemberSubscription` model
-   [ ] Create `App\Models\Membership\MemberFee` model
-   [ ] Add `BelongsToOrganization` trait to all models
-   [ ] Implement soft deletes for audit trail
-   [ ] Create model factories for testing

## 🎯 Phase 2: Business Logic & Services

### 2.1 Core Service Classes

```php
// MembershipService.php
class MembershipService
{
    public function createMember(array $data): Member
    public function updateMember(Member $member, array $data): Member
    public function generateMembershipNumber(): string
    public function generateBarcodeNumber(): string
    public function addFamilyMember(Member $member, array $data): FamilyMember
    public function deactivateMember(Member $member): bool
}

// SubscriptionService.php
class SubscriptionService
{
    public function createSubscription(Member $member, SubscriptionPlan $plan): MemberSubscription
    public function renewSubscription(MemberSubscription $subscription): MemberSubscription
    public function cancelSubscription(MemberSubscription $subscription): bool
    public function calculateSubscriptionFee(SubscriptionPlan $plan, int $familyMembers): float
}

// FeeService.php
class FeeService
{
    public function createFee(Member $member, array $data): MemberFee
    public function processFeePayment(MemberFee $fee, array $paymentData): bool
    public function distributeFeeToAccounts(MemberFee $fee): bool
    public function generateOverdueFees(): Collection
}

// CardPrintingService.php
class CardPrintingService
{
    public function generateMemberCard(Member $member): string
    public function generateFamilyMemberCard(FamilyMember $familyMember): string
    public function createBatchCards(Collection $members): string
    public function exportCardsToPdf(Collection $cards): string
}
```

### 2.2 Service Implementation Tasks

-   [ ] Implement `App\Services\Membership\MembershipService`
-   [ ] Implement `App\Services\Membership\SubscriptionService`
-   [ ] Implement `App\Services\Membership\FeeService`
-   [ ] Implement `App\Services\Membership\CardPrintingService`
-   [ ] Add barcode generation functionality
-   [ ] Integrate with accounting system for fee distribution
-   [ ] Implement subscription renewal logic
-   [ ] Add family member validation rules

## 🎯 Phase 3: Controllers & API Endpoints

### 3.1 Web Controllers

```php
// MemberController.php
class MemberController extends Controller
{
    public function index()
    public function create()
    public function store(StoreMemberRequest $request)
    public function show(Member $member)
    public function edit(Member $member)
    public function update(UpdateMemberRequest $request, Member $member)
    public function destroy(Member $member)
    public function printCard(Member $member)
    public function addFamilyMember(StoreFamilyMemberRequest $request, Member $member)
}

// SubscriptionController.php
class SubscriptionController extends Controller
{
    public function index()
    public function create(Member $member)
    public function store(StoreSubscriptionRequest $request, Member $member)
    public function show(MemberSubscription $subscription)
    public function renew(MemberSubscription $subscription)
    public function cancel(MemberSubscription $subscription)
}

// FeeController.php
class FeeController extends Controller
{
    public function index()
    public function create(Member $member)
    public function store(StoreFeeRequest $request)
    public function processPayment(ProcessFeePaymentRequest $request, MemberFee $fee)
    public function receipt(MemberFee $fee)
}
```

### 3.2 API Controllers

```php
// Api/MemberController.php
class MemberController extends BaseController
{
    public function index()
    public function store(StoreMemberApiRequest $request)
    public function show(Member $member)
    public function update(UpdateMemberApiRequest $request, Member $member)
    public function scanBarcode(ScanBarcodeRequest $request)
}

// Api/SubscriptionController.php
class SubscriptionController extends BaseController
{
    public function index()
    public function store(StoreSubscriptionApiRequest $request)
    public function show(MemberSubscription $subscription)
}
```

### 3.3 Controller Implementation Tasks

-   [ ] Create `App\Http\Controllers\Membership\MemberController`
-   [ ] Create `App\Http\Controllers\Membership\SubscriptionController`
-   [ ] Create `App\Http\Controllers\Membership\FeeController`
-   [ ] Create API controllers for mobile/scanner integration
-   [ ] Implement form request validation classes
-   [ ] Add resource authorization policies
-   [ ] Create barcode scanning endpoint
-   [ ] Add card printing endpoints

## 🎯 Phase 4: Livewire Components

### 4.1 Member Management Components

```php
// MemberList.php
class MemberList extends Component
{
    public Collection $members;
    public string $search = '';
    public string $status = 'all';
    
    public function render()
    public function deleteMember(int $memberId)
    public function printMemberCard(int $memberId)
}

// MemberForm.php
class MemberForm extends Component
{
    public Member $member;
    public bool $editMode = false;
    
    public function mount(?Member $member = null)
    public function save()
    public function addFamilyMember()
}

// SubscriptionManager.php
class SubscriptionManager extends Component
{
    public Member $member;
    public Collection $availablePlans;
    public MemberSubscription $subscription;
    
    public function mount(Member $member)
    public function subscribe(int $planId)
    public function renewSubscription()
    public function cancelSubscription()
}
```

### 4.2 Card Printing Components

```php
// CardDesigner.php
class CardDesigner extends Component
{
    public Member $member;
    public string $cardTemplate = 'default';
    public bool $previewMode = false;
    
    public function mount(Member $member)
    public function generatePreview()
    public function printCard()
    public function downloadPdf()
}

// BatchCardPrinting.php
class BatchCardPrinting extends Component
{
    public array $selectedMembers = [];
    public string $cardTemplate = 'default';
    
    public function render()
    public function generateBatchCards()
    public function downloadBatchPdf()
}
```

### 4.3 Livewire Implementation Tasks

-   [ ] Create member list and management components
-   [ ] Implement member form with family member support
-   [ ] Build subscription management interface
-   [ ] Create card designer and printing components
-   [ ] Add batch card printing functionality
-   [ ] Implement barcode scanning interface
-   [ ] Create fee payment processing components
-   [ ] Add member search and filtering

## 🎯 Phase 5: Views & Frontend

### 5.1 Blade Templates Structure

```
resources/views/membership/
├── members/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   ├── show.blade.php
│   └── partials/
│       ├── family-members.blade.php
│       ├── subscription-info.blade.php
│       └── fee-history.blade.php
├── subscriptions/
│   ├── index.blade.php
│   ├── create.blade.php
│   └── show.blade.php
├── fees/
│   ├── index.blade.php
│   ├── payment.blade.php
│   └── receipt.blade.php
├── cards/
│   ├── designer.blade.php
│   ├── preview.blade.php
│   ├── templates/
│   │   ├── default.blade.php
│   │   ├── premium.blade.php
│   │   └── family.blade.php
│   └── batch-printing.blade.php
└── layouts/
    └── membership.blade.php
```

### 5.2 Frontend Implementation Tasks

-   [ ] Create member management views
-   [ ] Build subscription management interface
-   [ ] Design fee payment and receipt views
-   [ ] Implement card designer interface
-   [ ] Create card template views
-   [ ] Add barcode display components
-   [ ] Implement responsive design
-   [ ] Add dark mode support

## 🎯 Phase 6: Accounting Integration

### 6.1 Financial Distribution Logic

```php
// FeeDistributionService.php
class FeeDistributionService
{
    public function distributeMemberFee(MemberFee $fee): bool
    {
        // Create journal entry for fee payment
        $journalEntry = $this->createJournalEntry($fee);
        
        // Distribute to appropriate accounts
        $this->debitAccountsReceivable($journalEntry, $fee->amount);
        $this->creditMembershipRevenue($journalEntry, $fee->amount);
        
        // Handle payment processing
        if ($fee->isPaid()) {
            $this->processPayment($fee);
        }
        
        return true;
    }
    
    private function createJournalEntry(MemberFee $fee): JournalEntry
    {
        return JournalEntry::create([
            'organization_id' => $fee->organization_id,
            'date' => $fee->due_date,
            'description' => "Member Fee: {$fee->member->full_name}",
            'reference' => $fee->payment_reference,
            'total_debit' => $fee->amount,
            'total_credit' => $fee->amount,
        ]);
    }
}
```

### 6.2 Chart of Accounts Setup

```php
// Default membership accounts
$membershipAccounts = [
    [
        'name' => 'Membership Fees Receivable',
        'code' => '1200',
        'type' => 'asset',
        'category' => 'accounts_receivable',
    ],
    [
        'name' => 'Membership Revenue',
        'code' => '4000',
        'type' => 'revenue',
        'category' => 'membership_income',
    ],
    [
        'name' => 'Subscription Revenue',
        'code' => '4010',
        'type' => 'revenue',
        'category' => 'membership_income',
    ],
];
```

### 6.3 Accounting Integration Tasks

-   [ ] Implement fee distribution service
-   [ ] Create membership-specific chart of accounts
-   [ ] Add journal entry creation for fee payments
-   [ ] Integrate with existing voucher system
-   [ ] Add financial reporting for membership
-   [ ] Create membership revenue analytics
-   [ ] Implement tax handling for membership fees
-   [ ] Add audit trail for financial transactions

## 🎯 Phase 7: Barcode & Card System

### 7.1 Barcode Generation

```php
// BarcodeService.php
class BarcodeService
{
    public function generateMemberBarcode(Member $member): string
    {
        $barcodeData = "MEM-{$member->organization_id}-{$member->id}";
        return $this->generateBarcodeImage($barcodeData);
    }
    
    public function generateFamilyMemberBarcode(FamilyMember $familyMember): string
    {
        $barcodeData = "FAM-{$familyMember->organization_id}-{$familyMember->id}";
        return $this->generateBarcodeImage($barcodeData);
    }
    
    private function generateBarcodeImage(string $data): string
    {
        // Use DNS2D or similar barcode library
        $generator = new DNS2D();
        return $generator->getBarcodePNG($data, 'QRCODE', 3, 3);
    }
}
```

### 7.2 Card Templates

```php
// CardTemplateService.php
class CardTemplateService
{
    public function renderMemberCard(Member $member, string $template = 'default'): string
    {
        $data = [
            'member' => $member,
            'barcode' => $this->barcodeService->generateMemberBarcode($member),
            'expiryDate' => $member->expiry_date,
            'organization' => $member->organization,
        ];
        
        return view("membership.cards.templates.{$template}", $data)->render();
    }
}
```

### 7.3 Barcode & Card Implementation Tasks

-   [ ] Install and configure barcode generation library
-   [ ] Implement barcode service for members and family members
-   [ ] Create card template system
-   [ ] Design default, premium, and family card templates
-   [ ] Add PDF generation for card printing
-   [ ] Implement batch card printing
-   [ ] Create barcode scanning validation
-   [ ] Add card expiry tracking

## 🎯 Phase 8: Testing Suite

### 8.1 Feature Tests

```php
// MemberManagementTest.php
class MemberManagementTest extends TestCase
{
    use RefreshDatabase, SetupOrganization;
    
    public function test_can_create_member()
    public function test_can_add_family_member()
    public function test_can_generate_membership_number()
    public function test_can_generate_barcode()
    public function test_can_deactivate_member()
}

// SubscriptionTest.php
class SubscriptionTest extends TestCase
{
    use RefreshDatabase, SetupOrganization;
    
    public function test_can_create_subscription()
    public function test_can_renew_subscription()
    public function test_can_cancel_subscription()
    public function test_subscription_expiry_handling()
}

// FeePaymentTest.php
class FeePaymentTest extends TestCase
{
    use RefreshDatabase, SetupOrganization;
    
    public function test_can_create_member_fee()
    public function test_can_process_fee_payment()
    public function test_fee_distributes_to_accounts()
    public function test_overdue_fee_generation()
}
```

### 8.2 Unit Tests

```php
// MembershipServiceTest.php
class MembershipServiceTest extends TestCase
{
    public function test_membership_number_generation()
    public function test_barcode_number_generation()
    public function test_family_member_validation()
    public function test_member_status_transitions()
}

// FeeDistributionServiceTest.php
class FeeDistributionServiceTest extends TestCase
{
    public function test_journal_entry_creation()
    public function test_account_debit_credit()
    public function test_payment_processing()
}
```

### 8.3 Testing Implementation Tasks

-   [ ] Create comprehensive feature tests for all functionality
-   [ ] Implement unit tests for business logic
-   [ ] Add API endpoint testing
-   [ ] Create Livewire component tests
-   [ ] Test barcode generation and validation
-   [ ] Test accounting integration
-   [ ] Add performance tests for large member databases
-   [ ] Create test data factories and seeders

## 🎯 Phase 9: Permissions & Security

### 9.1 Permission Definitions

```php
// MembershipPermissions.php
class MembershipPermissions
{
    const VIEW_MEMBERS = 'membership.view_members';
    const CREATE_MEMBERS = 'membership.create_members';
    const EDIT_MEMBERS = 'membership.edit_members';
    const DELETE_MEMBERS = 'membership.delete_members';
    const MANAGE_SUBSCRIPTIONS = 'membership.manage_subscriptions';
    const PROCESS_PAYMENTS = 'membership.process_payments';
    const PRINT_CARDS = 'membership.print_cards';
    const SCAN_BARCODES = 'membership.scan_barcodes';
}
```

### 9.2 Security Implementation Tasks

-   [ ] Define membership-specific permissions
-   [ ] Create authorization policies
-   [ ] Implement role-based access control
-   [ ] Add data validation and sanitization
-   [ ] Secure barcode scanning endpoints
-   [ ] Implement audit logging
-   [ ] Add rate limiting for API endpoints
-   [ ] Ensure GDPR compliance for member data

## 🎯 Phase 10: Reporting & Analytics

### 10.1 Membership Reports

```php
// MembershipReportService.php
class MembershipReportService
{
    public function generateMemberCountReport(): array
    public function generateRevenueReport(DatePeriod $period): array
    public function generateSubscriptionReport(): array
    public function generateExpiryReport(): array
    public function exportToExcel(array $data, string $reportType): string
}
```

### 10.2 Dashboard Components

```php
// MembershipDashboard.php
class MembershipDashboard extends Component
{
    public function render()
    {
        return view('livewire.membership.dashboard', [
            'totalMembers' => Member::count(),
            'activeSubscriptions' => MemberSubscription::where('status', 'active')->count(),
            'monthlyRevenue' => $this->calculateMonthlyRevenue(),
            'expiringThisMonth' => $this->getExpiringMembers(),
        ]);
    }
}
```

### 10.3 Reporting Implementation Tasks

-   [ ] Create membership analytics service
-   [ ] Build dashboard components
-   [ ] Implement revenue tracking
-   [ ] Add membership growth reports
-   [ ] Create subscription analytics
-   [ ] Add expiry tracking reports
-   [ ] Implement export functionality
-   [ ] Create visual charts and graphs

## 🛠 Implementation Timeline

### Sprint 1: Foundation (2 weeks)
-   [ ] Database schema and models
-   [ ] Basic service classes
-   [ ] Core controllers
-   [ ] Basic views

### Sprint 2: Core Features (2 weeks)
-   [ ] Member management functionality
-   [ ] Family member system
-   [ ] Subscription management
-   [ ] Fee processing

### Sprint 3: Advanced Features (2 weeks)
-   [ ] Card printing system
-   [ ] Barcode generation
-   [ ] Accounting integration
-   [ ] Permission system

### Sprint 4: Polish & Testing (2 weeks)
-   [ ] Comprehensive testing
-   [ ] Reporting and analytics
-   [ ] Performance optimization
-   [ ] Documentation

## 📊 Success Metrics

### Functional Requirements
-   [ ] 100% member data management capability
-   [ ] Complete family member tracking
-   [ ] Functional barcode scanning system
-   [ ] Professional card printing
-   [ ] Seamless accounting integration

### Quality Standards
-   [ ] 95%+ test coverage
-   [ ] Page load < 2s for member lists
-   [ ] Barcode scan < 500ms response
-   [ ] Zero critical security vulnerabilities
-   [ ] Complete audit trail

## 🔧 Maintenance Plan

### Regular Tasks
-   **Daily**: Membership expiry monitoring
-   **Weekly**: Revenue report generation
-   **Monthly**: Card template updates
-   **Quarterly**: Barcode system maintenance

### Automation
-   Automatic subscription renewal reminders
-   Overdue fee generation and notifications
-   Member status updates based on expiry
-   Financial reconciliation automation

---

**Next Step**: Begin Sprint 1 implementation by creating database migrations and core models.

**Agent Instructions**: Follow this plan sequentially, starting with Phase 1. Each phase should be completed and tested before moving to the next. Report progress after each phase completion and seek clarification if any step is unclear.