# Sheet Music Manager

Demo application for an advanced usage for EasyAdmin 5

A sheet music library manager for choirs, bands, and orchestras — realistic enough to illustrate non-trivial EasyAdmin patterns without being a toy example.

---

## Stack

- **Symfony 7** — framework
- **EasyAdmin 5** — admin panel
- **Doctrine ORM** — SQLite
- **importmap** — asset management (no Webpack/Node)
- **SortableJS** — drag-and-drop reorder (plain JS, no Stimulus)
- **Gotenberg** — PDF generation (Docker service)
- **Taskfile** — task runner (`task` CLI)
- **Makefile** — alternative task runner (no extra tooling required)

---

## Quick start

```bash
git clone <repo-url>
cd sheetmusic-manager
```

### With Taskfile

```bash
task install   # migrate + load fixtures + copy test PDFs to public
task up        # start Symfony server + Docker (Gotenberg)
```

### With Make

```bash
make install   # migrate + load fixtures + copy test PDFs to public
make up        # start Symfony server + Docker (Gotenberg)
```

### Manually

```bash
symfony console doctrine:migrations:migrate --no-interaction
symfony console doctrine:fixtures:load --no-interaction
cp tests/public/uploads/sheets/* public/uploads/sheets/
symfony serve -d
docker compose up -d
```

Open `http://localhost:8000` — you will be redirected to the login page.

> **Gotenberg** (PDF features) requires a running Gotenberg instance. For local dev without Docker, PDF export actions will fail gracefully. Everything else works without it.

---

## Login credentials

| Role | Email | Password | Can do |
|---|---|---|---|
| Member | member@sheetmusic.test | password | Read everything, create setlists |
| Contributor | contributor@sheetmusic.test | password | + Edit sheets and own setlists |
| Librarian | librarian@sheetmusic.test | password | + Create/delete sheets, persons |
| Admin | admin@sheetmusic.test | password | + Manage members |

---

## Entities

| Entity | Description |
|---|---|
| `Sheet` | A piece of sheet music with PDF file, refs (BWV, K, Op…), tags, and credits |
| `Person` | A composer or arranger |
| `PersonType` | Composer / Arranger / Conductor |
| `CreditedPerson` | Links a Person + PersonType to a Sheet |
| `Setlist` | An ordered list of sheets for a performance |
| `SetListItem` | One entry in a setlist (sheet + position + name) |
| `Member` | An application user with a role |

---

## Features demonstrated

- Basic CRUD for all entities via EasyAdmin 4
- Role-based access control (4 roles, Symfony voters)
- Custom filters — `HasPdfFilter` (boolean), date range, text search
- Custom actions — duplicate setlist, generate PDF, merge sheet PDFs, batch add to setlist
- `CollectionTableField` — custom form type for inline editing of related entities (sheet credits, setlist items)
- Drag-and-drop reorder of setlist items (SortableJS + position sync on save)
- CSV export respecting active search/filter state
- Blameable entities (`createdBy` / `updatedBy` auto-set from logged-in user)

---

## Branch structure

Each `epic/XX` branch is a self-contained snapshot. Branches build on each other and are all merged into `main`.

| Branch | What it adds |
|---|---|
| `epic/01-setup` | Symfony skeleton, dependencies |
| `epic/02-entities` | All entities, migrations, basic fixtures |
| `epic/03-easyadmin` | EasyAdmin dashboard, all CRUD controllers |
| `epic/04-authentication` | Login, roles, voters, security |
| `epic/07-filters` | Custom filters (`HasPdfFilter`, tag filter) |
| `epic/08-actions` | Custom actions (duplicate, PDF, merge, batch add) |
| `epic/09-custom-fields` | `CollectionTableField` custom form type |
| `epic/10-dnd-reorder` | Drag-and-drop reorder on setlist items |
| `epic/11-advanced` | CSV export with filter awareness, extended search |
| `epic/12-demo-data` | Enriched fixtures (14 sheets, 14 persons, setlist items) |
| `main` | Everything |

To explore a specific branch:

### With Taskfile

```bash
task checkout   # shows an interactive branch selector
```

### With Make

```bash
make checkout BRANCH=epic/07-filters
```

### Manually

```bash
git stash
git checkout epic/07-filters
composer install --quiet
symfony console doctrine:migrations:migrate --no-interaction
symfony console doctrine:fixtures:load --no-interaction
cp tests/public/uploads/sheets/* public/uploads/sheets/
```

---

## Running tests

### With Taskfile

```bash
task test-init   # create test DB, run migrations and fixtures
task tests       # run the test suite
```

### With Make

```bash
make test-init
make tests
```

### Manually

```bash
php bin/phpunit
```

Tests use a separate SQLite test database. No external services required.
