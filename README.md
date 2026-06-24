# Family Tree - Árbol Familiar Interactivo

Aplicación web para gestionar y visualizar árboles genealógicos interactivos construida con Laravel, Filament y D3.js.

## Stack

- **Backend:** Laravel 13 + MySQL 8.0
- **Admin Panel:** Filament 3.3
- **Frontend:** D3.js v7 con Tailwind CSS
- **Infraestructura:** Docker (PHP 8.3-fpm + Nginx)

## Requisitos

- Docker y Docker Compose

## Instalación y uso

```bash
# 1. Clonar el repositorio
git clone <repo-url> family-tree
cd family-tree

# 2. Iniciar contenedores
docker compose up -d

# 3. Instalar dependencias de Composer
docker compose exec app composer install

# 4. Configurar .env (ya incluido, verificar)
#    DB_HOST=db, DB_PORT=3306, etc.

# 5. Generar APP_KEY (si no está seteada)
docker compose exec app php artisan key:generate

# 6. Ejecutar migraciones
docker compose exec app php artisan migrate

# 7. Poblar base de datos con datos de ejemplo
docker compose exec app php artisan db:seed

# 8. Crear usuario admin (opcional, para el panel Filament)
docker compose exec app php artisan make:filament-user
#    Email: admin@admin.com | Password: <lo que quieras>
```

## Acceso

| Sitio              | URL                                       |
|--------------------|-------------------------------------------|
| Árbol público      | http://localhost:8080                     |
| Admin Filament     | http://localhost:8080/admin               |
| Árbol por persona  | http://localhost:8080/tree/{id}           |
| API (árbol completo)| http://localhost:8080/api/tree/full      |
| API (por persona)  | http://localhost:8080/api/tree/{id}       |

> **Nota:** El puerto del contenedor web es `8080:80`. Si quieres usar el puerto 80, cambia `"8080:80"` por `"80:80"` en `docker-compose.yml`.

## Estructura del proyecto

```
├── docker-compose.yml      # Servicios: app (PHP), web (Nginx), db (MySQL)
├── Dockerfile               # PHP 8.3-fpm con extensiones
├── nginx/
│   └── default.conf         # Configuración de Nginx para Laravel
├── php/
│   └── local.ini            # Configuración PHP
└── src/                     # Código de la aplicación Laravel
    ├── app/
    │   ├── Models/
    │   │   ├── Person.php       # Modelo Persona (nombre, fecha, género, foto)
    │   │   └── Relationship.php # Relación padre-hijo
    │   ├── Filament/Resources/
    │   │   └── PersonResource.php  # CRUD admin con Filament
    │   └── Http/Controllers/
    │       └── FamilyTreeController.php  # API y vistas del árbol
    ├── database/
    │   ├── migrations/
    │   │   ├── ..._create_people_table.php
    │   │   └── ..._create_relationships_table.php
    │   └── seeders/
    │       └── FamilyTreeSeeder.php   # Datos de prueba (3 generaciones)
    ├── resources/views/
    │   ├── layouts/app.blade.php      # Layout base
    │   └── family-tree/
    │       ├── index.blade.php        # Árbol general (D3.js)
    │       └── tree.blade.php         # Árbol por persona (D3.js)
    └── routes/web.php
```

## Funcionalidades

- **Árbol interactivo**: zoom, pan, nodos colapsables
- **Panel admin**: CRUD completo para gestionar personas y relaciones familiares
- **Vista por persona**: árbol desde un miembro específico
- **Fotos**: cada persona puede tener foto; si no tiene, se genera avatar automático
- **Modal informativo**: al hacer clic en un nodo se muestra biografía y datos completos

## Comandos útiles

```bash
# Ver logs de Laravel
docker compose exec app tail -f storage/logs/laravel.log

# Acceder a MySQL
docker compose exec db mysql -u family_tree_user -p family_tree

# Ejecutar migraciones desde cero
docker compose exec app php artisan migrate:fresh --seed

# Ver estado de los contenedores
docker compose ps
```

## Solución de problemas

**Error 500 al abrir la página:**
```bash
docker compose exec app php artisan storage:link
docker compose exec app php artisan optimize:clear
```

**El árbol no carga datos:**
- Verificar que las migraciones se ejecutaron: `docker compose exec app php artisan migrate`
- Verificar que hay datos: `docker compose exec app php artisan db:seed`
- Revisar logs: `docker compose exec app tail -f storage/logs/laravel.log`

**Puerto 8080 en uso:**
Cambia `"8080:80"` por `"8081:80"` en `docker-compose.yml` y luego:
```bash
docker compose up -d
```
