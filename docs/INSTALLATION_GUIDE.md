# Installation Guide

## Requirements
- PHP 8.4+
- MySQL 8+
- Redis 6+
- Composer 2+
- Node 20+
- PHP extensions: `openssl`, `pdo_mysql`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `zip`, `gd`

## Setup
1. Clone project.
2. Run `composer install`.
3. Run `cp .env.example .env` (Windows: duplicate `.env.example` as `.env`).
4. Configure database, redis, mail, app url.
5. Run `php artisan key:generate`.
6. Run `php artisan migrate --seed`.
7. Run `npm install`.
8. Run `npm run build`.

## Runtime Services
- Queue worker: `php artisan queue:work --tries=3`
- Scheduler: `php artisan schedule:work`

## cPanel Deployment Notes
- Ensure shell_exec/process execution permissions are enabled by host.
- Point public web root to `/public`.
- Configure cron in cPanel: `* * * * * php /home/USER/app/artisan schedule:run >> /dev/null 2>&1`
- Keep queue worker supervised (Supervisor, systemd, or cPanel process manager if available).
