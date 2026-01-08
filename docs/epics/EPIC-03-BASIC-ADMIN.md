# Epic 3: Basic EasyAdmin CRUD

**Branch**: `epic/03-basic-admin`
**Status**: ⏳ Pending
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
- [ ] Generate dashboard: `php bin/console make:admin:dashboard`
- [ ] Configure dashboard title and menu
- [ ] Set default page
- [ ] Add menu sections
- [ ] Configure dashboard layout

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
- Dashboard controller created
- Menu configured with all entities
- Menu organized into logical sections
- Dashboard accessible at `/admin`

**Deliverables**:
- `src/Controller/Admin/DashboardController.php`
- `templates/admin/dashboard.html.twig`

---

### Story 3.2: Create Organization CRUD Controller

**Description**: Generate CRUD controller for Organization entity.

**Tasks**:
- [ ] Generate CRUD: `php bin/console make:admin:crud`
- [ ] Configure fields for list view
- [ ] Configure fields for form view
- [ ] Configure fields for detail view
- [ ] Add search fields

**Command**:
```bash
php bin/console make:admin:crud
# Choose: Organization
# Generate in: src/Controller/Admin
```

**Configuration** (`src/Controller/Admin/OrganizationCrudController.php`):
```php
<?php

namespace App\Controller\Admin;

use App\Entity\Organization;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;

class OrganizationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Organization::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Organization')
            ->setEntityLabelInPlural('Organizations')
            ->setSearchFields(['name', 'type'])
            ->setDefaultSort(['name' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();
        yield TextField::new('name');
        yield TextField::new('type');
        yield ImageField::new('logo')
            ->setBasePath('uploads/logos')
            ->setUploadDir('public/uploads/logos')
            ->setUploadedFileNamePattern('[randomhash].[extension]')
            ->hideOnIndex();
        yield DateTimeField::new('createdAt')->hideOnForm();
        yield DateTimeField::new('updatedAt')->hideOnForm();
    }
}
```

**Acceptance Criteria**:
- CRUD controller created
- All fields configured
- Search enabled on name and type
- Logo upload configured
- Timestamps displayed in list view

**Deliverables**:
- `src/Controller/Admin/OrganizationCrudController.php`

---

### Story 3.3: Create Person CRUD Controller

**Description**: Generate CRUD controller for Person entity (composers/arrangers).

**Tasks**:
- [ ] Generate CRUD controller
- [ ] Configure fields
- [ ] Add association field for Organization
- [ ] Add filter by type
- [ ] Configure search

**Key Configuration**:
```php
public function configureFields(string $pageName): iterable
{
    yield IdField::new('id')->onlyOnIndex();
    yield TextField::new('name');
    yield ChoiceField::new('type')
        ->setChoices([
            'Composer' => 'composer',
            'Arranger' => 'arranger',
            'Both' => 'both',
        ]);
    yield AssociationField::new('organization');
    yield DateTimeField::new('createdAt')->hideOnForm();
    yield DateTimeField::new('updatedAt')->hideOnForm();
}

public function configureFilters(Filters $filters): Filters
{
    return $filters
        ->add('type')
        ->add('organization');
}
```

**Acceptance Criteria**:
- CRUD controller created
- Type field as choice dropdown
- Organization association field
- Filters configured
- Search enabled

**Deliverables**:
- `src/Controller/Admin/PersonCrudController.php`

---

### Story 3.4: Create Sheet CRUD Controller

**Description**: Generate CRUD controller for Sheet entity.

**Tasks**:
- [ ] Generate CRUD controller
- [ ] Configure all text fields
- [ ] Configure choice fields (difficulty, status)
- [ ] Configure association fields (composer, arranger, organization)
- [ ] Configure file uploads (PDF, cover image)
- [ ] Configure JSON field (references) - basic display
- [ ] Add filters and search

**Key Configuration**:
```php
public function configureFields(string $pageName): iterable
{
    yield IdField::new('id')->onlyOnIndex();
    yield TextField::new('title');
    yield TextField::new('genre')->hideOnIndex();
    yield ChoiceField::new('difficulty')
        ->setChoices([
            'Beginner' => 'beginner',
            'Intermediate' => 'intermediate',
            'Advanced' => 'advanced',
        ]);
    yield TextField::new('duration')->hideOnIndex();
    yield TextField::new('keySignature')->hideOnIndex();
    yield ChoiceField::new('status')
        ->setChoices([
            'Active' => 'active',
            'Archived' => 'archived',
        ]);
    yield TextareaField::new('notes')->hideOnIndex();

    // Associations
    yield AssociationField::new('composer');
    yield AssociationField::new('arranger');
    yield AssociationField::new('organization')->hideOnForm();

    // Files
    yield ImageField::new('coverImage')
        ->setBasePath('uploads/covers')
        ->setUploadDir('public/uploads/covers')
        ->hideOnIndex();
    yield Field::new('pdfFile')
        ->setFormType(FileType::class)
        ->hideOnIndex();

    // JSON field - basic display for now
    yield ArrayField::new('references')->hideOnIndex();

    // Timestamps
    yield DateTimeField::new('createdAt')->hideOnForm();
    yield DateTimeField::new('updatedAt')->hideOnForm();
}
```

**Acceptance Criteria**:
- CRUD controller created
- All fields configured
- File uploads working
- Associations working
- Filters and search configured

**Deliverables**:
- `src/Controller/Admin/SheetCrudController.php`

---

### Story 3.5: Create Setlist CRUD Controller

**Description**: Generate CRUD controller for Setlist entity.

**Tasks**:
- [ ] Generate CRUD controller
- [ ] Configure fields
- [ ] Configure date field for event_date
- [ ] Configure status choice field
- [ ] Add association to organization
- [ ] Configure filters and search

**Key Configuration**:
```php
public function configureFields(string $pageName): iterable
{
    yield IdField::new('id')->onlyOnIndex();
    yield TextField::new('name');
    yield DateField::new('eventDate');
    yield TextField::new('occasion')->hideOnIndex();
    yield ChoiceField::new('status')
        ->setChoices([
            'Draft' => 'draft',
            'Finalized' => 'finalized',
            'Performed' => 'performed',
        ]);
    yield TextareaField::new('notes')->hideOnIndex();
    yield AssociationField::new('organization')->hideOnForm();
    yield DateTimeField::new('createdAt')->hideOnForm();
    yield DateTimeField::new('updatedAt')->hideOnForm();
}
```

**Acceptance Criteria**:
- CRUD controller created
- All fields configured
- Status choice field working
- Date field configured

**Deliverables**:
- `src/Controller/Admin/SetlistCrudController.php`

---

### Story 3.6: Create SetlistItem CRUD Controller

**Description**: Generate CRUD controller for SetlistItem entity.

**Tasks**:
- [ ] Generate CRUD controller
- [ ] Configure fields
- [ ] Configure associations to Setlist and Sheet
- [ ] Configure position field
- [ ] Set default sort by position
- [ ] Configure search

**Key Configuration**:
```php
public function configureCrud(Crud $crud): Crud
{
    return $crud
        ->setEntityLabelInSingular('Setlist Item')
        ->setEntityLabelInPlural('Setlist Items')
        ->setDefaultSort(['position' => 'ASC']);
}

public function configureFields(string $pageName): iterable
{
    yield IdField::new('id')->onlyOnIndex();
    yield AssociationField::new('setlist');
    yield AssociationField::new('sheet');
    yield IntegerField::new('position');
    yield TextField::new('name');
    yield TextareaField::new('notes')->hideOnIndex();
    yield DateTimeField::new('createdAt')->hideOnForm();
    yield DateTimeField::new('updatedAt')->hideOnForm();
}
```

**Acceptance Criteria**:
- CRUD controller created
- Associations configured
- Sorted by position by default
- All fields working

**Deliverables**:
- `src/Controller/Admin/SetlistItemCrudController.php`

---

### Story 3.7: Create Member CRUD Controller

**Description**: Generate CRUD controller for Member entity (User).

**Tasks**:
- [ ] Generate CRUD controller
- [ ] Configure fields
- [ ] Configure password hashing
- [ ] Configure roles as array choice field
- [ ] Add association to organization
- [ ] Hide password on list/detail views

**Key Configuration**:
```php
public function configureFields(string $pageName): iterable
{
    yield IdField::new('id')->onlyOnIndex();
    yield TextField::new('name');
    yield EmailField::new('email');
    yield TextField::new('password')
        ->setFormType(PasswordType::class)
        ->onlyOnForms();
    yield ChoiceField::new('roles')
        ->setChoices([
            'Member' => 'ROLE_MEMBER',
            'Librarian' => 'ROLE_LIBRARIAN',
            'Conductor' => 'ROLE_CONDUCTOR',
            'Admin' => 'ROLE_ADMIN',
        ])
        ->allowMultipleChoices()
        ->renderExpanded();
    yield AssociationField::new('organization');
    yield DateTimeField::new('createdAt')->hideOnForm();
    yield DateTimeField::new('updatedAt')->hideOnForm();
}
```

**Password Hashing** (add to controller):
```php
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

public function __construct(
    private UserPasswordHasherInterface $passwordHasher
) {}

public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
{
    if ($entityInstance instanceof Member && $entityInstance->getPassword()) {
        $entityInstance->setPassword(
            $this->passwordHasher->hashPassword($entityInstance, $entityInstance->getPassword())
        );
    }
    parent::persistEntity($entityManager, $entityInstance);
}

public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
{
    if ($entityInstance instanceof Member && $entityInstance->getPassword()) {
        $entityInstance->setPassword(
            $this->passwordHasher->hashPassword($entityInstance, $entityInstance->getPassword())
        );
    }
    parent::updateEntity($entityManager, $entityInstance);
}
```

**Acceptance Criteria**:
- CRUD controller created
- Password hashed on save
- Roles configured as multi-choice
- Email field configured

**Deliverables**:
- `src/Controller/Admin/MemberCrudController.php`

---

### Story 3.8: Configure Menu & Navigation

**Description**: Enhance menu organization and add icons.

**Tasks**:
- [ ] Organize menu into logical sections
- [ ] Add Font Awesome icons to menu items
- [ ] Set menu badges (optional - counts)
- [ ] Configure menu item visibility based on user roles (for later)

**Enhanced Menu** (already in Story 3.1, ensure it's polished):
```php
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
```

**Acceptance Criteria**:
- Menu organized into sections
- Icons added to all menu items
- Menu items navigate correctly

**Deliverables**:
- Enhanced DashboardController

---

### Story 3.9: Basic Styling & UX Polish

**Description**: Configure EasyAdmin theme and polish user experience.

**Tasks**:
- [ ] Set page titles
- [ ] Configure breadcrumbs
- [ ] Set entity labels (singular/plural)
- [ ] Configure default page sizes
- [ ] Test all CRUD operations

**Global Configuration** (in DashboardController):
```php
public function configureDashboard(): Dashboard
{
    return Dashboard::new()
        ->setTitle('Sheet Music Manager')
        ->setFaviconPath('favicon.ico')
        ->renderContentMaximized()
        ->generateRelativeUrls();
}
```

**Per-Entity Configuration** (in each CRUD controller):
```php
public function configureCrud(Crud $crud): Crud
{
    return $crud
        ->setEntityLabelInSingular('Sheet')
        ->setEntityLabelInPlural('Sheets')
        ->setPageTitle('index', 'Sheet Music Library')
        ->setPageTitle('new', 'Add New Sheet')
        ->setPageTitle('edit', 'Edit %entity_label_singular%')
        ->setPageTitle('detail', '%entity_label_singular% Details')
        ->setPaginatorPageSize(30);
}
```

**Acceptance Criteria**:
- Page titles configured
- Entity labels set
- Default pagination configured
- User experience is smooth

**Deliverables**:
- Polished CRUD controllers
- Enhanced dashboard

---

## Epic Acceptance Criteria

- [ ] Dashboard controller created and configured
- [ ] All 7 CRUD controllers created
- [ ] Menu organized with icons
- [ ] All entities can be created, read, updated, deleted
- [ ] File uploads working (basic)
- [ ] Associations working in forms (dropdowns)
- [ ] Search and filters working
- [ ] Passwords hashed for members
- [ ] Clean, professional UI
- [ ] No console errors
- [ ] Ready for demo/talk

---

## Testing Checklist

Manual testing before tagging `step-1-base`:

```bash
# Start server
symfony server:start

# Visit /admin
# Test each CRUD:
- [ ] Create Organization
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

- [ ] `src/Controller/Admin/DashboardController.php`
- [ ] `src/Controller/Admin/OrganizationCrudController.php`
- [ ] `src/Controller/Admin/PersonCrudController.php`
- [ ] `src/Controller/Admin/SheetCrudController.php`
- [ ] `src/Controller/Admin/SetlistCrudController.php`
- [ ] `src/Controller/Admin/SetlistItemCrudController.php`
- [ ] `src/Controller/Admin/MemberCrudController.php`
- [ ] `templates/admin/dashboard.html.twig`
- [ ] Working CRUD operations for all entities
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
