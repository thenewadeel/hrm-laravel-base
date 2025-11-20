# AGENTS.md - Development Guidelines for HRM Laravel Base ERP System

## Project Overview
**HRM Laravel Base** is a comprehensive, production-ready ERP system that has evolved from a simple HRM concept into a full-featured business management platform. The system follows a multi-tenant architecture with complete data isolation and supports Financial Management, Human Resources, Inventory Management, and Organization Management modules.

## Build/Lint/Test Commands
- **Run all tests**: `composer test` (outputs to docs/testResults.txt)
- **Run single test**: `php artisan test --filter=TestClass::testMethod`
- **Run specific test file**: `php artisan test tests/Feature/SpecificTest.php`
- **Lint PHP code**: `vendor/bin/pint` (Laravel Pint code formatter)
- **Build frontend assets**: `npm run build`
- **Development server**: `composer run dev` (runs Laravel, queue, logs, and Vite concurrently)
- **Database migrations**: `php artisan migrate`
- **Seed demo data**: `php artisan db:seed --class=DemoDataSeeder`

## Architecture Overview

### Multi-Tenant Structure
- **Organization-based isolation**: All business data is scoped to organizations
- **Shared system tables**: Users, teams, and other system-wide data
- **Role-based access control**: Granular permissions per organization
- **Data integrity**: Foreign key constraints and soft deletes

### Module Organization
```
app/
├── Actions/          # Laravel Actions (Fortify, Jetstream)
├── Console/Commands/  # Artisan commands
├── Exceptions/        # Custom exception classes
├── Http/
│   ├── Controllers/   # HTTP controllers by feature
│   ├── Middleware/    # Custom middleware
│   └── Requests/      # Form request validation
├── Livewire/         # Livewire components by module
│   ├── Accounting/    # Financial management components
│   ├── Organization/  # Organization management
│   └── Traits/        # Shared Livewire traits
├── Models/           # Eloquent models
│   ├── Accounting/   # Financial models
│   ├── Inventory/    # Inventory models
│   ├── Scopes/       # Query scopes
│   └── Traits/       # Model traits
├── Permissions/      # Permission definitions
├── Policies/         # Authorization policies
├── Roles/           # Role definitions
├── Services/        # Business logic services
└── View/Components/ # Blade view components
```

## Code Style Guidelines

### PHP/Laravel Conventions
- **Namespaces**: PSR-4 (`App\` for application code, `Tests\` for tests)
- **Imports**: Group by type (classes, traits, interfaces); alphabetize within groups
- **Indentation**: 4 spaces (per .editorconfig)
- **Naming**: PascalCase for classes/methods, camelCase for variables/properties
- **DocBlocks**: Use PHPDoc for classes/methods with descriptions
- **Error Handling**: Use Laravel's exception handling; validate requests with `Request::validate()`
- **Database**: Use Eloquent models with proper relationships and scopes

### Model Conventions
- **Organization scoping**: All business models must use `BelongsToOrganization` trait
- **Soft deletes**: Use soft deletes for business data retention
- **Casts**: Use `casts()` method instead of `$casts` property (Laravel 12+)
- **Relationships**: Always define return types for relationship methods
- **Factories**: Create factories for all models with realistic test data

### Service Layer Patterns
- **Single responsibility**: Each service handles one business domain
- **Dependency injection**: Use constructor property promotion
- **Transaction management**: Use database transactions for multi-table operations
- **Event handling**: Dispatch events for significant business actions
- **Validation**: Validate data in services, not just controllers

### Livewire Component Guidelines
- **Single root element**: All components must have one root element
- **Wire keys**: Always use `wire:key` in loops for proper reactivity
- **Loading states**: Use `wire:loading` and `wire:dirty` for UX
- **Authorization**: Check permissions in component methods
- **Validation**: Use Livewire's built-in validation with proper error messages

### Testing Standards
- **Framework**: Pest/PHPUnit with `#[Test]` attributes
- **Traits**: Use `RefreshDatabase` for database tests
- **Setup**: Use `setUp()` methods for common test initialization
- **Assertions**: Prefer specific assertions (`assertDatabaseHas`, `assertStatus`, etc.)
- **Factories/Seeders**: Use for test data creation
- **Coverage**: Maintain 85%+ test coverage for critical business logic

### Frontend/JavaScript
- **Build Tool**: Vite with Laravel Vite plugin
- **Styling**: Tailwind CSS with custom components
- **JavaScript**: ES6+ modules, axios for API calls
- **Dark mode**: Support dark mode in all new components
- **Responsive**: Mobile-first design approach
- **Accessibility**: WCAG 2.1 compliance

### File Organization
- **Controllers**: Group by feature (e.g., `Attendance/AttendanceController`)
- **Models**: Use traits for shared functionality (e.g., `BelongsToOrganization`)
- **Views**: Blade templates with consistent component structure
- **Routes**: Feature-based grouping in separate files
- **Services**: Business logic separated from controllers
- **Tests**: Mirror application structure in test directories

### Security & Best Practices
- **Validation**: Always validate user input with Form Requests
- **Authorization**: Use Laravel policies and gates for all actions
- **Database**: Use migrations, avoid raw queries when possible
- **Environment**: Never commit secrets, use `.env` files
- **Multi-tenancy**: Ensure all queries are properly scoped to organization
- **Audit trails**: Log important business actions
- **Data privacy**: Implement GDPR-compliant data handling

## Module-Specific Guidelines

### Financial Management (Accounting)
- **Double-entry**: Always maintain balanced debits and credits
- **Chart of Accounts**: Use hierarchical account structure
- **Voucher system**: Implement proper voucher numbering and validation
- **Reporting**: Use proper accounting periods and consolidation
- **Audit trail**: Track all financial transactions with user attribution

### Human Resources
- **Employee lifecycle**: Manage complete employee data with privacy
- **Attendance**: Handle biometric integration and shift management
- **Payroll**: Ensure accurate calculations with proper tax handling
- **Leave management**: Implement approval workflows
- **Performance**: Track performance metrics and reviews

### Inventory Management
- **Multi-store**: Support multiple inventory locations
- **Stock tracking**: Real-time inventory updates with proper costing
- **Transactions**: Record all stock movements (IN, OUT, TRANSFER, ADJUST)
- **Reorder points**: Automated alerts for low stock
- **Valuation**: Support FIFO and weighted average costing methods

### Organization Management
- **Multi-tenancy**: Strict data isolation between organizations
- **Hierarchical structure**: Support complex organizational trees
- **Member management**: Invitation-based member onboarding
- **Role-based access**: Granular permission system
- **Analytics**: Organization-level metrics and reporting

## Database Design Principles

### Schema Organization
- **Business tables**: Prefix with organization_id for multi-tenancy
- **System tables**: Shared across all organizations
- **Foreign keys**: Always define proper foreign key constraints
- **Indexing**: Strategic indexes for performance optimization
- **Soft deletes**: Use for audit trail and data recovery

### Migration Best Practices
- **Column modifications**: Include all existing attributes when modifying columns
- **Rollback support**: Always provide proper down() methods
- **Data integrity**: Use transactions for complex migrations
- **Testing**: Test migrations on both empty and populated databases

## API Development

### RESTful Conventions
- **Resource naming**: Use plural nouns for resource endpoints
- **HTTP methods**: Use appropriate HTTP verbs (GET, POST, PUT, DELETE)
- **Status codes**: Return proper HTTP status codes
- **Error handling**: Consistent error response format
- **Versioning**: Implement API versioning strategy

### Authentication & Authorization
- **Sanctum tokens**: Use Laravel Sanctum for API authentication
- **Scoping**: API responses should be scoped to user's organization
- **Rate limiting**: Implement proper rate limiting for API endpoints
- **Documentation**: Provide comprehensive API documentation

## Performance Optimization

### Database Optimization
- **Eager loading**: Prevent N+1 queries with proper eager loading
- **Query optimization**: Use query scopes and efficient queries
- **Caching**: Implement multi-level caching strategy
- **Connection pooling**: Optimize database connection management

### Frontend Performance
- **Asset optimization**: Minify and compress assets
- **Lazy loading**: Load components on-demand
- **Caching**: Implement browser caching strategies
- **CDN**: Use CDN for static assets in production

## Deployment & DevOps

### Environment Management
- **Configuration**: Use environment-specific configuration files
- **Secrets management**: Never commit sensitive information
- **Backup strategy**: Implement automated backup systems
- **Monitoring**: Set up application and infrastructure monitoring

### Production Deployment
- **Zero downtime**: Use blue-green deployment strategy
- **Rollback capability**: Maintain ability to rollback quickly
- **Health checks**: Implement comprehensive health checks
- **Performance monitoring**: Track application performance metrics

## Quality Assurance

### Code Review Process
- **Static analysis**: Use PHPStan and Laravel Pint
- **Security scanning**: Regular security vulnerability scans
- **Performance testing**: Load testing for critical paths
- **Accessibility testing**: Ensure WCAG compliance

### Testing Strategy
- **Unit tests**: Test individual components in isolation
- **Feature tests**: Test complete user workflows
- **Integration tests**: Test module interactions
- **End-to-end tests**: Test critical business scenarios

## Documentation Standards

### Code Documentation
- **PHPDoc**: Comprehensive documentation for all classes and methods
- **API docs**: Auto-generated API documentation
- **Architecture docs**: Maintain up-to-date architecture documentation
- **User guides**: Create user-friendly documentation for all features

### Project Documentation
- **README**: Keep README.md current with setup instructions
- **Changelog**: Maintain detailed changelog for all releases
- **Architecture diagrams**: Use visual diagrams for complex systems
- **Deployment guides**: Step-by-step deployment instructions</content>
<parameter name="filePath">AGENTS.md

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to enhance the user's satisfaction building Laravel applications.

## Foundational Context
This application is a Laravel-based ERP system and its main Laravel ecosystem package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- **php** - 8.4.12
- **laravel/fortify** (FORTIFY) - v1.31.2
- **laravel/framework** (LARAVEL) - v12.35.1
- **laravel/prompts** (PROMPTS) - v0.3.7
- **laravel/sanctum** (SANCTUM) - v4.2.0
- **livewire/livewire** (LIVEWIRE) - v3.6.4
- **laravel/mcp** (MCP) - v0.3.3
- **laravel/pint** (PINT) - v1.25.1
- **laravel/sail** (SAIL) - v1.46.0
- **pestphp/pest** (PEST) - v3.8.4
- **phpunit/phpunit** (PHPUNIT) - v11.5.33
- **tailwindcss** (TAILWINDCSS) - v3.4.17

## Conventions
- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts
- Do not create verification scripts or tinker when tests cover that functionality and prove it works. Unit and feature tests are more important.

## Application Structure & Architecture
- Stick to existing directory structure - don't create new base folders without approval.
- Do not change the application's dependencies without approval.
- **Current Modules**: Financial Management (95% complete), Human Resources (90% complete), Inventory Management (100% complete), Organization Management (95% complete)
- **Database Engine**: SQLite (development), MySQL (production ready)
- **Multi-tenant Architecture**: Complete organization-based data isolation
- **Portal Ecosystem**: Employee, Manager, HR Admin, and Mobile Kiosk portals

## Frontend Bundling
- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Replies
- Be concise in your explanations - focus on what's important rather than explaining obvious details.

## Documentation Files
- You must only create documentation files if explicitly requested by the user.


=== boost rules ===

## Laravel Boost
- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan
- Use the `list-artisan-commands` tool when you need to call an Artisan command to double check the available parameters.

## URLs
- Whenever you share a project URL with the user you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain / IP, and port.

## Tinker / Debugging
- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool
- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)
- Boost comes with a powerful `search-docs` tool you should use before any other approaches. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation specific for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- The 'search-docs' tool is perfect for all Laravel related packages, including Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, Nightwatch, etc.
- You must use this tool to search for Laravel-ecosystem documentation before falling back to other approaches.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic based queries to start. For example: `['rate limiting', 'routing rate limiting', 'routing']`.
- Do not add package names to queries - package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax
- You can and should pass multiple queries at once. The most relevant results will be returned first.

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit"
3. Quoted Phrases (Exact Position) - query="infinite scroll" - Words must be adjacent and in that order
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit"
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms


=== php rules ===

## PHP

- Always use curly braces for control structures, even if it has one line.

### Constructors
- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function __construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters.

### Type Declarations
- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Comments
- Prefer PHPDoc blocks over comments. Never use comments within the code itself unless there is something _very_ complex going on.

## PHPDoc Blocks
- Add useful array shape type definitions for arrays when appropriate.

## Enums
- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.


=== laravel/core rules ===

## Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Database
- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation
- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources
- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

### Controllers & Validation
- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

### Queues
- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

### Authentication & Authorization
- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

### URL Generation
- When generating links to other pages, prefer named routes and the `route()` function.

### Configuration
- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

### Testing
- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] <name>` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

### Vite Error
- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.


=== laravel/v12 rules ===

## Laravel 12

- Use the `search-docs` tool to get version specific documentation.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

### Laravel 12 Structure
- No middleware files in `app/Http/Middleware/`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- **No app\Console\Kernel.php** - use `bootstrap/app.php` or `routes/console.php` for console configuration.
- **Commands auto-register** - files in `app/Console/Commands/` are automatically available and do not require manual registration.
- **Route files**: Feature-based routing with separate files for each module:
  - `web.php` - Main web routes
  - `api.php` - API routes
  - `accounts.php` - Financial management routes
  - `hrm.php` - Human resources routes
  - `inventory.php` - Inventory management routes
  - `organization.php` - Organization management routes
  - `setup.php` - System setup routes
  - `demo.php` - Demo and testing routes

### Database
- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 11 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.
- **Multi-tenant design**: All business tables include `organization_id` for data isolation
- **Key tables**: organizations, users, employees, chart_of_accounts, journal_entries, ledger_entries, items, stores, transactions
- **Soft deletes**: Implemented on business-critical tables for audit trails
- **Foreign keys**: Comprehensive foreign key constraints for data integrity

### Models
- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.
- **Organization trait**: All business models use `BelongsToOrganization` trait for multi-tenancy
- **Model organization**: 
  - `App\Models\Accounting\` - Financial models (ChartOfAccount, JournalEntry, LedgerEntry)
  - `App\Models\Inventory\` - Inventory models (Item, Store, Transaction, Head)
  - `App\Models\` - Core business models (Employee, Organization, User, etc.)
- **Scopes**: Use `App\Models\Scopes\OrganizationScope` for automatic organization filtering
- **Relationships**: Define proper relationships with return type hints


=== livewire/core rules ===

## Livewire Core
- Use the `search-docs` tool to find exact version specific documentation for how to write Livewire & Livewire tests.
- Use the `php artisan make:livewire [Posts\CreatePost]` artisan command to create new components
- State should live on the server, with the UI reflecting it.
- All Livewire requests hit the Laravel backend, they're like regular HTTP requests. Always validate form data, and run authorization checks in Livewire actions.

## Livewire Best Practices
- Livewire components require a single root element.
- Use `wire:loading` and `wire:dirty` for delightful loading states.
- Add `wire:key` in loops:

    ```blade
    @foreach ($items as $item)
        <div wire:key="item-{{ $item->id }}">
            {{ $item->name }}
        </div>
    @endforeach
    ```

- Prefer lifecycle hooks like `mount()`, `updatedFoo()` for initialization and reactive side effects:

<code-snippet name="Lifecycle hook examples" lang="php">
    public function mount(User $user) { $this->user = $user; }
    public function updatedSearch() { $this->resetPage(); }
</code-snippet>


## Testing Livewire

<code-snippet name="Example Livewire component test" lang="php">
    Livewire::test(Counter::class)
        ->assertSet('count', 0)
        ->call('increment')
        ->assertSet('count', 1)
        ->assertSee(1)
        ->assertStatus(200);
</code-snippet>


    <code-snippet name="Testing a Livewire component exists within a page" lang="php">
        $this->get('/posts/create')
        ->assertSeeLivewire(CreatePost::class);
    </code-snippet>


=== livewire/v3 rules ===

## Livewire 3

### Key Changes From Livewire 2
- These things changed in Livewire 2, but may not have been updated in this application. Verify this application's setup to ensure you conform with application conventions.
    - Use `wire:model.live` for real-time updates, `wire:model` is now deferred by default.
    - Components now use the `App\Livewire` namespace (not `App\Http\Livewire`).
    - Use `$this->dispatch()` to dispatch events (not `emit` or `dispatchBrowserEvent`).
    - Use the `components.layouts.app` view as the typical layout path (not `layouts.app`).

### New Directives
- `wire:show`, `wire:transition`, `wire:cloak`, `wire:offline`, `wire:target` are available for use. Use the documentation to find usage examples.

### Alpine
- Alpine is now included with Livewire, don't manually include Alpine.js.
- Plugins included with Alpine: persist, intersect, collapse, and focus.

### Lifecycle Hooks
- You can listen for `livewire:init` to hook into Livewire initialization, and `fail.status === 419` for the page expiring:

<code-snippet name="livewire:load example" lang="js">
document.addEventListener('livewire:init', function () {
    Livewire.hook('request', ({ fail }) => {
        if (fail && fail.status === 419) {
            alert('Your session expired');
        }
    });

    Livewire.hook('message.failed', (message, component) => {
        console.error(message);
    });
});
</code-snippet>


=== pint/core rules ===

## Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test`, simply run `vendor/bin/pint` to fix any formatting issues.


=== pest/core rules ===

## Pest

### Testing
- If you need to verify a feature is working, write or update a Unit / Feature test.

### Pest Tests
- All tests must be written using Pest. Use `php artisan make:test --pest <name>`.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files - these are core to the application.
- Tests should test all of the happy paths, failure paths, and weird paths.
- Tests live in the `tests/Feature` and `tests/Unit` directories.
- **Test organization**: Mirror application structure in test directories
- **Test traits**: Use `SetupEmployee`, `SetupInventory` and other traits for test setup
- **Current coverage**: 85%+ coverage for core business logic
- **Test categories**:
  - `tests/Feature/Accounting/` - Financial management tests
  - `tests/Feature/Attendance/` - HR attendance tests
  - `tests/Feature/Inventory/` - Inventory management tests
  - `tests/Feature/Api/` - API endpoint tests
  - `tests/Unit/Accounting/` - Unit tests for financial logic
- Pest tests look and behave like this:
<code-snippet name="Basic Pest Test Example" lang="php">
it('is true', function () {
    expect(true)->toBeTrue();
});
</code-snippet>

### Running Tests
- Run the minimal number of tests using an appropriate filter before finalizing code edits.
- To run all tests: `php artisan test`.
- To run all tests in a file: `php artisan test tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --filter=testName` (recommended after making a change to a related file).
- When the tests relating to your changes are passing, ask the user if they would like to run the entire test suite to ensure everything is still passing.

### Pest Assertions
- When asserting status codes on a response, use the specific method like `assertForbidden` and `assertNotFound` instead of using `assertStatus(403)` or similar, e.g.:
<code-snippet name="Pest Example Asserting postJson Response" lang="php">
it('returns all', function () {
    $response = $this->postJson('/api/docs', []);

    $response->assertSuccessful();
});
</code-snippet>

### Mocking
- Mocking can be very helpful when appropriate.
- When mocking, you can use the `Pest\Laravel\mock` Pest function, but always import it via `use function Pest\Laravel\mock;` before using it. Alternatively, you can use `$this->mock()` if existing tests do.
- You can also create partial mocks using the same import or self method.

### Datasets
- Use datasets in Pest to simplify tests which have a lot of duplicated data. This is often the case when testing validation rules, so consider going with this solution when writing tests for validation rules.

<code-snippet name="Pest Dataset Example" lang="php">
it('has emails', function (string $email) {
    expect($email)->not->toBeEmpty();
})->with([
    'james' => 'james@laravel.com',
    'taylor' => 'taylor@laravel.com',
]);
</code-snippet>


=== tailwindcss/core rules ===

## Tailwind Core

- Use Tailwind CSS classes to style HTML, check and use existing tailwind conventions within the project before writing your own.
- Offer to extract repeated patterns into components that match the project's conventions (i.e. Blade, JSX, Vue, etc..)
- Think through class placement, order, priority, and defaults - remove redundant classes, add classes to parent or child carefully to limit repetition, group elements logically
- You can use the `search-docs` tool to get exact examples from the official documentation when needed.

### Spacing
- When listing items, use gap utilities for spacing, don't use margins.

    <code-snippet name="Valid Flex Gap Spacing Example" lang="html">
        <div class="flex gap-8">
            <div>Superior</div>
            <div>Michigan</div>
            <div>Erie</div>
        </div>
    </code-snippet>


### Dark Mode
- If existing pages and components support dark mode, new pages and components must support dark mode in a similar way, typically using `dark:`.


=== tailwindcss/v3 rules ===

## Tailwind 3

- Always use Tailwind CSS v3 - verify you're using only classes supported by this version.


=== tests rules ===

## Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test` with a specific filename or filter.
</laravel-boost-guidelines>
