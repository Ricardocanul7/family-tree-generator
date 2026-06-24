# Árbol Familiar - Especificaciones del Proyecto

## Stack Tecnológico
- **Backend**: Laravel 13 + PHP 8.3
- **Base de datos**: MySQL 8.0
- **Frontend**: Tailwind CSS 4 + Vite 8 + Alpine.js (vía CDN)
- **Visualización**: D3.js v7 (árbol genealógico interactivo)
- **Admin**: Filament Admin v3
- **Infraestructura**: Docker (nginx + php-fpm + mysql)

## Infraestructura (Docker)
- **docker-compose.yml**: 3 servicios (`app`, `web`, `db`)
- `app`: PHP 8.3-FPM con extensiones pdo_mysql, gd, zip, intl, etc.
- `web`: nginx:alpine, puerto `8080:80`
- `db`: MySQL 8.0, puerto `3307:3306`, DB: `family_tree`
- Volumen persistente: `db_data` para MySQL

## Modelos de Datos

### Person
- `id` (increments)
- `first_name` (string, required)
- `last_name` (string, required)
- `birth_date` (date, nullable)
- `death_date` (date, nullable)
- `gender` (enum: male/female, nullable)
- `photo` (string, nullable - ruta storage)
- `biography` (text, nullable)
- `timestamps`

### Relationship (pivot parent-child)
- `id` (increments)
- `parent_id` (FK -> people, cascadeOnDelete)
- `child_id` (FK -> people, cascadeOnDelete)
- Unique: `[parent_id, child_id]`

### Relaciones Eloquent
- `Person::parents()`: belongsToMany via `relationships` como `child_id`
- `Person::children()`: belongsToMany via `relationships` como `parent_id`
- `Relationship::parent()`: belongsTo Person como `parent_id`
- `Relationship::child()`: belongsTo Person como `child_id`

### Accesors (Person)
- `photo_url`: retorna `Storage::url()` o fallback a ui-avatars.com
- `full_name`: `"{first_name} {last_name}"`

## Rutas

| Método | URI | Nombre | Controlador |
|--------|-----|--------|-------------|
| GET | `/` | `family-tree.index` | FamilyTreeController@index |
| GET | `/api/tree/full` | `family-tree.full` | FamilyTreeController@fullTree |
| GET | `/api/tree/{person}` | `family-tree.data` | FamilyTreeController@treeData |
| GET | `/tree/{person}` | `family-tree.person` | FamilyTreeController@show |
| GET | `/lang/{locale}` | `language.switch` | Closure |

## Controlador (FamilyTreeController)

### `index()`
- Obtiene todas las personas con sus padres y conteo de hijos
- Filtra personas raíz (sin padres); si no hay, usa todas
- Renderiza `family-tree.index`

### `show(Person $person)`
- Renderiza `family-tree.tree` con la persona como raíz

### `treeData(Person $person)`
- Construye árbol JSON recursivo (máx. 10 niveles de profundidad)
- Datos por nodo: id, name, first_name, last_name, photo, birth_date, death_date, gender, biography, children_count, children[]

### `fullTree()`
- Obtiene personas sin padres como raíces
- Si hay múltiples raíces, las agrupa en un nodo "Familias"

## Filament Admin (PersonResource)
- **Form** (2 columnas):
  - Sección "Información Personal": first_name, last_name, gender, birth_date, death_date, photo (file upload avatar), biography
  - Sección "Relaciones Familiares": parents (select multiple), children (select multiple)
- **Table**: foto circular, nombre, apellido, género, nacimiento, fallecimiento, hijos count
- **Filtros**: por género
- **Acciones**: editar, "Ver Árbol" (link a ruta pública)

## Frontend

### Layout (`layouts/app.blade.php`)
- Navbar con logo, links a "Ver Árbol" y "Admin"
- Selector de idioma (en/es/pl) mediante hover dropdown
- Botón dark mode con persistencia en localStorage
- Soporte dark mode vía clase `.dark` en `<html>`

### Vista Principal (`family-tree/index.blade.php`)
- Árbol genealógico completo renderizado con D3.js
- Carga datos de `/api/tree/full`
- Zoom, pan, colapsar/expandir nodos
- Modal con info de persona al hacer clic
- Cards con: foto circular, nombre, fechas, contador de hijos
- Código duplicado entre index.blade.php y tree.blade.php (misma lógica D3)

### Vista Individual (`family-tree/tree.blade.php`)
- Mismo árbol D3 pero desde una persona específica
- Carga datos de `/api/tree/{person}`

### Internacionalización
- 3 idiomas: English (en), Español (es), Polski (pl)
- Archivos en `lang/{en,es,pl}.json`
- Middleware `SetLocale` usa sesión para persistir idioma
- Ruta `/lang/{locale}` para cambiar idioma

## Comandos Útiles

```bash
# Setup completo (primera vez)
composer run setup

# Desarrollo local (servidor + queue + logs + vite)
composer run dev

# Ejecutar tests
composer run test

# Comandos Docker
docker-compose up -d --build
docker-compose exec app bash
```

## Notas Técnicas
- `composer run test` ejecuta `php artisan config:clear` primero
- Filament admin en `/admin`
- Puerto nginx: `8080`
- Puerto MySQL: `3307`
- Los archivos de idioma están en `lang/*.json` (no en `lang/vendor/`)
- El middleware `SetLocale` se agrega al grupo `web` en `bootstrap/app.php`
- El seeder `FamilyTreeSeeder` crea 3 generaciones de la familia García
