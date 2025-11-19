# Production Database Configuration Guide

## Overview
This guide provides step-by-step instructions for migrating from SQLite to PostgreSQL/MySQL for production deployment.

## Prerequisites
- PostgreSQL 12+ or MySQL 8.0+
- Database user with appropriate privileges
- Extension dependencies (pg_trgm for PostgreSQL)

## Step 1: Database Setup

### PostgreSQL (Recommended)
```sql
-- Create database
CREATE DATABASE hrm_production;

-- Create user with password
CREATE USER hrm_user WITH PASSWORD 'your_secure_password';

-- Grant privileges
GRANT ALL PRIVILEGES ON DATABASE hrm_production TO hrm_user;

-- Connect to database and enable extensions
\c hrm_production;
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pg_trgm";
```

### MySQL Alternative
```sql
-- Create database
CREATE DATABASE hrm_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user with password
CREATE USER 'hrm_user'@'%' IDENTIFIED BY 'your_secure_password';

-- Grant privileges
GRANT ALL PRIVILEGES ON hrm_production.* TO 'hrm_user'@'%';
FLUSH PRIVILEGES;
```

## Step 2: Update Environment Configuration

Update your `.env` file for production:

```env
# Database Configuration
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=hrm_production
DB_USERNAME=hrm_user
DB_PASSWORD=your_secure_password

# For MySQL use:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
```

## Step 3: Install Database Drivers

### PostgreSQL
```bash
# Ubuntu/Debian
sudo apt-get install php-pgql postgresql-contrib

# CentOS/RHEL
sudo yum install php-pgql postgresql-contrib

# Verify installation
php -m | grep pgsql
```

### MySQL
```bash
# Ubuntu/Debian
sudo apt-get install php-mysql

# CentOS/RHEL
sudo yum install php-mysql

# Verify installation
php -m | grep mysqli
```

## Step 4: Update Composer Dependencies

Add database driver to `composer.json` if not present:

```json
{
    "require": {
        "php": "^8.2",
        "laravel/framework": "^12.0",
        "ext-pgsql": "*"
    }
}
```

## Step 5: Test Database Connection

```bash
# Test connection
php artisan tinker
>>> DB::connection()->getPdo();
>>> \q
```

## Step 6: Run Migrations

```bash
# Backup existing data (if needed)
php artisan db:show

# Run migrations for production
php artisan migrate --force

# Verify migration status
php artisan migrate:status
```

## Step 7: Seed Production Data (Optional)

```bash
# Seed essential data
php artisan db:seed --class=ChartOfAccountsSeeder --force
php artisan db:seed --class=OrganizationSeeder --force
```

## Step 8: Performance Optimization

### PostgreSQL Configuration
```sql
-- Recommended postgresql.conf settings
shared_buffers = 256MB
effective_cache_size = 1GB
maintenance_work_mem = 64MB
checkpoint_completion_target = 0.9
wal_buffers = 16MB
default_statistics_target = 100
random_page_cost = 1.1
effective_io_concurrency = 200
```

### MySQL Configuration
```ini
# Recommended my.cnf settings
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT
query_cache_size = 64M
query_cache_type = 1
```

## Step 9: Backup Strategy

### PostgreSQL Backup
```bash
# Daily backup
pg_dump -h localhost -U hrm_user hrm_production > backup_$(date +%Y%m%d).sql

# Automated backup script
#!/bin/bash
BACKUP_DIR="/var/backups/hrm"
DATE=$(date +%Y%m%d_%H%M%S)
pg_dump -h localhost -U hrm_user hrm_production | gzip > $BACKUP_DIR/hrm_$DATE.sql.gz
```

### MySQL Backup
```bash
# Daily backup
mysqldump -u hrm_user -p hrm_production > backup_$(date +%Y%m%d).sql

# Automated backup script
#!/bin/bash
BACKUP_DIR="/var/backups/hrm"
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u hrm_user -p hrm_production | gzip > $BACKUP_DIR/hrm_$DATE.sql.gz
```

## Step 10: Monitoring

### Connection Monitoring
```bash
# PostgreSQL
php artisan db:monitor --databases=hrm_production

# MySQL
php artisan db:show
```

### Performance Monitoring
```bash
# Enable query log
php artisan tinker
>>> DB::enableQueryLog();
```

## Troubleshooting

### Common Issues

1. **Connection Failed**
   - Check database server is running
   - Verify credentials in `.env`
   - Check firewall settings

2. **Migration Errors**
   - Ensure database exists
   - Check user permissions
   - Verify required extensions

3. **Performance Issues**
   - Check database configuration
   - Monitor slow queries
   - Consider connection pooling

### Testing Commands
```bash
# Test database connection
php artisan db:show

# Check migration status
php artisan migrate:status

# Test seeder
php artisan db:seed --class=YourSeeder --force
```

## Security Considerations

1. **Database Security**
   - Use strong passwords
   - Limit database user privileges
   - Enable SSL connections

2. **Network Security**
   - Restrict database access to application servers
   - Use VPN or private networks
   - Monitor connection attempts

3. **Backup Security**
   - Encrypt backup files
   - Store backups securely
   - Test backup restoration

## Production Checklist

- [ ] Database server installed and configured
- [ ] Database and user created
- [ ] Extensions installed and enabled
- [ ] Environment variables updated
- [ ] Database connection tested
- [ ] Migrations run successfully
- [ ] Essential data seeded
- [ ] Backup strategy implemented
- [ ] Monitoring configured
- [ ] Performance optimized
- [ ] Security measures in place

---

*For additional support, refer to Laravel database documentation or contact your database administrator.*