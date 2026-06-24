# Family Tree - Project Specifications

## Tech Stack
- **Backend**: Laravel 13 + PHP 8.3
- **Database**: MySQL 8.0
- **Frontend**: Tailwind CSS 4 + Vite 8 + Alpine.js (via CDN)
- **Visualization**: D3.js v7 (interactive family tree)
- **Admin**: Filament Admin v3
- **Infrastructure**: Docker (nginx + php-fpm + mysql)

## Infrastructure (Docker)
- **docker-compose.yml**: 3 services (`app`, `web`, `db`)
- `app`: PHP 8.3-FPM with pdo_mysql, gd, zip, intl extensions, etc.
- `web`: nginx:alpine, port `8080:80`
- `db`: MySQL 8.0, port `3307:3306`, DB: `family_tree`
- Persistent volume: `db_data` for MySQL

## Data Models

### Person
- `id` (increments)
- `first_name` (string, required)
- `last_name` (string, required)
- `birth_date` (date, nullable)
- `death_date` (date, nullable)
- `gender` (enum: male/female, nullable)
- `photo` (string, nullable - storage path)
- `biography` (text, nullable)
- `timestamps`

### Relationship (pivot parent-child)
- `id` (increments)
- `parent_id` (FK -> people, cascadeOnDelete)
- `child_id` (FK -> people, cascadeOnDelete)
- Unique: `[parent_id, child_id]`

### Eloquent Relationships
- `Person::parents()`: belongsToMany via `relationships` as `child_id`
- `Person::children()`: belongsToMany via `relationships` as `parent_id`
- `Relationship::parent()`: belongsTo Person as `parent_id`
- `Relationship::child()`: belongsTo Person as `child_id`

### Accessors (Person)
- `photo_url`: returns `Storage::url()` or fallback to ui-avatars.com
- `full_name`: `"{first_name} {last_name}"`

## Routes

| Method | URI | Name | Controller |
|--------|-----|------|------------|
| GET | `/` | `family-tree.index` | FamilyTreeController@index |
| GET | `/api/tree/full` | `family-tree.full` | FamilyTreeController@fullTree |
| GET | `/api/tree/{person}` | `family-tree.data` | FamilyTreeController@treeData |
| GET | `/tree/{person}` | `family-tree.person` | FamilyTreeController@show |
| GET | `/lang/{locale}` | `language.switch` | Closure |

## Controller (FamilyTreeController)

### `index()`
- Gets all people with their parents and child count
- Filters root people (no parents); if none, uses all
- Renders `family-tree.index`

### `show(Person $person)`
- Renders `family-tree.tree` with the person as root

### `treeData(Person $person)`
- Builds recursive JSON tree (max. 10 levels deep)
- Node data: id, name, first_name, last_name, photo, birth_date, death_date, gender, biography, children_count, children[]

### `fullTree()`
- Gets people without parents as roots
- If multiple roots, groups them into a "Families" node

## Filament Admin (PersonResource)
- **Form** (2 columns):
  - Section "Personal Information": first_name, last_name, gender, birth_date, death_date, photo (file upload avatar), biography
  - Section "Family Relationships": parents (select multiple), children (select multiple)
- **Table**: circular photo, first name, last name, gender, birth date, death date, children count
- **Filters**: by gender
- **Actions**: edit, "View Tree" (link to public route)

## Frontend

### Layout (`layouts/app.blade.php`)
- Navbar with logo, links to "View Tree" and "Admin"
- Language selector (en/es/pl) via hover dropdown
- Dark mode button with localStorage persistence
- Dark mode support via `.dark` class on `<html>`

### Main View (`family-tree/index.blade.php`)
- Full family tree rendered with D3.js
- Loads data from `/api/tree/full`
- Zoom, pan, collapse/expand nodes
- Modal with person info on click
- Cards with: circular photo, name, dates, children count
- Duplicated code between index.blade.php and tree.blade.php (same D3 logic)

### Individual View (`family-tree/tree.blade.php`)
- Same D3 tree but from a specific person
- Loads data from `/api/tree/{person}`

### Internationalization
- 3 languages: English (en), Español (es), Polski (pl)
- Files in `lang/{en,es,pl}.json`
- `SetLocale` middleware uses session to persist language
- Route `/lang/{locale}` to switch language

## Useful Commands

```bash
# Full setup (first time)
composer run setup

# Local development (server + queue + logs + vite)
composer run dev

# Run tests
composer run test

# Docker commands
docker-compose up -d --build
docker-compose exec app bash
```

## Technical Notes
- `composer run test` runs `php artisan config:clear` first
- Filament admin at `/admin`
- Nginx port: `8080`
- MySQL port: `3307`
- Language files are in `lang/*.json` (not in `lang/vendor/`)
- `SetLocale` middleware is added to the `web` group in `bootstrap/app.php`
- `FamilyTreeSeeder` creates 3 generations of the García family
