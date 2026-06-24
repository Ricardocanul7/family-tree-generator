# Project Architecture

## Tech Stack

| Component      | Technology                            |
|----------------|---------------------------------------|
| Backend        | Laravel 13 + PHP 8.3                  |
| Database       | MySQL 8.0                             |
| Admin Panel    | Filament Admin v3                     |
| Frontend CSS   | Tailwind CSS 4 + Vite 8               |
| Interactivity  | Alpine.js (via CDN)                   |
| Visualization  | D3.js v7 (family tree)                |
| Containers     | Docker (nginx:alpine + php:8.3-fpm)   |

---

## Docker Infrastructure

`docker-compose.yml` defines 3 services:

| Service | Image | Host Port | Purpose |
|---------|-------|-----------|---------|
| `app`   | PHP 8.3-fpm (local build) | — | PHP-FPM + Composer |
| `web`   | nginx:alpine | `8080:80` | Web server |
| `db`    | mysql:8.0 | `3307:3306` | Database |

**Volumes:**
- `./src:/var/www` — application code (shared between app and web)
- `./php/local.ini:/usr/local/etc/php/conf.d/local.ini` — PHP configuration
- `./nginx/default.conf:/etc/nginx/conf.d/default.conf` — Nginx configuration
- `db_data:/var/lib/mysql` — MySQL persistence

**Network:** Bridge network `family-tree-network` for container communication.

**Healthcheck:** MySQL has a healthcheck with `mysqladmin ping`; `app` waits for `db` to be healthy.

---

## Data Models

### Person

| Field       | Type           | Nullable | Description                 |
|-------------|----------------|----------|-----------------------------|
| id          | bigIncrements  | No       | Primary key                 |
| first_name  | string(255)    | No       | First name                  |
| last_name   | string(255)    | No       | Last name                   |
| birth_date  | date           | Yes      | Birth date                  |
| death_date  | date           | Yes      | Death date                  |
| gender      | enum           | Yes      | `male` or `female`          |
| photo       | string(255)    | Yes      | Storage path                |
| biography   | text           | Yes      | Biography                   |
| timestamps  | —              | No       | created_at, updated_at      |

### Relationship

| Field     | Type          | Nullable | Description                      |
|-----------|---------------|----------|----------------------------------|
| id        | bigIncrements | No       | Primary key                      |
| parent_id | foreignId     | No       | FK → people (cascadeOnDelete)    |
| child_id  | foreignId     | No       | FK → people (cascadeOnDelete)    |
| timestamps| —             | No       | created_at, updated_at           |

**Unique:** `[parent_id, child_id]` — prevents duplicate relationships.

---

## Eloquent Relationships

```php
// Person
public function parents(): BelongsToMany
    // belongsToMany via 'relationships' as child_id
    $this->belongsToMany(Person::class, 'relationships', 'child_id', 'parent_id');

public function children(): BelongsToMany
    // belongsToMany via 'relationships' as parent_id
    $this->belongsToMany(Person::class, 'relationships', 'parent_id', 'child_id');

// Relationship
public function parent(): BelongsTo  // belongsTo Person (parent_id)
public function child(): BelongsTo   // belongsTo Person (child_id)
```

### Accessors

| Accessor    | Return                                                |
|-------------|-------------------------------------------------------|
| `photo_url` | `Storage::url($this->photo)` or fallback to ui-avatars.com |
| `full_name` | `"{$this->first_name} {$this->last_name}"`            |

---

## Routes

| Method | URI                  | Name              | Controller / Action                |
|--------|----------------------|--------------------|-------------------------------------|
| GET    | `/`                  | `family-tree.index`| `FamilyTreeController@index`        |
| GET    | `/api/tree/full`     | `family-tree.full` | `FamilyTreeController@fullTree`     |
| GET    | `/api/tree/{person}` | `family-tree.data` | `FamilyTreeController@treeData`     |
| GET    | `/tree/{person}`     | `family-tree.person`| `FamilyTreeController@show`        |
| GET    | `/lang/{locale}`     | `language.switch`  | Closure                             |
|        | `/admin`             | (Filament)         | Filament auto-registered            |

---

## FamilyTreeController

### `index()`
1. Gets all people with `parents` and `children` count
2. Filters root people (no parents); if no roots, uses all
3. Returns `family-tree.index` view

### `show(Person $person)`
- Route-model-binding
- Returns `family-tree.tree` view with the person as root

### `treeData(Person $person)`
- Builds recursive JSON tree by calling `buildTree()`
- Maximum 10 levels deep

### `buildTree(Person $person, int $depth, int $maxDepth)`
- For each person, gets children ordered by `birth_date` with child count
- Each node structure:
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
1. Gets people without parents as roots
2. If no roots, uses the first registered person
3. If there's a single root, returns that tree directly
4. If there are multiple roots, groups them in a virtual "Families" node (id: 0)

---

## Frontend

### Layout (`layouts/app.blade.php`)
- Navbar with logo, links to "View Tree" and "Admin"
- Language selector (en/es/pl) via hover dropdown
- Dark mode button with localStorage persistence via `.dark` class on `<html>`
- Dark mode support via nested CSS rules under `.dark`

### Main View (`family-tree/index.blade.php`)
- Full family tree with D3.js v7
- Loads data from `/api/tree/full`
- **Controls:** zoom in/out, reset, export SVG
- **Nodes:** cards with circular photo (clipPath), name, dates, children count
- **Gender coding:** blue border for male, pink for female
- **Collapse/expand:** toggle with transition animation (500ms)
- **Modal:** full data and biography on node click
- **Export SVG:** download the tree as a vector SVG file
- **Print support:** `@media print` query

### Individual View (`family-tree/tree.blade.php`)
- Same D3.js tree but from a specific person
- Loads data from `/api/tree/{person}`
- Link "← View full tree" to go back to the main view

### Dark Mode
- Detects system preference (`prefers-color-scheme: dark`)
- Persistence in localStorage (`theme: dark/light`)
- Custom CSS variables for dark mode colors
- Toggle button with moon/sun icons

---

## Filament Admin (PersonResource)

### Form (2 columns)

**Section "Personal Information"** (2 columns):
- `first_name` — TextInput (required)
- `last_name` — TextInput (required)
- `gender` — Select (male/female)
- `birth_date` — DatePicker
- `death_date` — DatePicker
- `photo` — FileUpload (image, avatar, directory `people`)
- `biography` — Textarea (columnSpanFull)

**Section "Family Relationships"** (2 columns):
- `parents` — Select multiple, searchable, preload, ordered by name
- `children` — Select multiple, searchable, preload, ordered by name

### Table
- Circular photo with fallback to auto avatar
- First name, last name (searchable), gender, birth date, death date, children count
- Ordered by `created_at` descending
- **Filters:** by gender (male/female)
- **Actions:** edit, "View Tree" (link to public route)

---

## Internationalization

- 3 languages: English (en), Español (es), Polski (pl)
- Flat JSON files in `lang/{en,es,pl}.json`
- `SetLocale` middleware added to `web` group in `bootstrap/app.php`
- Language persistence in session
- Route `/lang/{locale}` to switch language

---

## Seeder

`FamilyTreeSeeder` creates 3 generations of the García family:

| Generation | Members |
|------------|---------|
| 1 (Grandparents) | Carlos García, María López |
| 2 (Children) | Pedro, Ana, Juan García López |
| 3 (Grandchildren) | Miguel, Lucía, Sofía, Diego, Valentina García |

Relationships: Carlos and María are parents of Pedro, Ana, Juan. Each child has their respective children.
