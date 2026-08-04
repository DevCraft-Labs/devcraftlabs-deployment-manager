# API Documentation

Base: `/api`
Auth: `Authorization: Bearer <token>`

## Endpoints
- `POST /deploy/{script}`: Queue deployment for script id.
- `GET /deployment/history`: Paginated deployment history.
- `GET /deployment/status`: Latest execution status.
- `GET /deployment/report`: Report status stream (mapped to history endpoint in current build).
- `POST /test/redis`: Test Redis profile. Payload: `redis_profile_id`, optional `ttl`.
- `POST /test/smtp`: Test SMTP profile. Payload: `smtp_profile_id`, `recipient_email`.
- `POST /test/telegram`: Test Telegram connection. Payload: `telegram_connection_id`.
- `GET /scripts`: List scripts.
- `GET /scripts/{id}`: Script detail.

## Response Codes
- `200` success
- `401` invalid/missing token
- `422` validation failure
- `500` unexpected server error
