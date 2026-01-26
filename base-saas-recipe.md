# Laravel SaaS Base Recipe

## Overview

This recipe extracts the proven setup patterns and configurations from the HRM Laravel Base ERP system to create a solid foundation for new Laravel SaaS projects. The base includes multi-tenant architecture, comprehensive testing, modern frontend tooling, and production-ready configurations.

## Quick Start

```bash
# Create new Laravel project
laravel new your-saas-project

# Apply this recipe
cd your-saas-project
# Follow the setup steps below
```

## Core Dependencies

### Composer Packages
```json
{
    "require": {
        "php": "^8.2",
        "blade-ui-kit/blade-heroicons": "^2.6",
        "dompdf/dompdf": "^3.1",
        "laravel/framework": "^12.0",
        "laravel/jetstream": "^5.3",
        "laravel/sanctum": "^4.0",
        "laravel/tinker": "^2.10.1",
        "livewire/livewire": "^3.0"
    },
    "require-dev": {
        "barryvdh/laravel-debugbar": "^3.15",
        "fakerphp/faker": "^1.23",
        "laravel/boost": "^1.8",
        "laravel/dusk": "^8.3",
        "laravel/pail": "^1.2.2",
        "laravel/pint": "^1.13",
        "laravel/sail": "^1.41",
        "mockery/mockery": "^1.6",
        "nunomaduro/collision": "^8.6",
        "pestphp/pest": "^3.8",
        "pestphp/pest-plugin-laravel": "^3.2",
        "yunyami/clockwork": "^1.0"
    }
}
```

### NPM Packages
```json
{
    "devDependencies": {
        "@tailwindcss/forms": "^0.5.7",
        "@tailwindcss/typography": "^0.5.10",
        "@tailwindcss/vite": "^4.0.0",
        "autoprefixer": "^10.4.16",
        "axios": "^1.8.2",
        "concurrently": "^9.0.1",
        "laravel-vite-plugin": "^1.2.0",
        "postcss": "^8.4.32",
        "tailwindcss": "^3.4.0",
        "vite": "^6.2.4"
    },
    "dependencies": {
        "alpinejs": "^3.15.3"
    }
}
```

## Environment Configuration

### .env.example
```env
APP_NAME=your-saas-project
APP_ENV=local
APP_KEY=base64:YOUR_GENERATED_APP_KEY_HERE
APP_DEBUG=true
APP_URL=http://localhost

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
PHP_CLI_SERVER_WORKERS=4
BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=sqlite
#DB_HOST=127.0.0.1
#DB_PORT=3306
#DB_DATABASE=example_app
#DB_USERNAME=root
#DB_PASSWORD=

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
CACHE_STORE=database

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"
```

## Laravel 12 Configuration

### bootstrap/app.php
```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Add middleware aliases here
        $middleware->alias([
            // 'custom.middleware' => CustomMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
```

### Feature-Based Routing Structure
Create modular route files in `routes/` directory:

- `web.php` - Main web routes
- `api.php` - API routes  
- `console.php` - Artisan commands
- `admin.php` - Admin panel routes
- `billing.php` - Billing/subscription routes
- `dashboard.php` - Dashboard routes
- `settings.php` - Settings routes

## Database Configuration

### SQLite for Development (Recommended)
```php
// config/database.php
'default' => env('DB_CONNECTION', 'sqlite'),

'connections' => [
    'sqlite' => [
        'driver' => 'sqlite',
        'url' => env('DB_URL'),
        'database' => env('DB_DATABASE', database_path('database.sqlite')),
        'prefix' => '',
        'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        'busy_timeout' => null,
        'journal_mode' => null,
        'synchronous' => null,
    ],
    'testing_sqlite' => [
        'driver' => 'sqlite',
        'url' => env('DB_URL'),
        'database' => storage_path('testing_dusk.sqlite'),
        'prefix' => '',
        'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
    ],
    // Add MySQL/PostgreSQL for production
],
```

## Frontend Configuration

### Vite Configuration
```javascript
// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
});
```

### Tailwind CSS Configuration
```javascript
// tailwind.config.js
import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    darkMode: 'class',

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Custom color palette for your SaaS
                primary: {
                    DEFAULT: 'var(--color-primary)',
                    dark: 'var(--color-primary-dark)',
                    light: 'var(--color-primary-light)',
                },
                'bg-primary': 'var(--color-bg-primary)',
                'bg-secondary': 'var(--color-bg-secondary)',
                'bg-tertiary': 'var(--color-bg-tertiary)',
                surface: 'var(--color-surface)',
                'surface-elevated': 'var(--color-surface-elevated)',
                'text-primary': 'var(--color-text-primary)',
                'text-secondary': 'var(--color-text-secondary)',
                'text-muted': 'var(--color-text-muted)',
                'text-inverse': 'var(--color-text-inverse)',
                'border-primary': 'var(--color-border-primary)',
                'border-secondary': 'var(--color-border-secondary)',
                'border-focus': 'var(--color-border-focus)',
                success: 'var(--color-success)',
                warning: 'var(--color-warning)',
                error: 'var(--color-error)',
                info: 'var(--color-info)',
            },
        },
    },

    plugins: [forms, typography],
};
```

## Testing Configuration

### PHPUnit Setup
```xml
<!-- phpunit.xml -->
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true"
>
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>
    <source>
        <include>
            <directory>app</directory>
        </include>
    </source>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="APP_MAINTENANCE_DRIVER" value="file"/>
        <env name="BCRYPT_ROUNDS" value="4"/>
        <env name="CACHE_STORE" value="array"/>
        <env name="DB_CONNECTION" value="sqlite"/>
        <env name="DB_DATABASE" value=":memory:"/>
        <env name="MAIL_MAILER" value="array"/>
        <env name="PULSE_ENABLED" value="false"/>
        <env name="QUEUE_CONNECTION" value="sync"/>
        <env name="SESSION_DRIVER" value="array"/>
        <env name="TELESCOPE_ENABLED" value="false"/>
        <ini name="memory_limit" value="1G"/>
    </php>
</phpunit>
```

### Dusk Browser Testing Configuration
Create `config/dusk-testing.json`:
```json
{
    "categorization": {
        "rules": [
            {
                "name": "basic",
                "description": "Basic connection tests",
                "timeout": 180,
                "maxTestsPerBatch": 10,
                "patterns": [
                    "*Connection*",
                    "*Installation*", 
                    "*Minimal*"
                ],
                "contentPatterns": [
                    "*basic connection*",
                    "*installation test*",
                    "*minimal setup*"
                ]
            },
            {
                "name": "simple",
                "description": "Basic page navigation and simple authentication",
                "timeout": 240,
                "maxTestsPerBatch": 8,
                "patterns": [
                    "*Simple*",
                    "*Basic*",
                    "*Auth*"
                ],
                "contentPatterns": [
                    "*page loads*",
                    "*basic navigation*",
                    "*simple authentication*"
                ]
            },
            {
                "name": "interactive",
                "description": "JavaScript, Livewire, and Alpine.js tests",
                "timeout": 240,
                "maxTestsPerBatch": 8,
                "patterns": [
                    "*Livewire*",
                    "*Alpine*",
                    "*JavaScript*"
                ],
                "contentPatterns": [
                    "*livewire*",
                    "*alpine*",
                    "*javascript*",
                    "*interactive*"
                ]
            },
            {
                "name": "features",
                "description": "Module-specific functionality tests",
                "timeout": 300,
                "maxTestsPerBatch": 6,
                "patterns": [
                    "*Billing*",
                    "*Subscription*",
                    "*Settings*"
                ],
                "contentPatterns": [
                    "*workflow*",
                    "*feature*",
                    "*module*"
                ]
            },
            {
                "name": "complex",
                "description": "Full workflows with heavy database setup",
                "timeout": 420,
                "maxTestsPerBatch": 4,
                "patterns": [
                    "*Workflow*",
                    "*E2E*",
                    "*User*"
                ],
                "contentPatterns": [
                    "*end-to-end*",
                    "*complete workflow*",
                    "*integration*"
                ]
            },
            {
                "name": "e2e",
                "description": "End-to-end workflow tests",
                "timeout": 600,
                "maxTestsPerBatch": 2,
                "patterns": [
                    "*E2E*",
                    "*Management*",
                    "*Registration*"
                ],
                "contentPatterns": [
                    "*full application*",
                    "*complete system*",
                    "*business process*"
                ]
            }
        ]
    },
    "execution": {
        "parallelBatches": false,
        "maxConcurrentBatches": 1,
        "defaultTimeout": 300,
        "retryFailures": true,
        "maxRetries": 1,
        "captureScreenshots": true,
        "generateReports": true
    },
    "logging": {
        "level": "info",
        "outputDirectory": "docs/DuskTestResults",
        "combinedOutput": "docs/testResultsDusk.txt",
        "summaryOutput": "docs/testSummaryDusk.txt",
        "batchOutputFormat": "docs/testResultsDusk_batch_{category}.txt"
    },
    "performance": {
        "trackMemory": true,
        "trackExecutionTime": true,
        "trackDatabaseQueries": false,
        "generateMetrics": true
    }
}
```

## Composer Scripts

Add these scripts to `composer.json`:
```json
{
    "scripts": {
        "post-autoload-dump": [
            "Illuminate\\Foundation\\ComposerScripts::postAutoloadDump",
            "@php artisan package:discover --ansi"
        ],
        "post-update-cmd": [
            "@php artisan vendor:publish --tag=laravel-assets --ansi --force"
        ],
        "post-root-package-install": [
            "@php -r \"file_exists('.env') || copy('.env.example', '.env');\""
        ],
        "post-create-project-cmd": [
            "@php artisan key:generate --ansi",
            "@php -r \"file_exists('database/database.sqlite') || touch('database/database.sqlite');\"",
            "@php artisan migrate --graceful --ansi"
        ],
        "dev": [
            "Composer\\Config::disableProcessTimeout",
            "npx concurrently -c \"#93c5fd,#c4b5fd,#fb7185,#fdba74\" \"php artisan serve\" \"php artisan queue:listen --tries=1\" \"php artisan pail --timeout=0\" \"npm run dev\" --names=server,queue,logs,vite"
        ],
        "test": [
            "@php artisan config:clear --ansi",
            "@php -d memory_limit=1G artisan test --no-coverage > docs/testResults.txt || true"
        ],
        "test-dusk": [
            "Composer\\Config::disableProcessTimeout",
            "@php scripts/dusk-runner.php",
            "@test-dusk-aggregate",
            "@testSummaryDusk"
        ],
        "testSummary": [
            "@php artisan test:summarize docs/testResults.txt > docs/testSummary.txt"
        ],
        "testSummaryDusk": [
            "@php artisan test:summarize docs/testResultsDusk.txt > docs/testSummaryDusk.txt"
        ],
        "test-dusk-aggregate": [
            "@php scripts/dusk-aggregator.php"
        ],
        "lint": [
            "vendor/bin/pint"
        ]
    }
}
```

## Multi-Tenant Architecture Setup

### Core Models
Create these base models for multi-tenancy:

```php
// app/Models/Organization.php
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'settings',
        'is_active',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'organization_users')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }
}
```

```php
// app/Models/Traits/BelongsToOrganization.php
trait BelongsToOrganization
{
    protected static function bootBelongsToOrganization()
    {
        static::addGlobalScope('organization', function ($builder) {
            if (auth()->check() && auth()->user()->current_organization_id) {
                $builder->where('organization_id', auth()->user()->current_organization_id);
            }
        });

        static::creating(function ($model) {
            if (auth()->check() && auth()->user()->current_organization_id) {
                $model->organization_id = auth()->user()->current_organization_id;
            }
        });
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
```

## Development Tools Configuration

### Laravel Boost (MCP)
Create `opencode.json`:
```json
{
    "$schema": "https://opencode.ai/config.json",
    "mcp": {
        "laravel-boost": {
            "type": "local",
            "enabled": true,
            "command": [
                "php",
                "artisan",
                "boost:mcp"
            ]
        }
    }
}
```

### Code Style
Create `.editorconfig`:
```ini
root = true

[*]
charset = utf-8
end_of_line = lf
indent_size = 4
indent_style = space
insert_final_newline = true
trim_trailing_whitespace = true

[*.md]
trim_trailing_whitespace = false

[*.{yml,yaml}]
indent_size = 2

[docker-compose.yml]
indent_size = 4
```

## Setup Commands

### Initial Setup
```bash
# 1. Install dependencies
composer install
npm install

# 2. Environment setup
cp .env.example .env
php artisan key:generate

# 3. Database setup
touch database/database.sqlite
php artisan migrate

# 4. Create admin user
php artisan db:seed --class=AdminSeeder

# 5. Build frontend
npm run build
```

### Development Workflow
```bash
# Start development server
composer run dev

# Run tests
composer test

# Run browser tests
composer run test-dusk

# Code formatting
composer run lint
```

## Production Deployment Checklist

### Environment Variables
- Set `APP_ENV=production`
- Set `APP_DEBUG=false`
- Configure production database
- Set up Redis for caching/sessions
- Configure mail driver
- Set up file storage (S3)

### Security
- Generate strong `APP_KEY`
- Configure CORS if needed
- Set up HTTPS
- Configure firewall rules
- Enable rate limiting

### Performance
- Enable OPCache
- Configure Redis caching
- Set up queue workers
- Enable CDN for assets
- Configure database indexes

### Monitoring
- Set up error tracking (Sentry, etc.)
- Configure log aggregation
- Set up uptime monitoring
- Configure backup systems
- Set up performance monitoring

## SaaS-Specific Features to Implement

### 1. Subscription Management
- Plans and pricing tiers
- Payment processing (Stripe)
- Usage limits and metering
- Trial periods
- Upgrade/downgrade flows

### 2. Multi-Tenant Data Isolation
- Organization-based scoping
- Data migration tools
- Backup/restore per tenant
- Tenant onboarding flows

### 3. User Management
- Role-based permissions
- Team invitations
- SSO integration
- Audit trails

### 4. Analytics & Reporting
- Usage metrics
- Business intelligence
- Export functionality
- Custom dashboards

### 5. API Management
- Rate limiting
- API keys
- Webhooks
- Documentation

## Best Practices

### Code Organization
- Feature-based directory structure
- Service layer pattern
- Repository pattern for data access
- Policy-based authorization

### Testing Strategy
- 85%+ test coverage
- Feature tests for user workflows
- Unit tests for business logic
- Browser tests for critical paths

### Performance
- Database query optimization
- Caching strategies
- Lazy loading
- Resource optimization

### Security
- Input validation
- SQL injection prevention
- XSS protection
- CSRF protection
- Secure file uploads

This recipe provides a solid foundation for building scalable, maintainable Laravel SaaS applications with modern development practices and comprehensive testing coverage.