# DevCraft Labs CPanel Deployment Manager

Production-ready Laravel 13 deployment automation platform for cPanel/shared hosting operations.

## Technology Stack
- Laravel 13, PHP 8.4+
- MySQL, Redis, Queue, Scheduler, Cache
- Blade, Bootstrap 5, AlpineJS, Axios
- Monaco Editor for shell script authoring

## Functional Coverage
- Username/password authentication
- Redis connection manager with SET/GET/TTL/DELETE verification
- SMTP manager with test mail dispatch
- Telegram connection manager and webhook commands
- Deployment script manager with Monaco shell editor
- Cron schedule manager
- Read-only MySQL provisioning explorer
- Internal bearer-token REST API
- XLSX deployment report export
- Full activity audit log
- Application settings module

## Architecture
- `app/Contracts` for interfaces
- `app/Repositories` for data access pattern
- `app/Services` for business logic
- `app/DTOs` for data transport
- `app/Http/Requests` for validation
- `app/Policies` for authorization
- `app/Jobs` for async/background execution

## Logging Channels
- `storage/logs/deployment.log`
- `storage/logs/telegram.log`
- `storage/logs/redis.log`
- `storage/logs/smtp.log`
- `storage/logs/api.log`

## Installation
See `docs/INSTALLATION_GUIDE.md`.

## Documentation
- `docs/API_DOCUMENTATION.md`
- `docs/DATABASE_SCHEMA.md`
- `docs/ERD.md`
- `docs/SEQUENCE_DIAGRAM.md`
- `docs/DEPLOYMENT_ARCHITECTURE.md`
- `docs/USER_MANUAL.md`
- `docs/ADMIN_MANUAL.md`

## Default Seeder Credentials
- Username: `admin`
- Password: `admin12345`

Change credentials immediately for production environments.
