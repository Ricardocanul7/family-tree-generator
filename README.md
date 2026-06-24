# Family Tree - Árbol Familiar Interactivo

Aplicación web para gestionar y visualizar árboles genealógicos interactivos construida con Laravel 13, Filament Admin y D3.js.

## Capturas de Pantalla

| Árbol Familiar | Panel de Administración | Editar Persona |
|:---:|:---:|:---:|
| ![Árbol Familiar](docs/screenshots/family-tree-page.png) | ![Admin: Lista de Personas](docs/screenshots/people-admin-module.png) | ![Admin: Editar Persona](docs/screenshots/people-admin-module-edit.png) |
| Vista interactiva del árbol genealógico con D3.js. Zoom, pan, colapsar/expandir nodos. | Panel Filament con listado de personas, filtros por género, foto circular y acciones. | Formulario de edición con información personal, foto y relaciones familiares. |

| Exportación SVG |
|:---:|
| ![Exportación SVG](docs/screenshots/family-tree-export-sample.svg) |
| El árbol se puede exportar a formato SVG vectorial. |

## Stack

- **Backend:** Laravel 13 + PHP 8.3
- **Base de datos:** MySQL 8.0
- **Admin Panel:** Filament Admin v3
- **Frontend:** Tailwind CSS 4 + Vite 8 + Alpine.js (vía CDN)
- **Visualización:** D3.js v7 (árbol genealógico interactivo)
- **Infraestructura:** Docker (PHP 8.3-fpm + Nginx + MySQL)

## Requisitos

- Docker y Docker Compose

## Instalación y uso

```bash
# 1. Clonar el repositorio
git clone <repo-url> family-tree
cd family-tree

# 2. Iniciar contenedores
docker compose up -d --build

# 3. Instalar dependencias de Composer y configurar
docker compose exec app composer install

# 4. Configurar .env (verificar DB_HOST=db, DB_PORT=3306)
docker compose exec app php artisan key:generate

# 5. Ejecutar migraciones y seed
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed

# 6. Crear usuario admin (para el panel Filament)
docker compose exec app php artisan make:filament-user
```

O en un solo paso:
```bash
docker compose exec app composer run setup
```

## Acceso

| Sitio              | URL                                       |
|--------------------|-------------------------------------------|
| Árbol público      | http://localhost:8080                     |
| Admin Filament     | http://localhost:8080/admin               |
| Árbol por persona  | http://localhost:8080/tree/{id}           |
| API (completo)     | http://localhost:8080/api/tree/full       |
| API (por persona)  | http://localhost:8080/api/tree/{id}       |

> Para cambiar el puerto, edita `"8080:80"` en `docker-compose.yml`.

## Funcionalidades

- **Árbol interactivo**: zoom, pan, nodos colapsables/expandibles con D3.js v7
- **Panel admin Filament**: CRUD completo con foto, relaciones padres/hijos, filtros
- **Vista por persona**: árbol desde un miembro específico
- **Exportación SVG**: descarga el árbol como imagen vectorial
- **Dark mode**: con persistencia en localStorage
- **Internacionalización**: Español, English, Polski
- **Fotos y avatares**: foto real o avatar generado automáticamente
- **Modal informativo**: datos completos y biografía al hacer clic en un nodo

## Estructura del proyecto

```
├── docker-compose.yml       # Servicios: app (PHP), web (Nginx), db (MySQL)
├── Dockerfile               # PHP 8.3-fpm con extensiones
├── nginx/default.conf       # Configuración Nginx
├── php/local.ini            # Configuración PHP
├── docs/                    # Documentación
│   ├── screenshots/         # Capturas de pantalla
│   ├── ARCHITECTURE.md      # Arquitectura técnica
│   ├── API.md               # Referencia de API
│   └── DEPLOYMENT.md        # Guía de despliegue
└── src/                     # Código Laravel
    ├── app/
    │   ├── Models/Person.php           # Modelo Persona
    │   ├── Models/Relationship.php     # Relación padre-hijo
    │   ├── Filament/Resources/PersonResource.php  # CRUD admin
    │   ├── Http/Controllers/FamilyTreeController.php
    │   └── Http/Middleware/SetLocale.php
    ├── database/
    │   ├── migrations/       # create_people, create_relationships
    │   └── seeders/FamilyTreeSeeder.php  # 3 generaciones García
    ├── lang/{en,es,pl}.json  # Traducciones
    ├── resources/views/
    │   ├── layouts/app.blade.php
    │   └── family-tree/
    │       ├── index.blade.php  # Árbol general (D3.js)
    │       └── tree.blade.php   # Árbol por persona (D3.js)
    └── routes/web.php
```

## Comandos útiles

```bash
# Setup completo
docker compose exec app composer run setup

# Desarrollo (servidor + logs + vite)
docker compose exec app composer run dev

# Ejecutar tests
docker compose exec app composer run test

# Ver logs
docker compose exec app tail -f storage/logs/laravel.log

# Acceder a MySQL
docker compose exec db mysql -u family_tree_user -p family_tree

# Migrar desde cero con datos
docker compose exec app php artisan migrate:fresh --seed
```

## Solución de problemas

**Error 500:**
```bash
docker compose exec app php artisan storage:link
docker compose exec app php artisan optimize:clear
```

**El árbol no carga datos:** Verificar migraciones y seed.

**Puerto 8080 en uso:** Cambiar a `"8081:80"` en `docker-compose.yml`.

## Documentación adicional

- [Arquitectura](docs/ARCHITECTURE.md) - Detalles técnicos del proyecto
- [API](docs/API.md) - Referencia de endpoints
- [Despliegue](docs/DEPLOYMENT.md) - Guía de producción
