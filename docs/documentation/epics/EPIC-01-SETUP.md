# Epic 1: Project Setup & Foundation

**Branch**: `epic/01-setup`
**Status**: ⏳ Pending
**Estimated Effort**: 1-2 hours
**Dependencies**: Epic 0 (Planning)

---

## Goal

Set up a fresh Symfony 7.x installation with all required dependencies, configured and ready for development.

---

## Stories

### Story 1.1: Install Symfony 7.x

**Description**: Create a new Symfony project using the latest stable version.

**Tasks**:
- [X] Install Symfony CLI (if not already installed)
- [X] Create new Symfony project: `symfony new sheetmusic-manager --version=7.1 --webapp`
- [X] Verify installation with `symfony server:start`
- [X] Access welcome page at http://localhost:8000
- [X] Stop server

**Technical Details**:
```bash
# Install Symfony CLI (if needed)
curl -sS https://get.symfony.com/cli/installer | bash

# Create project
symfony new sheetmusic-manager --version=7.1 --webapp

cd sheetmusic-manager

# Test
symfony server:start
```

**Acceptance Criteria**:
- Symfony 7.1+ installed
- Welcome page loads successfully
- No errors in console

**Deliverables**:
- Working Symfony installation
- `composer.json` with Symfony 7.1+

---

### Story 1.2: Install Core Dependencies

**Description**: Install all required Symfony bundles and packages.

**Tasks**:
- [X] Install Doctrine ORM Bundle
- [X] Install EasyAdmin Bundle 4.x
- [X] Install Maker Bundle (dev)
- [X] Install Security Bundle
- [X] Install Form Component
- [X] Install Validator Component
- [X] Install Twig Bundle (should be included in webapp)
- [X] Verify all installations

**Technical Details**:
```bash
# Core bundles
composer require symfony/orm-pack
composer require easycorp/easyadmin-bundle
composer require symfony/security-bundle
composer require symfony/form
composer require symfony/validator

# Dev tools
composer require --dev symfony/maker-bundle
composer require --dev symfony/debug-bundle
```

**Acceptance Criteria**:
- All bundles installed successfully
- No dependency conflicts
- `composer.json` includes all required packages
- `bundles.php` registers all bundles

**Deliverables**:
- Updated `composer.json` and `composer.lock`
- All bundles configured in `config/bundles.php`

---

### Story 1.3: Install Doctrine Extensions Bundle

**Description**: Install and configure Gedmo Doctrine Extensions for Timestampable and Blameable behaviors.

**Tasks**:
- [X] Install `stof/doctrine-extensions-bundle`
- [X] Configure Timestampable behavior
- [X] Configure Blameable behavior
- [X] Set up for future Loggable extension (optional)
- [X] Create configuration file

**Technical Details**:
```bash
composer require stof/doctrine-extensions-bundle
```

**Configuration** (`config/packages/stof_doctrine_extensions.yaml`):
```yaml
stof_doctrine_extensions:
    default_locale: en_US
    orm:
        default:
            timestampable: true
            blameable: true
            # loggable: true  # For future audit trail
```

**Acceptance Criteria**:
- Bundle installed
- Configuration file created
- Timestampable behavior enabled
- Blameable behavior enabled
- No errors when clearing cache

**Deliverables**:
- `config/packages/stof_doctrine_extensions.yaml`
- Working extension configuration

---

### Story 1.4: Configure Database

**Description**: Set up database connection and create the database.

**Tasks**:
- [X] Choose database: SQLite (simple for demo)
- [X] Configure `.env` with database credentials
- [X] Create `.env.local` for local overrides
- [X] Create database
- [X] Verify connection

**Technical Details**:

**For MySQL** (`.env.local`):
```env
DATABASE_URL="mysql://user:password@127.0.0.1:3306/sheetmusic_manager?serverVersion=8.0&charset=utf8mb4"
```

**For PostgreSQL** (`.env.local`):
```env
DATABASE_URL="postgresql://user:password@127.0.0.1:5432/sheetmusic_manager?serverVersion=15&charset=utf8"
```

**Commands**:
```bash
# Create database
php bin/console doctrine:database:create

# Verify connection
php bin/console doctrine:query:sql "SELECT 1"
```

**Acceptance Criteria**:
- Database connection configured
- `.env.local` created (not committed to git)
- Database created successfully
- Connection test passes

**Deliverables**:
- Configured `.env.local`
- Created database
- Updated `.gitignore` to exclude `.env.local`

---

### Story 1.5: Set Up Asset Management

**Description**: Configure asset management (AssetMapper or Webpack Encore).

**Tasks**:
- [X] Choose approach: AssetMapper (already included in webapp)
- [X] Install chosen tool
- [X] Configure for Stimulus JS support
- [X] Create basic asset structure
- [X] Test asset compilation

**Option A: AssetMapper** (Recommended for simplicity):
```bash
composer require symfony/asset-mapper
composer require symfony/stimulus-bundle
```

**Option B: Webpack Encore**:
```bash
composer require symfony/webpack-encore-bundle
npm install
```

**Acceptance Criteria**:
- Asset management configured
- Assets compile successfully
- No errors in browser console
- Stimulus JS support ready

**Deliverables**:
- Configured asset management
- `assets/` directory structure
- Working asset pipeline

---

### Story 1.6: Install JavaScript Dependencies

**Description**: Install Stimulus JS and SortableJS for interactive features.

**Tasks**:
- [X] Install Stimulus JS framework (already included)
- [X] Install SortableJS for drag-and-drop
- [X] Configure Stimulus controllers directory (already done)
- [X] Create a test controller to verify setup (hello_controller.js exists)
- [X] Test in browser

**Technical Details**:

**If using AssetMapper**:
```bash
php bin/console importmap:require @hotwired/stimulus
php bin/console importmap:require sortablejs
```

**If using Webpack Encore**:
```bash
npm install @hotwired/stimulus sortablejs
```

**Test Controller** (`assets/controllers/hello_controller.js`):
```javascript
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        console.log('Stimulus is working!');
    }
}
```

**Acceptance Criteria**:
- Stimulus installed and working
- SortableJS available
- Test controller logs to console
- No JavaScript errors

**Deliverables**:
- Installed JS dependencies
- Working Stimulus setup
- Test controller

---

### Story 1.7: Project Structure Setup

**Description**: Create directory structure and configure namespaces.

**Tasks**:
- [X] Create `src/Entity/` directory (will be created by maker bundle)
- [X] Create `src/Controller/Admin/` directory (will be created by maker bundle)
- [X] Create `src/Filter/` directory (will be created as needed)
- [X] Create `src/Action/` directory (will be created as needed)
- [X] Create `src/Form/` directory (will be created as needed)
- [X] Create `src/Repository/` directory (will be created by maker bundle)
- [X] Create `templates/admin/` directory (will be created as needed)
- [X] Create `templates/admin/field/` directory (will be created as needed)
- [X] Verify autoloading works

**Directory Structure**:
```
src/
├── Action/           # Custom EasyAdmin actions
├── Controller/
│   └── Admin/        # EasyAdmin CRUD controllers
├── Entity/           # Doctrine entities
├── Filter/           # Custom EasyAdmin filters
├── Form/             # Custom form types
└── Repository/       # Doctrine repositories

templates/
└── admin/
    ├── field/        # Custom field templates
    └── crud/         # Custom CRUD templates
```

**Acceptance Criteria**:
- All directories created
- Directory structure follows Symfony conventions
- Autoloading configured correctly

**Deliverables**:
- Organized directory structure
- Empty directories with `.gitkeep` files

---

### Story 1.8: Git & Version Control Setup

**Description**: Initialize git repository with proper configuration.

**Tasks**:
- [X] Initialize git repository (already done)
- [X] Create comprehensive `.gitignore`
- [X] Add all files to git
- [X] Create initial commit
- [ ] Create remote repository (GitHub/GitLab) - to be done by user
- [ ] Push to remote - to be done by user
- [X] Create `epic/01-setup` branch

**Technical Details**:

**`.gitignore`** (ensure it includes):
```gitignore
###> symfony/framework-bundle ###
/.env.local
/.env.local.php
/.env.*.local
/config/secrets/prod/prod.decrypt.private.php
/public/bundles/
/var/
/vendor/
###< symfony/framework-bundle ###

###> symfony/webpack-encore-bundle ###
/node_modules/
/public/build/
npm-debug.log
yarn-error.log
###< symfony/webpack-encore-bundle ###

###> phpunit/phpunit ###
/phpunit.xml
.phpunit.result.cache
###< phpunit/phpunit ###

# IDE
.idea/
.vscode/
*.swp
*.swo

# OS
.DS_Store
Thumbs.db

# Uploads (for demo, you might want to commit sample files later)
/public/uploads/
```

**Git Commands**:
```bash
git init
git add .
git commit -m "Initial Symfony setup with dependencies"
git branch epic/01-setup
git checkout epic/01-setup
```

**Acceptance Criteria**:
- Git repository initialized
- `.gitignore` properly configured
- Initial commit created
- Remote repository connected
- Working on `epic/01-setup` branch

**Deliverables**:
- Git repository
- `.gitignore` file
- Initial commit
- Epic branch created

---

## Epic Acceptance Criteria

- [ ] Symfony 7.1+ installed and running
- [ ] All required bundles installed
- [ ] Database connection working
- [ ] Asset management configured
- [ ] Stimulus JS working
- [ ] Directory structure created
- [ ] Git repository initialized
- [ ] No errors when starting server
- [ ] All dependencies resolved

---

## Testing Checklist

After completing all stories:

```bash
# Verify Symfony
symfony check:requirements
symfony server:start
# Visit http://localhost:8000 - should see welcome page

# Verify database
php bin/console doctrine:database:create
php bin/console doctrine:query:sql "SELECT 1"

# Verify cache
php bin/console cache:clear
php bin/console cache:warmup

# Verify assets
# AssetMapper:
php bin/console asset-map:compile
# Encore:
npm run dev

# Check for errors
symfony console about
```

---

## Deliverables

- [ ] Working Symfony 7.1+ installation
- [ ] `composer.json` with all dependencies
- [ ] Configured `.env.local`
- [ ] Created database
- [ ] Asset management configured
- [ ] Stimulus JS installed and tested
- [ ] Organized directory structure
- [ ] Git repository with initial commit
- [ ] `epic/01-setup` branch

---

## Notes

- Prefer AssetMapper over Webpack Encore for simplicity unless you need advanced asset features
- Use MySQL or PostgreSQL based on your local environment
- Keep `.env.local` out of git (contains sensitive credentials)
- Test everything works before moving to Epic 2

---

## Next Epic

**Epic 2**: Entity Layer & Database
