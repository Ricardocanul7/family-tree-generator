# Arquitectura del Proyecto

## Stack Tecnológico

| Componente     | Tecnología                             |
|----------------|----------------------------------------|
| Backend        | Laravel 13 + PHP 8.3                   |
| Base de datos  | MySQL 8.0                              |
| Admin Panel    | Filament Admin v3                      |
| Frontend CSS   | Tailwind CSS 4 + Vite 8                |
| Interactividad | Alpine.js (vía CDN)                    |
| Visualización  | D3.js v7 (árbol genealógico)           |
| Contenedores   | Docker (nginx:alpine + php:8.3-fpm)    |

---

## Infraestructura Docker

`docker-compose.yml` define 3 servicios:

| Servicio | Imagen | Puerto Host | Propósito |
|----------|--------|-------------|-----------|
| `app`    | PHP 8.3-fpm (build local) | — | PHP-FPM + Composer |
| `web`    | nginx:alpine | `8080:80` | Servidor web |
| `db`     | mysql:8.0 | `3307:3306` | Base de datos |

**Volúmenes:**
- `./src:/var/www` — código de la aplicación (compartido entre app y web)
- `./php/local.ini:/usr/local/etc/php/conf.d/local.ini` — configuración PHP
- `./nginx/default.conf:/etc/nginx/conf.d/default.conf` — configuración Nginx
- `db_data:/var/lib/mysql` — persistencia de MySQL

**Red:** Red bridge `family-tree-network` para comunicación entre contenedores.

**Healthcheck:** MySQL tiene healthcheck con `mysqladmin ping`; `app` espera a `db` saludable.

---

## Modelos de Datos

### Person

| Campo       | Tipo           | Nullable | Descripción                |
|-------------|----------------|----------|----------------------------|
| id          | bigIncrements  | No       | ID primario                |
| first_name  | string(255)    | No       | Nombre                     |
| last_name   | string(255)    | No       | Apellido                   |
| birth_date  | date           | Sí       | Fecha de nacimiento        |
| death_date  | date           | Sí       | Fecha de fallecimiento     |
| gender      | enum           | Sí       | `male` o `female`          |
| photo       | string(255)    | Sí       | Ruta en storage            |
| biography   | text           | Sí       | Biografía                  |
| timestamps  | —              | No       | created_at, updated_at     |

### Relationship

| Campo     | Tipo          | Nullable | Descripción                     |
|-----------|---------------|----------|----------------------------------|
| id        | bigIncrements | No       | ID primario                     |
| parent_id | foreignId     | No       | FK → people (cascadeOnDelete)   |
| child_id  | foreignId     | No       | FK → people (cascadeOnDelete)   |
| timestamps| —             | No       | created_at, updated_at          |

**Unique:** `[parent_id, child_id]` — no permite relaciones duplicadas.

---

## Relaciones Eloquent

```php
// Person
public function parents(): BelongsToMany
    // belongsToMany via 'relationships' como child_id
    $this->belongsToMany(Person::class, 'relationships', 'child_id', 'parent_id');

public function children(): BelongsToMany
    // belongsToMany via 'relationships' como parent_id
    $this->belongsToMany(Person::class, 'relationships', 'parent_id', 'child_id');

// Relationship
public function parent(): BelongsTo  // belongsTo Person (parent_id)
public function child(): BelongsTo   // belongsTo Person (child_id)
```

### Accesors

| Accesor     | Retorno                                                |
|-------------|--------------------------------------------------------|
| `photo_url` | `Storage::url($this->photo)` o fallback a ui-avatars.com |
| `full_name` | `"{$this->first_name} {$this->last_name}"`             |

---

## Rutas

| Método | URI                  | Nombre             | Controlador / Acción               |
|--------|----------------------|--------------------|-------------------------------------|
| GET    | `/`                  | `family-tree.index`| `FamilyTreeController@index`        |
| GET    | `/api/tree/full`     | `family-tree.full` | `FamilyTreeController@fullTree`     |
| GET    | `/api/tree/{person}` | `family-tree.data` | `FamilyTreeController@treeData`     |
| GET    | `/tree/{person}`     | `family-tree.person`| `FamilyTreeController@show`        |
| GET    | `/lang/{locale}`     | `language.switch`  | Closure                             |
|        | `/admin`             | (Filament)         | Filament auto-registrado            |

---

## Controlador FamilyTreeController

### `index()`
1. Obtiene todas las personas con `parents` y conteo de `children`
2. Filtra personas raíz (sin padres); si no hay raíces, usa todas
3. Retorna vista `family-tree.index`

### `show(Person $person)`
- Route-model-binding
- Retorna vista `family-tree.tree` con la persona como raíz

### `treeData(Person $person)`
- Construye árbol JSON recursivo llamando a `buildTree()`
- Máximo 10 niveles de profundidad

### `buildTree(Person $person, int $depth, int $maxDepth)`
- Para cada persona, obtiene hijos ordenados por `birth_date` con conteo de hijos
- Estructura de cada nodo:
```json
{
  "id": 1,
  "name": "Carlos García",
  "first_name": "Carlos",
  "last_name": "García",
  "photo": "https://ui-avatars.com/api/...",
  "birth_date": "15/03/1950",
  "death_date": null,
  "gender": "male",
  "biography": "Fundador de la familia García.",
  "children_count": 3,
  "children": [ ... ]
}
```

### `fullTree()`
1. Obtiene personas sin padres como raíces
2. Si no hay raíces, usa la primera persona registrada
3. Si hay una sola raíz, retorna ese árbol directamente
4. Si hay múltiples raíces, las agrupa en un nodo virtual "Familias" (id: 0)

---

## Frontend

### Layout (`layouts/app.blade.php`)
- Navbar con logo, enlaces a "Ver Árbol" y "Admin"
- Selector de idioma (en/es/pl) mediante hover dropdown
- Botón dark mode con persistencia en localStorage vía clase `.dark` en `<html>`
- Soporte dark mode mediante reglas CSS anidadas bajo `.dark`

### Vista Principal (`family-tree/index.blade.php`)
- Árbol genealógico completo con D3.js v7
- Carga datos desde `/api/tree/full`
- **Controles:** zoom in/out, reset, exportar SVG
- **Nodos:** cards con foto circular (clipPath), nombre, fechas, contador de hijos
- **Código de género:** borde azul para masculino, rosa para femenino
- **Colapsar/expandir:** toggle con animación de transición (500ms)
- **Modal:** datos completos y biografía al hacer clic en un nodo
- **Export SVG:** descarga el árbol como archivo SVG vectorial
- **Soporte impresión:** media query `@media print`

### Vista Individual (`family-tree/tree.blade.php`)
- Mismo árbol D3.js pero desde una persona específica
- Carga datos desde `/api/tree/{person}`
- Enlace "← Ver árbol completo" para volver a la vista general

### Dark Mode
- Detecta preferencia del sistema (`prefers-color-scheme: dark`)
- Persistencia en localStorage (`theme: dark/light`)
- Variables CSS personalizadas para colores en modo oscuro
- Botón toggle con iconos de luna/sol

---

## Filament Admin (PersonResource)

### Formulario (2 columnas)

**Sección "Información Personal"** (columnas 2):
- `first_name` — TextInput (required)
- `last_name` — TextInput (required)
- `gender` — Select (male/female)
- `birth_date` — DatePicker
- `death_date` — DatePicker
- `photo` — FileUpload (image, avatar, directorio `people`)
- `biography` — Textarea (columnSpanFull)

**Sección "Relaciones Familiares"** (columnas 2):
- `parents` — Select multiple, searchable, preload, ordenado por nombre
- `children` — Select multiple, searchable, preload, ordenado por nombre

### Tabla
- Foto circular con fallback a avatar automático
- Nombre, apellido (searchable), género, fecha nacimiento, fecha fallecimiento, hijos count
- Ordenado por `created_at` descendente
- **Filtros:** por género (male/female)
- **Acciones:** editar, "Ver Árbol" (link a ruta pública)

---

## Internacionalización

- 3 idiomas: English (en), Español (es), Polski (pl)
- Archivos JSON planos en `lang/{en,es,pl}.json`
- Middleware `SetLocale` se agrega al grupo `web` en `bootstrap/app.php`
- Persistencia del idioma en sesión
- Ruta `/lang/{locale}` para cambiar idioma

---

## Seeder

`FamilyTreeSeeder` crea 3 generaciones de la familia García:

| Generación | Miembros |
|------------|----------|
| 1 (Abuelos) | Carlos García, María López |
| 2 (Hijos) | Pedro, Ana, Juan García López |
| 3 (Nietos) | Miguel, Lucía, Sofía, Diego, Valentina García |

Relaciones: Carlos y María son padres de Pedro, Ana, Juan. Cada hijo tiene sus respectivos hijos.
