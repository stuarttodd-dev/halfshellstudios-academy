FROM php:8.3-cli-alpine

WORKDIR /app

# Runtime config comes from env_file at container start — not COPY .env
COPY print-env.php .
