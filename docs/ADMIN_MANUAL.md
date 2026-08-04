# Admin Manual

## Initial Provisioning
1. Run migrations and seeders.
2. Login with seeded admin credentials.
3. Rotate admin password.
4. Create production API token and disable default token.

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
