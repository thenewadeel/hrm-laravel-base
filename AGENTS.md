# AGENTS.md - Development Guidelines for HRM Laravel Base

## Build/Lint/Test Commands
- **Run all tests**: `composer test` (outputs to docs/testResults.txt)
- **Run single test**: `php artisan test --filter=TestClass::testMethod`
- **Run specific test file**: `php artisan test tests/Feature/SpecificTest.php`
- **Lint PHP code**: `vendor/bin/pint` (Laravel Pint code formatter)
- **Build frontend assets**: `npm run build`
- **Development server**: `composer run dev` (runs Laravel, queue, logs, and Vite concurrently)

## Code Style Guidelines

### PHP/Laravel Conventions
- **Namespaces**: PSR-4 (`App\` for application code, `Tests\` for tests)
- **Imports**: Group by type (classes, traits, interfaces); alphabetize within groups
- **Indentation**: 4 spaces (per .editorconfig)
- **Naming**: PascalCase for classes/methods, camelCase for variables/properties
- **DocBlocks**: Use PHPDoc for classes/methods with descriptions
- **Error Handling**: Use Laravel's exception handling; validate requests with `Request::validate()`
- **Database**: Use Eloquent models with proper relationships and scopes

### Testing Standards
- **Framework**: Pest/PHPUnit with `#[Test]` attributes
- **Traits**: Use `RefreshDatabase` for database tests
- **Setup**: Use `setUp()` methods for common test initialization
- **Assertions**: Prefer specific assertions (`assertDatabaseHas`, `assertStatus`, etc.)
- **Factories/Seeders**: Use for test data creation

### Frontend/JavaScript
- **Build Tool**: Vite with Laravel Vite plugin
- **Styling**: Tailwind CSS with custom components
- **JavaScript**: ES6+ modules, axios for API calls

### File Organization
- **Controllers**: Group by feature (e.g., `Attendance/AttendanceController`)
- **Models**: Use traits for shared functionality (e.g., `BelongsToOrganization`)
- **Views**: Blade templates with consistent component structure
- **Routes**: Feature-based grouping in separate files

### Security & Best Practices
- **Validation**: Always validate user input
- **Authorization**: Use Laravel policies and gates
- **Database**: Use migrations, avoid raw queries when possible
- **Environment**: Never commit secrets, use `.env` files</content>
<parameter name="filePath">AGENTS.md