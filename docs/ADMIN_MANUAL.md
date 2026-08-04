# Admin Manual

## Initial Provisioning
1. Run migrations and seeders.
2. Login with seeded admin credentials.
3. Rotate admin password.
4. Create production API token and disable default token.

## RBAC Roles
- **Owner**: unrestricted access, including user management, settings, API management, and destructive actions.
- **Developer**: can view, create, update, run, execute tests, configure cron jobs, and export reports. Developers cannot delete resources, manage users, or change global settings/API configuration.
- **Viewer**: read-only access to dashboards, scripts, connection profiles, deployment logs, cron schedules, settings, and the database schema explorer.

Run `php artisan db:seed --class=RolePermissionSeeder` after deploying this change to apply the three roles and their permissions. The standard `DatabaseSeeder` maps the seeded administrator to **Owner**.

## Security Checklist
- Enforce HTTPS.
- Restrict webhook endpoint access by secret and known telegram sources.
- Use strong credentials for all profiles.
- Set `APP_ENV=production` and `APP_DEBUG=false`.
- Protect storage and log folders from public access.

## Queue/Scheduler Operations
- Run persistent queue worker.
- Register scheduler cron every minute.

## Log Maintenance
- Monitor channels under `storage/logs`.
- Keep retention via settings + server rotation policy.
