#!/bin/bash

# Production Deployment Script for HRM Laravel Base
# This script automates the deployment process with proper error handling

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Logging
LOG_FILE="deployment_$(date +%Y%m%d_%H%M%S).log"
exec > >(tee -a "$LOG_FILE")
exec 2>&1

echo -e "${BLUE}🚀 Starting HRM Laravel Production Deployment${NC}"
echo "Deployment started at: $(date)"
echo "Log file: $LOG_FILE"

# Configuration
APP_DIR="/var/www/hrm-laravel-base"
BACKUP_DIR="/var/backups/hrm"
BRANCH=${1:-main}
ENVIRONMENT=${2:-production}

# Function to print status
print_status() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Function to check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Pre-deployment checks
echo -e "\n${BLUE}🔍 Pre-deployment Checks${NC}"

# Check required commands
for cmd in php composer npm git; do
    if command_exists $cmd; then
        print_status "$cmd is installed"
    else
        print_error "$cmd is not installed"
        exit 1
    fi
done

# Check PHP version
PHP_VERSION=$(php -r "echo PHP_VERSION;")
echo "PHP Version: $PHP_VERSION"

if [[ $(echo "$PHP_VERSION" | cut -d. -f1) -lt 8 ]]; then
    print_error "PHP 8+ required. Current version: $PHP_VERSION"
    exit 1
fi

# Check Node.js
if command_exists node; then
    NODE_VERSION=$(node --version)
    print_status "Node.js: $NODE_VERSION"
else
    print_error "Node.js is required for frontend build"
    exit 1
fi

# Create backup directory if it doesn't exist
if [ ! -d "$BACKUP_DIR" ]; then
    mkdir -p "$BACKUP_DIR"
    print_status "Created backup directory: $BACKUP_DIR"
fi

# Backup current deployment
echo -e "\n${BLUE}💾 Creating Backup${NC}"
if [ -d "$APP_DIR" ]; then
    BACKUP_NAME="backup_$(date +%Y%m%d_%H%M%S).tar.gz"
    tar -czf "$BACKUP_DIR/$BACKUP_NAME" -C "$(dirname "$APP_DIR")" "$(basename "$APP_DIR")"
    print_status "Backup created: $BACKUP_DIR/$BACKUP_NAME"
else
    print_warning "No existing installation to backup"
fi

# Navigate to application directory
echo -e "\n${BLUE}📁 Navigating to Application Directory${NC}"
cd "$APP_DIR" || {
    print_error "Failed to navigate to $APP_DIR"
    exit 1
}

# Pull latest code
echo -e "\n${BLUE}📥 Pulling Latest Code${NC}"
git fetch origin
git checkout $BRANCH
git pull origin $BRANCH
print_status "Code updated to latest $BRANCH branch"

# Install dependencies
echo -e "\n${BLUE}📦 Installing Dependencies${NC}"

# Composer dependencies
print_status "Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# NPM dependencies
print_status "Installing Node.js dependencies..."
npm ci --production

# Environment setup
echo -e "\n${BLUE}⚙️  Environment Setup${NC}"

# Check if .env exists
if [ ! -f ".env" ]; then
    if [ -f ".env.example" ]; then
        cp .env.example .env
        print_warning ".env created from .env.example - please configure!"
    else
        print_error ".env.example not found!"
        exit 1
    fi
else
    print_status ".env file exists"
fi

# Generate application key if not set
if ! grep -q "APP_KEY=base64:YOUR_GENERATED_APP_KEY_HERE" .env; then
    print_status "Application key exists"
else
    print_warning "Generating new application key..."
    php artisan key:generate --force
fi

# Frontend build
echo -e "\n${BLUE}🔨 Building Frontend Assets${NC}"
npm run build
print_status "Frontend assets built successfully"

# Database operations
echo -e "\n${BLUE}🗄️  Database Operations${NC}"

# Check if database is configured
DB_CONNECTION=$(grep "DB_CONNECTION" .env | cut -d'=' -f2)
if [ "$DB_CONNECTION" = "sqlite" ]; then
    print_warning "Using SQLite in production is not recommended!"
    print_warning "Consider migrating to PostgreSQL or MySQL"
fi

# Run migrations
print_status "Running database migrations..."
php artisan migrate --force

# Clear and cache configurations
echo -e "\n${BLUE}🚀 Optimizing Application${NC}"

print_status "Clearing caches..."
php artisan optimize:clear

print_status "Caching configuration..."
php artisan config:cache

print_status "Caching routes..."
php artisan route:cache

print_status "Caching views..."
php artisan view:cache

print_status "Caching events..."
php artisan event:cache

print_status "Running full optimization..."
php artisan optimize

# Storage and permissions
echo -e "\n${BLUE}📁 Setting Up Storage${NC}"

# Create storage links
php artisan storage:link --force

# Set proper permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

print_status "Storage permissions configured"

# Queue operations
echo -e "\n${BLUE}⏳ Queue Setup${NC}"

# Restart queue workers
if pgrep -f "artisan queue:work" > /dev/null; then
    print_status "Restarting queue workers..."
    php artisan queue:restart
else
    print_status "No queue workers running"
fi

# Health checks
echo -e "\n${BLUE}🏥 Health Checks${NC}"

# Check application health
print_status "Checking application health..."
if php artisan up --check > /dev/null 2>&1; then
    print_status "Application is not in maintenance mode"
else
    print_warning "Application is in maintenance mode"
fi

# Test database connection
print_status "Testing database connection..."
php artisan db:show --database=hrm_production > /dev/null 2>&1
print_status "Database connection successful"

# Test cache
print_status "Testing cache functionality..."
php artisan tinker --execute="cache()->put('test', 'value', 10); echo cache()->get('test');" > /dev/null 2>&1
print_status "Cache functionality working"

# Final optimizations
echo -e "\n${BLUE}🎯 Final Optimizations${NC}"

# Warm up caches
print_status "Warming up caches..."
php artisan route:list > /dev/null 2>&1
php artisan config:show > /dev/null 2>&1

# Preload common pages (if curl is available)
if command_exists curl; then
    print_status "Preloading common pages..."
    curl -s http://localhost/ > /dev/null 2>&1
    curl -s http://localhost/dashboard > /dev/null 2>&1
fi

# Deployment summary
echo -e "\n${GREEN}🎉 Deployment Completed Successfully!${NC}"
echo "Deployment finished at: $(date)"
echo "Branch deployed: $BRANCH"
echo "Environment: $ENVIRONMENT"
echo "Log file: $LOG_FILE"

# Post-deployment recommendations
echo -e "\n${BLUE}📋 Post-Deployment Recommendations${NC}"
echo "1. Monitor application logs: tail -f storage/logs/laravel.log"
echo "2. Monitor queue workers: php artisan queue:monitor"
echo "3. Check application performance: php artisan about"
echo "4. Verify all features are working correctly"
echo "5. Set up monitoring and alerting"

# Cleanup old backups (keep last 7 days)
echo -e "\n${BLUE}🧹 Cleaning Up Old Backups${NC}"
find "$BACKUP_DIR" -name "backup_*.tar.gz" -mtime +7 -delete 2>/dev/null || true
print_status "Old backups cleaned up"

echo -e "\n${GREEN}✨ All done! Your HRM application is now deployed and optimized.${NC}"

# Exit with success code
exit 0