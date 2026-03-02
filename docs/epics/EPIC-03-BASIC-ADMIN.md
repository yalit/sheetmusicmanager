# Epic 3: Basic EasyAdmin CRUD

**Branch**: `epic/03-basic-admin`
**Status**: 🔄 In Progress
**Estimated Effort**: 2-3 hours
**Dependencies**: Epic 2 (Entity Layer)

**Git Tag After Completion**: `step-1-base` ⭐ **TALK STARTS HERE**

---

## Goal

Set up EasyAdmin with basic CRUD controllers for all entities. This creates the starting point for the talk demonstration.

---

## Stories

### Story 3.1: Create Dashboard Controller

**Description**: Generate and configure the main EasyAdmin dashboard.

**Tasks**:
- [x] Generate dashboard: `php bin/console make:admin:dashboard`
- [x] Configure dashboard title and menu
- [x] Set default page
- [x] Add menu sections
- [x] Configure dashboard layout

**Command**:
```bash
php bin/console make:admin:dashboard
# Choose: DashboardController
# Namespace: App\Controller\Admin
```

**Dashboard Configuration** (`src/Controller/Admin/DashboardController.php`):
```php
<?php

namespace App\Controller\Admin;

use App\Entity\Organization;
use App\Entity\Person;
use App\Entity\Sheet;
use App\Entity\Setlist;
use App\Entity\SetlistItem;
use App\Entity\Member;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Sheet Music Manager')
            ->setFaviconPath('favicon.ico');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::section('Music Library');
        yield MenuItem::linkToCrud('Sheets', 'fa fa-music', Sheet::class);
        yield MenuItem::linkToCrud('Composers & Arrangers', 'fa fa-user-tie', Person::class);

        yield MenuItem::section('Performances');
        yield MenuItem::linkToCrud('Setlists', 'fa fa-list', Setlist::class);
        yield MenuItem::linkToCrud('Setlist Items', 'fa fa-bars', SetlistItem::class);

        yield MenuItem::section('Administration');
        yield MenuItem::linkToCrud('Organizations', 'fa fa-building', Organization::class);
        yield MenuItem::linkToCrud('Members', 'fa fa-users', Member::class);
    }
}
```

**Acceptance Criteria**:
- [x] Dashboard controller created
- [x] Menu configured with all entities (adapted: no Organization entity exists)
- [x] Menu organized into logical sections
- [x] Dashboard accessible at `/admin`

**Deliverables**:
- [x] `src/Controller/Admin/DashboardController.php`
- [ ] `templates/admin/dashboard.html.twig` ← redirects to Sheet index instead

---

### Story 3.2: Create Organization CRUD Controller

> ⚠️ **N/A** — `Organization` entity was not created in the entity layer (Epic 2 deviated from plan). Skip until entity exists.

**Acceptance Criteria**:
- [ ] CRUD controller created
- [ ] All fields configured
- [ ] Search enabled on name and type
- [ ] Logo upload configured
- [ ] Timestamps displayed in list view

**Deliverables**:
- [ ] `src/Controller/Admin/OrganizationCrudController.php`

---

### Story 3.3: Create Person CRUD Controller

**Description**: Generate CRUD controller for Person entity (composers/arrangers).

> ℹ️ `type` is not a property of Person — a person's role depends on context (sheet credit). Handled via `PersonType` entity on `CreditedPerson` instead. `Organization` entity was dropped.

**Tasks**:
- [x] Generate CRUD controller
- [x] Configure fields (name)
- [x] Person type handled via PersonType entity on CreditedPerson (see Story 3.4)
- [ ] Configure filters
- [ ] Configure search

**Acceptance Criteria**:
- [x] CRUD controller created
- [x] Person type as configurable entity (`PersonTypeCrudController` added to menu)
- [ ] Filters configured
- [ ] Search enabled

**Deliverables**:
- [x] `src/Controller/Admin/PersonCrudController.php`
- [x] `src/Controller/Admin/PersonTypeCrudController.php`

---

### Story 3.4: Create Sheet CRUD Controller

**Description**: Generate CRUD controller for Sheet entity.

> ℹ️ Entity deviated from plan: no `genre`, `difficulty`, `status`, `composer`, `arranger` fields. Has `tags`, `refs`, `files` (multi-PDF) instead. Controller adapted accordingly.

**Tasks**:
- [x] Generate CRUD controller
- [x] Configure all text fields
- [ ] Configure choice fields (difficulty, status) ← not on entity
- [x] Configure credits (Person + PersonType) as inline table collection
- [x] Configure file uploads (PDF via custom PDFField)
- [x] Configure refs as custom ChoiceAutoCompleteStringField
- [ ] Add filters and search

**Acceptance Criteria**:
- [x] CRUD controller created
- [x] Fields configured (adapted to actual entity)
- [x] File uploads working (custom multi-PDF field)
- [x] Credits (Person + PersonType) as inline table via CollectionTableField
- [ ] Filters and search configured

**Deliverables**:
- [x] `src/Controller/Admin/SheetCrudController.php`
- [x] `src/Controller/Admin/CreditedPersonCrudController.php`

---

### Story 3.5: Create Setlist CRUD Controller

**Description**: Generate CRUD controller for Setlist entity.

**Tasks**:
- [x] Generate CRUD controller
- [x] Configure fields
- [x] Configure date field
- [ ] Configure status choice field ← no `status` field on entity
- [ ] Add association to organization ← no Organization entity
- [ ] Configure filters and search

**Acceptance Criteria**:
- [x] CRUD controller created
- [x] Fields configured (title, date)
- [x] Inline SetlistItem collection as table (enhancement over plan)
- [ ] Status choice field ← not on entity

**Deliverables**:
- [x] `src/Controller/Admin/SetlistCrudController.php`

---

### Story 3.6: Create SetlistItem CRUD Controller

> ℹ️ **Implemented differently**: not exposed as a standalone menu entry. Used exclusively as the entry form config for the inline table collection in SetlistCrudController via `useEntryCrudForm()`.

**Acceptance Criteria**:
- [x] Controller created (technical, not in menu)
- [x] Associations configured (sheet)
- [x] Position auto-assigned server-side from row order

**Deliverables**:
- [x] `src/Controller/Admin/SetlistItemCrudController.php`

---

### Story 3.7: Create Member CRUD Controller

**Description**: Generate CRUD controller for Member entity (User).

**Tasks**:
- [x] Generate CRUD controller
- [x] Configure fields
- [x] Configure password field (plainPassword, handled by entity)
- [ ] Configure roles as array choice field ← not implemented
- [ ] Add association to organization ← no Organization entity
- [x] Hide password on list/detail views

**Acceptance Criteria**:
- [x] CRUD controller created
- [ ] Password hashed on save ← uses plainPassword on entity (verify hashing is wired)
- [ ] Roles configured as multi-choice ← not implemented
- [x] Email field configured

**Deliverables**:
- [x] `src/Controller/Admin/MemberCrudController.php`

---

### Story 3.8: Configure Menu & Navigation

**Tasks**:
- [x] Organize menu into logical sections
- [x] Add Font Awesome icons to menu items
- [ ] Set menu badges (optional - counts)
- [ ] Configure menu item visibility based on user roles (for later)

**Acceptance Criteria**:
- [x] Menu organized into sections (Administration, Partitions, Performances)
- [x] Icons added to all menu items
- [x] Menu items navigate correctly

**Deliverables**:
- [x] Enhanced DashboardController

---

### Story 3.9: Basic Styling & UX Polish

**Tasks**:
- [ ] Set page titles
- [ ] Configure breadcrumbs
- [x] Set entity labels (singular/plural) ← done on Setlist only
- [ ] Configure default page sizes
- [ ] Test all CRUD operations

**Acceptance Criteria**:
- [ ] Page titles configured
- [x] `renderContentMaximized()` enabled globally
- [x] Form themes configured
- [ ] Default pagination configured

**Deliverables**:
- [ ] Polished CRUD controllers
- [ ] Enhanced dashboard

---

## Epic Acceptance Criteria

- [x] Dashboard controller created and configured
- [x] All relevant CRUD controllers created (Organization N/A — entity dropped)
- [x] Menu organized with icons
- [x] All entities can be created, read, updated, deleted (Organization N/A)
- [x] File uploads working (PDF)
- [x] Credits (Person + PersonType) working as inline table on Sheet
- [ ] Search and filters working ← not configured
- [ ] Passwords hashed for members ← verify
- [x] Clean, professional UI
- [ ] No console errors ← untested
- [ ] Ready for demo/talk

---

## Testing Checklist

Manual testing before tagging `step-1-base`:

```bash
# Start server
symfony server:start

# Visit /admin
# Test each CRUD:
- [ ] Create Organization  ← N/A (no entity)
- [ ] Create Person (composer)
- [ ] Create Person (arranger)
- [ ] Create Member (test user)
- [ ] Create Sheet (with composer, arranger)
- [ ] Create Setlist
- [ ] Create SetlistItem (linking setlist to sheet)

# Verify:
- [ ] All forms work
- [ ] Associations show in dropdowns
- [ ] Files upload successfully
- [ ] Search works
- [ ] Filters work
- [ ] Edit/delete work
- [ ] Timestamps appear
- [ ] No errors in console
```

---

## Deliverables

- [x] `src/Controller/Admin/DashboardController.php`
- [ ] `src/Controller/Admin/OrganizationCrudController.php` ← N/A (no entity)
- [x] `src/Controller/Admin/PersonCrudController.php`
- [x] `src/Controller/Admin/PersonTypeCrudController.php`
- [x] `src/Controller/Admin/SheetCrudController.php`
- [x] `src/Controller/Admin/CreditedPersonCrudController.php`
- [x] `src/Controller/Admin/SetlistCrudController.php`
- [x] `src/Controller/Admin/SetlistItemCrudController.php`
- [x] `src/Controller/Admin/MemberCrudController.php`
- [ ] `templates/admin/dashboard.html.twig`
- [ ] Working CRUD operations for all entities ← partially tested
- [ ] `public/uploads/` directories created

---

## Git Tag

After completing and testing this epic:

```bash
git add .
git commit -m "Epic 3: Basic EasyAdmin CRUD complete"
git tag -a step-1-base -m "Talk starting point: Basic CRUD operational"
git push origin epic/03-basic-admin --tags
```

**This tag marks the starting point for your talk!** ⭐

---

## Next Epic

**Epic 4**: Authentication & Security Layer
- OR -
**Epic 7**: Custom Filters (if jumping to live-coding features first)
