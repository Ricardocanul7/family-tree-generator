# Guía de Despliegue

## Despliegue con Docker (Producción básica)

### Requisitos

- Docker y Docker Compose
- Git
- Dominio (opcional, con configuración DNS)

### Pasos

```bash
# 1. Clonar en el servidor
git clone <repo-url> /opt/family-tree
cd /opt/family-tree

# 2. Configurar variables de entorno
cp src/.env.example src/.env
# Editar src/.env con valores de producción:
#   APP_ENV=production
#   APP_DEBUG=false
#   APP_URL=https://tudominio.com
#   DB_HOST=db
#   DB_DATABASE=family_tree
#   DB_USERNAME=family_tree_user
#   DB_PASSWORD=<password_segura>

# 3. Construir e iniciar contenedores
docker compose up -d --build

# 4. Instalar dependencias
docker compose exec app composer install --optimize-autoloader --no-dev

# 5. Generar key y optimizaciones
docker compose exec app php artisan key:generate
docker compose exec app php artisan storage:link
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache

# 6. Migrar y seedear
docker compose exec app php artisan migrate --force
docker compose exec app php artisan db:seed --force

# 7. Crear usuario admin
docker compose exec app php artisan make:filament-user
```

### Cambiar puerto

Edita `docker-compose.yml` y cambia el mapeo de puertos:

```yaml
ports:
  - "443:80"   # Para usar HTTPS con proxy reverso
```

### Configurar HTTPS (Nginx + Let's Encrypt)

Se recomienda usar un proxy reverso (Nginx, Caddy, Traefik) para manejar SSL.

**Ejemplo con Nginx como proxy reverso:**

```nginx
server {
    listen 443 ssl;
    server_name tudominio.com;

    ssl_certificate /etc/letsencrypt/live/tudominio.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/tudominio.com/privkey.pem;

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
    server_name tudominio.com;
    return 301 https://$server_name$request_uri;
}
```

---

## Despliegue sin Docker (Producción)

### Requisitos

- PHP 8.3+ con extensiones: pdo_mysql, mbstring, gd, zip, intl
- Composer
- MySQL 8.0
- Node.js + NPM
- Servidor web (Nginx)

### Pasos

```bash
# 1. Clonar
git clone <repo-url> /var/www/family-tree
cd /var/www/family-tree/src

# 2. Dependencias
composer install --optimize-autoloader --no-dev
npm ci && npm run build

# 3. Configurar .env
cp .env.example .env
php artisan key:generate

# 4. Base de datos
php artisan migrate --force
php artisan db:seed --force

# 5. Storage
php artisan storage:link

# 6. Cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Permisos
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### Nginx sin Docker

```nginx
server {
    listen 80;
    server_name tudominio.com;
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

## Variables de Entorno

| Variable       | Descripción                    | Ejemplo                          |
|----------------|--------------------------------|----------------------------------|
| `APP_ENV`      | Entorno de la aplicación       | `production`                     |
| `APP_DEBUG`    | Modo debug                     | `false`                          |
| `APP_URL`      | URL pública de la aplicación   | `https://arbolfamiliar.com`      |
| `DB_HOST`      | Host de MySQL (Docker: `db`)   | `db`                             |
| `DB_PORT`      | Puerto de MySQL                | `3306`                           |
| `DB_DATABASE`  | Nombre de la base de datos     | `family_tree`                    |
| `DB_USERNAME`  | Usuario de MySQL               | `family_tree_user`               |
| `DB_PASSWORD`  | Contraseña de MySQL            | `password_segura`                |

---

## Mantenimiento

```bash
# Actualizar código
git pull
docker compose exec app composer install --optimize-autoloader --no-dev
docker compose exec app php artisan migrate --force
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache

# Respaldar base de datos
docker compose exec db mysqldump -u family_tree_user -p family_tree > backup.sql

# Ver logs
docker compose logs -f
docker compose exec app tail -f storage/logs/laravel.log

# Reiniciar contenedores
docker compose restart
```
