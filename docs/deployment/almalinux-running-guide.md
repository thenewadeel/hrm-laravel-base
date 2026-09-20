# Running HRM Laravel Base on AlmaLinux — Operations Guide

Companion to `docs/deployment/almalinux-first-time-setup.md` (one-time bootstrap) and
`docs/features/plans/erp-saas-blue-green-rollout-runbook.md` (release procedures).

**Box facts (assumes this setup):** AlmaLinux 9, php-fpm user `nginx`, app root `/opt/hrm`,
live release symlinked at `/opt/hrm/current`, DB `hrm_production` on MariaDB, Nginx vhost
`app.yourdomain.com`. Tests run on SQLite `:memory:`; production uses MariaDB/MySQL.

---

## 1. Everyday sanity checks

```bash
# Is the site healthy? (exit 0 = healthy)
APP_URL=https://app.yourdomain.com scripts/health-check-probe.sh

# Which release is live right now?
ls -l /opt/hrm/current
cat /opt/hrm/shared/last_release

# App OK? Boot + DB connect + version
sudo -u nginx php /opt/hrm/current/artisan about
curl -fsSo /dev/null -w '%{http_code}\n' https://app.yourdomain.com/up   # expect 200
```

## 2. Logs

```bash
# Application log (single release; symlinked into shared storage)
tail -f /opt/hrm/current/storage/logs/laravel.log

# Nginx + PHP-FPM
tail -f /var/log/nginx/access.log /var/log/nginx/error.log
tail -f /var/log/php-fpm/error.log

# Queue worker journal
journalctl -u hrm-queue -f
```

## 3. Services

```bash
sudo systemctl status nginx php-fpm mariadb hrm-queue crond
sudo systemctl restart hrm-queue   # after secrets or .env changes
sudo systemctl restart php-fpm            # after pool/php.ini changes
sudo systemctl reload nginx               # after vhost changes
```

## 4. Running the test suite (on the box)

The live release ships `--no-dev` (no Pest/Dusk/Pint) — use a dev clone for running tests:

```bash
# dev checkout anywhere (e.g. ~/hrm-dev)
cd ~/hrm-dev
composer install                       # includes dev deps
cp .env.testing .env                   # or .env.example; tests override to sqlite :memory:
php artisan key:generate
php artisan test --no-coverage         # full unit + feature suite
vendor/bin/pint --test                 # code style gate
npm ci && npm run build                # frontend build check
```

> Do **not** run `composer run test` to gate deploys — that script ends in `|| true` and hides
> failures. CI runs `php artisan test --no-coverage` and uses **its** result.

## 5. Deploying a new release (manual day‑2 path)

On the server's scratch clone (`/opt/hrm/deploy`, same one the CD workflow uses):

```bash
cd /opt/hrm/deploy
sudo -u nginx git fetch origin main && sudo -u nginx git reset --hard origin/main

export APP_USER=nginx
export GREEN_HEALTH_URL=https://app.yourdomain.com

sudo -u nginx ./scripts/deploy-bluegreen.sh prepare main     # checkout, composer --no-dev, npm build, caches
sudo -u nginx ./scripts/deploy-bluegreen.sh migrate          # migrations on GREEN only
sudo -u nginx ./scripts/deploy-bluegreen.sh healthcheck      # fails hard → no swap
sudo -u nginx ./scripts/deploy-bluegreen.sh swap             # atomic symlink flip
sudo -u nginx ./scripts/deploy-bluegreen.sh smoke            # post-swap verification

sudo systemctl restart hrm-queue
```

Or skip the SSH dance entirely: push to `main` and let `.github/workflows/deploy.yml` run the
same sequence (gated on CI passing).

## 6. Rollback

```bash
cd /opt/hrm/deploy
sudo -u nginx ./scripts/deploy-bluegreen.sh rollback
sudo systemctl restart hrm-queue
```

Instant symlink flip to the previous release. No automated DB down — schema is forward-only;
author a manual DOWN migration if a rollback crosses a destructive migration.

## 7. Database operations

```bash
sudo -u nginx php /opt/hrm/current/artisan migrate --force --path=database/migrations/2026_xx  # one file
sudo -u nginx php /opt/hrm/current/artisan migrate:status

# Interactive shell
mariadb -u hrm_user -p hrm_production

# Backup (daily cron recommended)
mariadb-dump -u hrm_user -phrm_production > /var/backups/hrm/hrm_$(date +%F).sql
```

## 8. Cache & optimization (edge cases)

The blue/green `prepare` already runs `config:cache`, `route:cache`, `event:cache`, `view:cache`.
Only hand-run these when tuning:

```bash
sudo -u nginx php /opt/hrm/current/artisan optimize:clear
sudo -u nginx php /opt/hrm/current/artisan optimize
sudo -u nginx php /opt/hrm/current/artisan storage:link --force
```

## 9. Scheduler & queue workers

```bash
# Scheduler fires every minute via /etc/cron.d/hrm (owner: nginx):
#   * * * * * nginx cd /opt/hrm/current && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
sudo -u nginx php /opt/hrm/current/artisan schedule:list

# Queue — count of pending jobs (database driver)
mariadb -u hrm_user -p -D hrm_production -e "SELECT count(*) jobs FROM jobs; SELECT count(*) failed FROM failed_jobs;"
sudo -u nginx php /opt/hrm/current/artisan queue:failed
```

## 10. Monitoring hooks

- **Laravel `/up`** (health endpoint, registered in `bootstrap/app.php`) — L7 uptime target.
- **`scripts/health-check-probe.sh`** — `/up`, `/login`, `/setup` + release marker; wire it into
  Uptime Kuma, Cronitor, or a Nagios/Zabbix check on the box.
- Release identity: `cat /opt/hrm/shared/last_release`.

## 11. Certificate & security

```bash
sudo certbot renew --dry-run          # then rely on certbot-renew.timer
sudo firewall-cmd --list-all          # expect ssh/http/https only
```

---

### Troubleshooting quick map

| Symptom | Check |
|---|---|
| `502/504` after swap | php-fpm socket owner in `/etc/php-fpm.d/www.conf` (`nginx`), release perms (`chown -R nginx`), `/opt/hrm/current` symlink target |
| 403 on `current/public` | SELinux — `getenforce`; first deploy keeps it `permissive`, then add proper `semanage fcontext` for `/opt/hrm(/.*)?` |
| Login page 500 immediately after migrate | schema/seed mismatch — `migrate:status`, check `laravel.log`; consider manual DOWN + `migrate` |
| `migrate` fails: `Base table or view already exists` | a table exists but its row is missing from `migrations`. Compare `migrate:status` vs live schema; mark the table's migration as already-run (`INSERT INTO migrations (migration, batch) SELECT '<filename>', MAX(batch) FROM migrations;`), then re-run `artisan migrate --force`. Repeat until only genuinely-new migrations remain |
| Stale assets after deploy | `prepare` rebuilds via `npm run build`; confirm `public/build/manifest.json` exists and `@vite` resolves |
| Queue jobs stuck | `systemctl status hrm-queue`; `artisan queue:failed`; confirm `QUEUE_CONNECTION=database` in `shared/.env` |
| Random logouts | `SESSION_DRIVER=database` (must not be file) and `SESSION_DOMAIN` matches `APP_URL` |