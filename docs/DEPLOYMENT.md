# Deployment Guide

## Docker Deployment (Basic Production)

### Requirements

- Docker and Docker Compose
- Git
- Domain (optional, with DNS configuration)

### Steps

```bash
# 1. Clone on the server
git clone <repo-url> /opt/family-tree
cd /opt/family-tree

# 2. Configure environment variables
cp src/.env.example src/.env
# Edit src/.env with production values:
#   APP_ENV=production
#   APP_DEBUG=false
#   APP_URL=https://yourdomain.com
#   DB_HOST=db
#   DB_DATABASE=family_tree
#   DB_USERNAME=family_tree_user
#   DB_PASSWORD=<secure_password>

# 3. Build and start containers
docker compose up -d --build

# 4. Install dependencies
docker compose exec app composer install --optimize-autoloader --no-dev

# 5. Generate key and optimizations
docker compose exec app php artisan key:generate
docker compose exec app php artisan storage:link
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache

# 6. Migrate and seed
docker compose exec app php artisan migrate --force
docker compose exec app php artisan db:seed --force

# 7. Create admin user
docker compose exec app php artisan make:filament-user
```

### Change Port

Edit `docker-compose.yml` and change the port mapping:

```yaml
ports:
  - "443:80"   # For HTTPS with reverse proxy
```

### Configure HTTPS (Nginx + Let's Encrypt)

It is recommended to use a reverse proxy (Nginx, Caddy, Traefik) to handle SSL.

**Example with Nginx as reverse proxy:**

```nginx
server {
    listen 443 ssl;
    server_name yourdomain.com;

    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;

    location / {
        proxy_pass http://localhost:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}

server {
    listen 80;
    server_name yourdomain.com;
    return 301 https://$server_name$request_uri;
}
```

---

## Non-Docker Deployment (Production)

### Requirements

- PHP 8.3+ with extensions: pdo_mysql, mbstring, gd, zip, intl
- Composer
- MySQL 8.0
- Node.js + NPM
- Web server (Nginx)

### Steps

```bash
# 1. Clone
git clone <repo-url> /var/www/family-tree
cd /var/www/family-tree/src

# 2. Dependencies
composer install --optimize-autoloader --no-dev
npm ci && npm run build

# 3. Configure .env
cp .env.example .env
php artisan key:generate

# 4. Database
php artisan migrate --force
php artisan db:seed --force

# 5. Storage
php artisan storage:link

# 6. Cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Permissions
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### Nginx without Docker

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/family-tree/src/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## Environment Variables

| Variable       | Description                    | Example                          |
|----------------|--------------------------------|----------------------------------|
| `APP_ENV`      | Application environment        | `production`                     |
| `APP_DEBUG`    | Debug mode                     | `false`                          |
| `APP_URL`      | Public application URL         | `https://familytree.com`         |
| `DB_HOST`      | MySQL host (Docker: `db`)      | `db`                             |
| `DB_PORT`      | MySQL port                     | `3306`                           |
| `DB_DATABASE`  | Database name                  | `family_tree`                    |
| `DB_USERNAME`  | MySQL user                     | `family_tree_user`               |
| `DB_PASSWORD`  | MySQL password                 | `secure_password`                |

---

## Maintenance

```bash
# Update code
git pull
docker compose exec app composer install --optimize-autoloader --no-dev
docker compose exec app php artisan migrate --force
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache

# Backup database
docker compose exec db mysqldump -u family_tree_user -p family_tree > backup.sql

# View logs
docker compose logs -f
docker compose exec app tail -f storage/logs/laravel.log

# Restart containers
docker compose restart
```
