# Docker Setup for NordFlex

This directory contains Docker configuration for the NordFlex e-commerce platform.

## Services

### MySQL Database
- **Image**: mysql:8.0
- **Container**: nordflex_mysql
- **Port**: 3306
- **Database**: u672825292_nordflex
- **Username**: u672825292_admin
- **Password**: EftRQgW~2

### Mailpit (Email Testing)
- **Image**: axllent/mailpit:latest
- **Container**: nordflex_mailpit
- **SMTP Port**: 1025
- **Web UI Port**: 8025
- **Web UI URL**: http://localhost:8025

## Quick Start

1. **Start Services**:
   ```bash
   ./docker-start.sh
   ```
   
   Or manually:
   ```bash
   docker-compose up -d
   ```

2. **Run Migrations**:
   ```bash
   php artisan migrate --seed
   ```

3. **Access Services**:
   - Application: http://localhost:8000
   - Mailpit Web UI: http://localhost:8025
   - MySQL: localhost:3306

## Commands

- **Start services**: `docker-compose up -d`
- **Stop services**: `docker-compose down`
- **View logs**: `docker-compose logs -f [service_name]`
- **Restart service**: `docker-compose restart [service_name]`

## Data Persistence

MySQL data is persisted in a Docker volume named `mysql_data`. To reset the database:

```bash
docker-compose down -v
docker-compose up -d
```

## Network

All services run on the `nordflex_network` bridge network for internal communication.