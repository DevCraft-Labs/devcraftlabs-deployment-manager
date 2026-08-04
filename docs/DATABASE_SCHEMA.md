# Database Schema

## Core Tables
- `users`: username auth users.
- `redis_profiles`: encrypted Redis credentials and TTL defaults.
- `smtp_profiles`: encrypted SMTP credentials.
- `telegram_connections`: encrypted bot token, chat id, webhook secret.
- `deployment_scripts`: shell script configuration and profile references.
- `deployment_executions`: execution lifecycle and stdout/stderr logs.
- `deployment_schedules`: normalized schedule metadata.
- `application_settings`: server defaults and retention.
- `api_tokens`: hashed bearer token records.
- `connection_test_histories`: redis/smtp/telegram test results.
- `provisioning_database_connections`: encrypted read-only db connection settings.
- `activity_audits`: audit trail (user/ip/context).
- Spatie permission tables + activity log table.

## Sensitive Fields
Encrypted using Eloquent encrypted casts:
- `redis_profiles.password`
- `smtp_profiles.password`
- `telegram_connections.bot_token`
- `telegram_connections.webhook_secret`
- `provisioning_database_connections.password`
