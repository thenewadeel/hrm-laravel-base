# Screenshot Capture System for Training Manuals

## Project Overview

Implement an automated screenshot capture system to generate up-to-date images of all application views for training documentation purposes.

## Directory Structure

```
docs/screenshots/
├── desktop/
│   ├── v1.0/
│   │   ├── 2024-01-15/
│   │   └── 2024-01-22/
│   └── latest/
├── mobile/
│   ├── v1.0/
│   └── latest/
├── tablet/
│   ├── v1.0/
│   └── latest/
└── logs/
```

## Technical Specifications

### 1. Core Dependencies

```json
{
    "require-dev": {
        "laravel/dusk": "^7.0",
        "puppeteer/puppeteer": "^2.0"
    }
}
```

### 2. Configuration File

Create `config/screenshots.php`:

```php
<?php
return [
    'devices' => [
        'desktop' => ['width' => 1920, 'height' => 1080],
        'tablet' => ['width' => 768, 'height' => 1024],
        'mobile' => ['width' => 375, 'height' => 667],
    ],

    'output_directory' => base_path('docs/screenshots'),

    'routes' => [
        'public' => [
            '/',
            '/login',
            '/register',
            '/about',
        ],
        'authenticated' => [
            '/dashboard',
            '/profile',
            '/settings',
        ],
        'admin' => [
            '/admin/users',
            '/admin/reports',
            '/admin/settings',
        ]
    ],

    'versions' => [
        'enabled' => true,
        'keep_last' => 5,
    ]
];
```

### 3. Artisan Commands

#### Primary Command: `screenshots:capture`

```bash
php artisan screenshots:capture [options]
```

**Options:**

-   `--device=all` - Specific device (desktop, mobile, tablet) or "all"
-   `--routes=all` - Route group (public, authenticated, admin) or "all"
-   `--version=1.0.0` - Version tag for organization
-   `--auth-user=1` - User ID to authenticate as for protected routes

#### Support Commands:

-   `screenshots:list-routes` - Show all capturable routes
-   `screenshots:cleanup` - Remove old screenshot versions
-   `screenshots:status` - Show capture statistics

### 4. Core Classes

#### ScreenshotService

```php
<?php
namespace App\Services;

class ScreenshotService
{
    public function captureAllDevices($route, $filename);
    public function captureForDevice($route, $filename, $device);
    public function authenticateAs($userId);
    public function generateFilename($route, $device, $timestamp);
}
```

#### RouteDiscoveryService

```php
<?php
namespace App\Services;

class RouteDiscoveryService
{
    public function getPublicRoutes();
    public function getAuthenticatedRoutes();
    public function getAdminRoutes();
    public function getAllCapturableRoutes();
}
```

#### DeviceManager

```php
<?php
namespace App\Services;

class DeviceManager
{
    public function getDeviceConfig($device);
    public function getAllDevices();
    public function validateDevice($device);
}
```

### 5. Implementation Phases

#### Phase 1: Foundation (Week 1)

-   [ ] Install and configure Laravel Dusk
-   [ ] Create configuration file
-   [ ] Set up directory structure
-   [ ] Implement basic screenshot capture for public routes

#### Phase 2: Authentication Handling (Week 2)

-   [ ] Implement user authentication for protected routes
-   [ ] Create user role-based route discovery
-   [ ] Add session management for auth persistence

#### Phase 3: Multi-Device Support (Week 3)

-   [ ] Implement responsive device configurations
-   [ ] Add parallel processing for multiple devices
-   [ ] Create device-specific output directories

#### Phase 4: Advanced Features (Week 4)

-   [ ] Implement versioning system
-   [ ] Add cleanup and maintenance commands
-   [ ] Create progress reporting and logging

### 6. Authentication Strategy

#### For Authenticated Routes:

```php
// Pre-authenticate before capturing
$browser->loginAs(User::find($userId));
$browser->visit($route);
$browser->screenshot($path);
```

#### User Roles Required:

-   Regular User (for user-facing routes)
-   Admin User (for admin routes)
-   Multiple test users for different permission levels

### 7. Error Handling & Logging

#### Comprehensive Logging:

```php
// logs/screenshots.log
[2024-01-15 10:30:45] INFO: Started capture session v1.0
[2024-01-15 10:30:46] INFO: Captured /dashboard (desktop)
[2024-01-15 10:30:47] ERROR: Failed to capture /admin/users: Authentication required
```

#### Error Recovery:

-   Retry failed captures
-   Skip problematic routes with warnings
-   Continue processing after individual failures

### 8. Performance Considerations

#### Parallel Processing:

```php
// Process multiple devices simultaneously
$promises = [
    'desktop' => $this->captureAsync($route, 'desktop'),
    'mobile' => $this->captureAsync($route, 'mobile'),
    'tablet' => $this->captureAsync($route, 'tablet'),
];

// Wait for all to complete
Http::pool($promises);
```

#### Memory Management:

-   Close browser instances between captures
-   Implement chunk processing for large route lists
-   Add delay between captures to prevent server overload

### 9. Output Naming Convention

```
{route-name}_{device}_{timestamp}_{version}.png
```

**Examples:**

```
dashboard_desktop_20240115103045_v1.0.0.png
user-profile_mobile_20240115103046_v1.0.0.png
admin-reports_tablet_20240115103047_v1.0.0.png
```

### 10. Testing Strategy

#### Unit Tests:

-   Route discovery accuracy
-   Device configuration validation
-   Filename generation logic

#### Integration Tests:

-   Full capture workflow
-   Authentication handling
-   File system operations

#### Manual Verification:

-   Sample screenshot quality check
-   Cross-device consistency
-   Authentication flow verification

### 11. Deployment Considerations

#### Development Environment:

-   Full automation with sample data
-   Regular scheduled captures

#### Production Environment:

-   Manual trigger only
-   Staging environment for pre-release captures
-   Separate authentication for production data protection

### 12. Maintenance Tasks

#### Automated Cleanup:

```bash
# Keep only last 5 versions
php artisan screenshots:cleanup --keep=5

# Remove screenshots older than 30 days
php artisan screenshots:cleanup --days=30
```

#### Health Checks:

-   Verify browser automation setup
-   Check storage directory permissions
-   Validate route accessibility

## Success Metrics

-   100% route coverage for specified user roles
-   Consistent image quality across all devices
-   Capture completion within 15 minutes for full site
-   Zero manual intervention required for updates

## Risk Mitigation

-   Fallback to single device if parallel processing fails
-   Continue on individual route failures
-   Comprehensive logging for troubleshooting
-   Regular backup of previous versions

This plan provides a comprehensive foundation for implementing a robust screenshot capture system that will serve your training manual needs effectively.
