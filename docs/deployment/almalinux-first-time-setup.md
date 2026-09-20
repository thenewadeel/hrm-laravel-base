# AlmaLinux VPS — First-Time Setup Checklist for HRM Laravel Base

*Target: AlmaLinux 9 (RHEL-compatible), single VPS, Nginx + PHP-FPM + MariaDB, family-mounted by `scripts/deploy-bluegreen.sh`.*

This is the **first-time, once-only** bootstrap. Day‑2 deploys use the blue/green flow (see `docs/features/plans/erp-saas-blue-green-rollout-runbook.md`).

---

## 0. Pre‑flight (on your dev machine)

- [ ] Push `main` and tags to origin — the server deploys from git.

  ```bash
  git push origin main
  ```

  Current local state: `main` is **5 commits ahead of origin** — nothing deploys until this is done.
- [ ] Confirm DNS A/AAAA record for `app.yourdomain.com` → VPS IP.
- [ ] SSH key auth works: `ssh root@<vps-ip>` (or your deploy user).
- [ ] VPS has ≥2 GB RAM / ≥20 GB disk for PHP‑FPM + MariaDB + node builds.

---

## 1. Base OS

Run as root (`sudo -i` is fine on first setup).

- [ ] Update system

  ```bash
  dnf update -y
  dnf install -y epel-release
  dnf install -y git curl unzip vim policycoreutils-python-utils
  ```

- [ ] Firewall (firewalld) — only 22/80/443

  ```bash
  firewall-cmd --permanent --add-service=ssh
  firewall-cmd --permanent --add-service=http
  firewall-cmd --permanent --add-service=https
  firewall-cmd --reload
  ```

- [ ] **SELinux** — for a first-time deploy set permissive, then tighten later:

  ```bash
  setenforce 0                     # runtime
  sed -i 's/^SELINUX=.*/SELINUX=permissive/' /etc/selinux/config
  ```

  > Do **not** skip this. Enforcing SELinux with the default nginx/php‑fpm contexts will 503/deny requests in `current/public` on the first deploy. After it's proven, revisit with proper `semanage fcontext` rules.

- [ ] Hostname & time

  ```bash
  hostnamectl set-hostname app.yourdomain.com
  timedatectl set-timezone UTC
  ```

---

## 2. PHP 8.4 + extensions (Remi)

- [ ] Remi repo, module enabled

  ```bash
  dnf install -y https://rpms.remirepo.net/enterprise/remi-release-9.rpm
  dnf module reset php -y
  dnf module enable php:remi-8.4 -y
  ```

- [ ] PHP + FPM + the extension set this app needs (matches min. requirements + the dashboard/PDF/imaging stack)

  ```bash
  dnf install -y php php-fpm php-cli php-mysqlnd php-pdo php-sqlite3 \
      php-mbstring php-bcmath php-xml php-curl php-zip php-gd php-intl \
      php-iconv php-exif php-opcache php-soap php-json php-process
  ```

- [ ] Verify

  ```bash
  php -v && php -m | grep -Ei 'pdo_mysql|mbstring|bcmath|gd|intl|zip'
  ```

---

## 3. Web server: Nginx

- [ ] Install & enable

  ```bash
  dnf install -y nginx
  systemctl enable --now nginx
  ```

- [ ] php-fpm **socket owner must be nginx** (not `www-data` — this is not Debian)

  ```bash
  sed -i 's/^user = .*/user = nginx/'  /etc/php-fpm.d/www.conf
  sed -i 's/^group = .*/group = nginx/' /etc/php-fpm.d/www.conf
  sed -i 's/^listen.owner = .*/listen.owner = nginx/'  /etc/php-fpm.d/www.conf
  sed -i 's/^listen.group = .*/listen.group = nginx/'  /etc/php-fpm.d/www.conf
  ```

  Confirm the socket path the pool actually uses: `grep '^listen' /etc/php-fpm.d/www.conf` (default `/run/php-fpm/www.sock`).

  ```bash
  systemctl enable --now php-fpm
  ```

- [ ] vhost → `/opt/hrm/current/public` (symlink flipped atomically per release)

  `/etc/nginx/conf.d/hrm.conf`:

  ```nginx
  server {
      listen 80;
      server_name app.yourdomain.com;
      root /opt/hrm/current/public;
      index index.php;
      charset utf-8;

      add_header X-Frame-Options "SAMEORIGIN";
      add_header X-Content-Type-Options "nosniff";

      location / {
          try_files $uri $uri/ /index.php?$query_string;
      }

      location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff2?)$ {
          expires 1y;
          access_log off;
      }

      location ~ \.php$ {
          try_files $uri =404;
          fastcgi_pass unix:/run/php-fpm/www.sock;
          fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
          include fastcgi_params;
      }

      location ~ /\.(?!well-known).* {
          deny all;
      }

      location ~ /\.env { deny all; }
  }
  ```

  ```bash
  nginx -t && systemctl reload nginx
  ```

---

## 4. Database: MariaDB (MySQL‑compatible)

- [ ] Install & secure

  ```bash
  dnf install -y mariadb-server
  systemctl enable --now mariadb
  mariadb-secure-installation     # set root pwd, remove anonymous/test db
  ```

- [ ] App DB + user (match values you'll put in `shared/.env`)

  ```sql
  CREATE DATABASE hrm_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
  CREATE USER 'hrm_user'@'127.0.0.1' IDENTIFIED BY '<STRONG_PASSWORD>';
  GRANT ALL PRIVILEGES ON hrm_production.* TO 'hrm_user'@'127.0.0.1';
  FLUSH PRIVILEGES;
  ```

---

## 5. Node + Composer

- [ ] Node 22 (LTS) — for `npm ci && npm run build` inside releases

  ```bash
  curl -fsSL https://rpm.nodesource.com/setup_22.x | bash -
  dnf install -y nodejs
  node -v && npm -v
  ```

- [ ] Composer 2

  ```bash
  php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
  php composer-setup.php --install-dir=/usr/local/bin --filename=composer
  rm composer-setup.php
  composer --version
  ```

---

## 6. Blue/green layout & shared secrets

Deploy dirs are owned by **nginx** (the php‑fpm worker user) — not `www-data`.

- [ ] Scaffold `/opt/hrm` (matches `deploy-bluegreen.sh` defaults)

  ```bash
  mkdir -p /opt/hrm/releases /opt/hrm/shared
  cp .env.production /opt/hrm/shared/.env     # see below — this is the REAL prod env
  chown -R nginx:nginx /opt/hrm
  chmod 600 /opt/hrm/shared/.env
  ```

  > Place a production `.env` in `/opt/hrm/shared/.env`. The blue/green `prepare` symlinks it into every release, so secrets never touch git (`gitignore` the `*.production` file locally too).

- [ ] Production `.env` — the defaults in `.env.example` are **sqlite/local**; these must change:

  ```env
  APP_NAME=hrm-laravel-base
  APP_ENV=production
  APP_DEBUG=false
  APP_URL=https://app.yourdomain.com
  APP_KEY=<run: php artisan key:generate --show>

  DB_CONNECTION=mysql
  DB_HOST=127.0.0.1
  DB_PORT=3306
  DB_DATABASE=hrm_production
  DB_USERNAME=hrm_user
  DB_PASSWORD=<STRONG_PASSWORD>

  LOG_CHANNEL=stack
  LOG_LEVEL=warning

  SESSION_DRIVER=database
  SESSION_LIFETIME=120
  CACHE_STORE=database
  QUEUE_CONNECTION=database

  MAIL_MAILER=log          # swap to smtp when mail is wired up
  MAIL_FROM_ADDRESS=noreply@app.yourdomain.com
  MAIL_FROM_NAME="${APP_NAME}"
  ```

- [ ] (Optional, later) Redis for sessions/cache — not required for MVP; `database` drivers keep first setup to one process.

---

## 7. First deploy (blue/green)

- [ ] Scripts default to `APP_USER=nginx` on this box (override with `APP_USER=...` if needed):

  ```bash
  cd /root  # scratch dir — clones from the git remote configured in this checkout
  git clone -b main https://github.com/<you>/<repo>.git hrm-deploy
  cd hrm-deploy

  APP_USER=nginx php /opt/hrm/bin/deploy-bluegreen.sh prepare main
  ```

  Actually the script lives in the repo — grab it once:

  ```bash
  cd hrm-deploy
  APP_USER=nginx \
  BASE_DIR=/opt/hrm \
  ./scripts/deploy-bluegreen.sh prepare main
  ```

- [ ] Run the sequence:

  ```bash
  APP_USER=nginx ./scripts/deploy-bluegreen.sh prepare main   # checkout, composer --no-dev, npm build, symlinks, caches
  APP_USER=nginx ./scripts/deploy-bluegreen.sh migrate        # artisan migrate --force on GREEN (blue still live)
  APP_USER=nginx ./scripts/deploy-bluegreen.sh healthcheck    # abort on failure = no swap
  APP_USER=nginx ./scripts/deploy-bluegreen.sh swap           # atomic symlink flip
  APP_USER=nginx ./scripts/deploy-bluegreen.sh smoke          # /up, /login, /setup, Vite manifest
  ```

- [ ] If `prepare` ever has no local origin to clone from, set it explicitly:

  ```bash
  APP_USER=nginx ./scripts/deploy-bluegreen.sh prepare main # needs `git remote get-url origin` → pre-populate remote
  ```

---

## 8. Long-running processes (systemd + cron)

- [ ] **Queue worker** — dequeues ERP jobs; `swap` calls `artisan queue:restart` which only matters if a worker runs.

  The unit is committed at `scripts/hrm-queue.service`. Install it under the same name the runbook and `deploy.yml` use:

  ```bash
  sudo cp scripts/hrm-queue.service /etc/systemd/system/hrm-queue.service
  systemctl daemon-reload
  systemctl enable --now hrm-queue
  ```

- [ ] **Scheduler** — cron for Laravel; user must be `nginx` on this box:

  ```bash
  echo '* * * * * nginx cd /opt/hrm/current && /usr/bin/php artisan schedule:run >> /dev/null 2>&1' > /etc/cron.d/hrm
  systemctl restart crond
  ```

  > The existing runbook/demo files say `www-data` — that’s Ubuntu syntax. Replace with `nginx` on AlmaLinux.

---

## 9. SSL (Let’s Encrypt)

- [ ] Certbot

  ```bash
  dnf install -y certbot python3-certbot-nginx
  certbot --nginx -d app.yourdomain.com
  systemctl enable --now certbot-renew.timer
  ```

---

## 10. Post-deploy verification

- [ ] Health probe (exit 0 = healthy)

  ```bash
  APP_URL=https://app.yourdomain.com scripts/health-check-probe.sh
  ```

- [ ] Manual smoke: open `https://app.yourdomain.com/login` → 200; `/up` → 200; register → org onboarding `/setup` works.
- [ ] `tail -f /opt/hrm/current/storage/logs/laravel.log` — no errors during the above.
- [ ] `systemctl status hrm-queue` — running; `php artisan queue:monitor` (MVP: database queue).

---

## 11. Day‑2 CI (GitHub Actions)

- [ ] `.github/workflows/ci.yml` (added) runs on every push/PR to `main`/`develop`:
      - `composer validate`, Laravel Pint `--test`, full **unit+feature** suite (sqlite `:memory:`), `npm run build`.
      - Run locally equivalent: `composer install && vendor/bin/pint --test && php artisan test --no-coverage && npm ci && npm run build`.
- [ ] `.github/workflows/dusk.yml` already exists for browser tests (MySQL service).
- [ ] After a green CI run, deploy from the server: pull latest `main` and repeat §7 (prepare → migrate → healthcheck → swap → smoke). Rollback is one command: `./scripts/deploy-bluegreen.sh rollback`.

---

## Checklist of gotchas specific to AlmaLinux

- [ ] php-fpm user is **nginx** (not `www-data`) — pool, socket ownership, cron, systemd unit, and `APP_USER=nginx` all agree.
- [ ] SELinux set to `permissive` until the nginx↔`/opt/hrm` wiring is proven.
- [ ] `dnf module`, not `apt` — Remi is the PHP source; never mix in Ubuntu packages.
- [ ] Production DB is **MariaDB/MySQL**; the app’s `.env.example` still says sqlite — `shared/.env` overrides it.
- [ ] `composer test` uses `|| true` and writes `docs/testResults.txt` — never gate CI on it; use `php artisan test`.
- [ ] Every release rebuilds assets (`prepare` runs `npm run build`) — no manual Vite uploads.

*Ready to go when: `/up`, `/login`, `/setup` = 200, queue worker is green, scheduler cron is owned by `nginx`, and `APP_URL` serves over HTTPS.*