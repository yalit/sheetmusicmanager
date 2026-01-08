# EasyAdmin Feature Demonstrations

Quick reference for which EasyAdmin features are demonstrated and where.

---

## Feature Checklist

- [ ] Specific queries
- [ ] Filter extension
- [ ] **EntityFilter with autocomplete** (NEW in v4.27.3!)
- [ ] Export using filters
- [ ] Action extension
- [ ] Field and form extension
- [ ] **CollectionField rendering** (Enhanced in v4.27.6!)
- [ ] Manipulating files and images
- [ ] Many-to-one relationships (with TomSelect autocomplete)
- [ ] Custom actions
- [ ] Specific roles on entities and/or actions
- [ ] Multi-tenancy
- [ ] JS integration (embedded)
- [ ] JS integration (Stimulus)

---

## Detailed Feature Map

### 1. Specific Queries
**What**: Custom DQL/QueryBuilder queries beyond basic CRUD

**Demonstrations**:
- **"Unused Sheets"**: Sheets never added to any setlist
  ```php
  // Find sheets with no setlist items
  SELECT s FROM Sheet s
  LEFT JOIN s.setlistItems si
  WHERE si.id IS NULL
  ```

- **"Most Used Composer"**: Person whose sheets appear most in setlists
  ```php
  // Group by composer and count setlist appearances
  SELECT p.name, COUNT(si.id) as usage_count
  FROM Person p
  JOIN p.sheetsComposed s
  JOIN s.setlistItems si
  GROUP BY p.id
  ORDER BY usage_count DESC
  ```

- **"Sheets by Difficulty and Genre"**: Complex filtering
  ```php
  // Repository method with multiple conditions
  public function findByDifficultyAndGenre($difficulty, $genre)
  ```

**EasyAdmin Integration**:
- Custom dashboard widgets showing statistics
- Repository methods called from controllers
- Dashboard action showing custom queries

---

### 2. Filter Extension
**What**: Custom filter classes for complex filtering logic

**Demonstrations**:

#### Filter 1: "Has PDF" (Simple)
```php
class HasPdfFilter extends Filter
{
    public function apply($queryBuilder, string $alias, string $property)
    {
        $queryBuilder->andWhere("$alias.pdfFile IS NOT NULL");
    }
}
```

#### Filter 2: "Difficulty + Status Combined" (Complex) - **LIVE CODED**
```php
class DifficultyStatusFilter extends Filter
{
    public function apply($queryBuilder, string $alias, string $property, FilterData $data)
    {
        $queryBuilder
            ->andWhere("$alias.difficulty = :difficulty")
            ->andWhere("$alias.status = :status")
            ->setParameter('difficulty', $data->get('difficulty'))
            ->setParameter('status', $data->get('status'));
    }
}
```

**Usage in CRUD Controller**:
```php
public function configureFilters(Filters $filters): Filters
{
    return $filters
        ->add('title')
        ->add('genre')
        ->add(HasPdfFilter::new('pdfFile'))
        ->add(DifficultyStatusFilter::new('combined'));
}
```

#### Filter 3: EntityFilter with Autocomplete (NEW in v4.27.3!) 🔥
**What**: Filter by entity associations with Ajax-powered autocomplete - perfect for large datasets!

**The Problem**:
Before v4.27.3, EntityFilter would load ALL entities at once. With hundreds of composers, this would freeze the page!

**The Solution**:
```php
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

// In your CRUD controller
public function configureFilters(Filters $filters): Filters
{
    return $filters
        ->add(EntityFilter::new('composer')
            ->autocomplete()  // 🔥 NEW! Loads composers dynamically via Ajax
        )
        ->add(EntityFilter::new('arranger')
            ->autocomplete()
        )
        ->add(EntityFilter::new('organization')
            ->autocomplete()
        );
}
```

**Benefits**:
- ✅ Loads options dynamically as user types
- ✅ Fast even with thousands of entities
- ✅ Same great UX as AssociationField in forms
- ✅ Uses TomSelect under the hood

**Demo Impact**:
- Show filtering 100+ composers without page freeze
- Type to search - instant results
- Modern, professional UX

**Note**: This is different from AssociationField autocomplete:
- **EntityFilter**: Filters data on **list pages**
- **AssociationField**: Selects entities in **forms**
- Both use autocomplete, different contexts!

---

### 2a. CollectionField Rendering (Enhanced in v4.27.6!) 🔥

**What**: Customize how collection items are displayed in lists and forms

**Where This Applies**: SheetReference DTO collection in Sheet entity

**The Enhancement**:
```php
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;

yield CollectionField::new('references')
    ->setEntryType(SheetReferenceType::class)

    // NEW in v4.27.6: Customize how items are displayed
    ->setEntryToStringMethod('getDisplayString')  // Custom display method

    // Control rendering length
    ->setTemplatePath('admin/field/sheet_references.html.twig')

    // For associations, CrudFormType is now automatic default
    ->allowAdd()
    ->allowDelete()
    ->setFormTypeOptions([
        'by_reference' => false,
    ]);
```

**Custom Display Method** (in SheetReference DTO):
```php
class SheetReference
{
    public string $referenceCode = '';
    public string $referenceType = '';

    // NEW: Custom display for collections
    public function getDisplayString(): string
    {
        return sprintf('[%s] %s',
            strtoupper($this->referenceType),
            $this->referenceCode
        );
    }

    // Example output: "[CATALOG] BW-123"
}
```

**In List View**:
Instead of seeing:
```
References: SheetReference, SheetReference, SheetReference
```

You see:
```
References: [CATALOG] BW-123, [PUBLISHER] PUB-456, [INTERNAL] INT-789
```

**Benefits**:
- ✅ Readable collection display without clicking through
- ✅ Customizable per entity needs
- ✅ Automatic CrudFormType for associations
- ✅ Professional presentation

**Demo Impact**:
- Show clean, readable reference codes in sheet lists
- No need to open detail view to see references
- Modern, polished UX

---

### 3. Export Using Filters
**What**: Export data with currently applied filters

**Demonstration**:
- Sheet catalog export to CSV/Excel
- Filters applied in list view carry over to export
- Custom exporter configuration

**Implementation**:
```php
public function configureActions(Actions $actions): Actions
{
    return $actions
        ->add(Crud::PAGE_INDEX, Action::new('export')
            ->linkToCrudAction('export')
            ->setCssClass('btn btn-success'));
}

public function export(Request $request): Response
{
    // Get filtered query builder from list page
    // Apply same filters
    // Export to CSV/Excel
}
```

---

### 4. Action Extension
**What**: Custom actions on entities (batch and single)

**Demonstrations**:

#### Batch Action: "Archive Selected Sheets"
```php
Action::new('batchArchive', 'Archive')
    ->linkToCrudAction('batchArchive')
    ->addCssClass('btn btn-warning')
    ->setIcon('fa fa-archive');
```

#### Batch Action: "Add to Setlist" - **LIVE CODED**
```php
public function addToSetlist(BatchActionDto $batchActionDto): Response
{
    // Show modal with setlist selection
    // Add selected sheets to chosen setlist
    // Return success message
}
```

**Usage**:
- Select multiple sheets
- Click "Add to Setlist" button
- Modal appears with setlist dropdown
- Confirm → sheets added as SetlistItems

---

### 5. Field and Form Extension
**What**: Custom field types and form layouts

**Demonstrations**:

#### Custom Field: SheetReference Collection
```php
// Custom form type for SheetReference DTO collection
class SheetReferenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('reference_code', TextType::class)
            ->add('reference_type', ChoiceType::class, [
                'choices' => [
                    'Catalog' => 'catalog',
                    'Publisher' => 'publisher',
                    'Internal' => 'internal',
                ]
            ]);
    }
}

// In CRUD controller
yield CollectionField::new('references')
    ->setEntryType(SheetReferenceType::class)
    ->setFormTypeOption('allow_add', true)
    ->setFormTypeOption('allow_delete', true)
    ->setFormTypeOption('by_reference', false);
```

#### Custom Form Layout: Conditional Fields
```php
// Show key_signature only if difficulty is "advanced"
yield TextField::new('key_signature')
    ->setFormTypeOption('attr', [
        'data-conditional' => 'difficulty',
        'data-conditional-value' => 'advanced'
    ]);
```

---

### 6. Manipulating Files and Images
**What**: File upload, storage, display, and download

**Demonstrations**:

#### Sheet Entity
- **PDF file**: `pdf_file` (upload, display link, download)
- **Cover image**: `cover_image` (upload, display thumbnail, preview)

#### Organization Entity
- **Logo**: `logo` (upload, display in header)

**Implementation**:
```php
// In CRUD controller
yield ImageField::new('cover_image')
    ->setBasePath('/uploads/covers')
    ->setUploadDir('public/uploads/covers')
    ->setUploadedFileNamePattern('[randomhash].[extension]');

yield Field::new('pdf_file')
    ->setFormType(FileType::class)
    ->setFormTypeOptions([
        'upload_new' => fn (UploadedFile $file) => $this->handlePdfUpload($file),
    ])
    ->onlyOnForms();

yield Field::new('pdf_file')
    ->setTemplatePath('admin/field/pdf_download.html.twig')
    ->onlyOnDetail();
```

**Features Shown**:
- File upload validation (size, type)
- Custom filename generation
- Display logic (thumbnail vs download link)
- Preview functionality

---

### 7. Many-to-One Relationships
**What**: Foreign key relationships displayed and edited in admin

**Demonstrations**:

| Entity | Relationship | Display |
|--------|-------------|---------|
| Sheet | → Composer (Person) | Dropdown in form, link in list |
| Sheet | → Arranger (Person) | Dropdown in form, link in list |
| Sheet | → Organization | Auto-set by multi-tenancy |
| Setlist | → Organization | Auto-set by multi-tenancy |
| SetlistItem | → Sheet | Autocomplete search field |
| SetlistItem | → Setlist | Dropdown (context-aware) |
| Member | → Organization | Dropdown (admin only) |

**EasyAdmin Configuration**:
```php
yield AssociationField::new('composer')
    ->setLabel('Composer')
    ->setCrudController(PersonCrudController::class)
    ->formatValue(fn ($value) => $value?->getName());

yield AssociationField::new('arranger')
    ->setLabel('Arranger')
    ->setCrudController(PersonCrudController::class)
    ->setRequired(false);
```

---

### 8. Custom Actions
**What**: Entity-specific actions beyond CRUD

**Demonstrations**:

#### Action: "Generate Setlist PDF"
```php
Action::new('generatePdf', 'Generate PDF')
    ->linkToCrudAction('generatePdf')
    ->setIcon('fa fa-file-pdf')
    ->displayIf(fn (Setlist $setlist) => $setlist->getStatus() === 'finalized');

public function generatePdf(AdminContext $context): Response
{
    $setlist = $context->getEntity()->getInstance();
    $pdf = $this->pdfGenerator->generate($setlist);
    return new Response($pdf, 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'attachment; filename="setlist.pdf"'
    ]);
}
```

#### Action: "Preview Sheet"
```php
Action::new('preview', 'Preview')
    ->linkToRoute('sheet_preview', fn (Sheet $sheet) => [
        'id' => $sheet->getId()
    ])
    ->setIcon('fa fa-eye')
    ->displayIf(fn (Sheet $sheet) => $sheet->getPdfFile() !== null);
```

#### Action: "Mark as Performed"
```php
Action::new('markPerformed', 'Mark as Performed')
    ->linkToCrudAction('markPerformed')
    ->setIcon('fa fa-check')
    ->displayIf(fn (Setlist $setlist) => $setlist->getStatus() === 'finalized')
    ->setCssClass('btn btn-success');
```

#### Action: "Duplicate Setlist"
```php
Action::new('duplicate', 'Duplicate')
    ->linkToCrudAction('duplicateSetlist')
    ->setIcon('fa fa-copy')
    ->setCssClass('btn btn-info');
```

---

### 9. Specific Roles on Entities and/or Actions
**What**: Role-based access control (RBAC) on CRUD operations and actions

**Role Definitions**:
- `ROLE_ADMIN`: Full access to everything
- `ROLE_CONDUCTOR`: Manage setlists + sheets
- `ROLE_LIBRARIAN`: Manage sheets only
- `ROLE_MEMBER`: Read-only access

**Demonstrations**:

#### Entity-Level Access
```php
// In SheetCrudController
public function configureActions(Actions $actions): Actions
{
    return $actions
        ->setPermission(Action::NEW, 'ROLE_LIBRARIAN')
        ->setPermission(Action::EDIT, 'ROLE_LIBRARIAN')
        ->setPermission(Action::DELETE, 'ROLE_ADMIN')
        ->setPermission(Action::INDEX, 'ROLE_MEMBER'); // Everyone can view
}
```

#### Action-Level Access
```php
// Custom action only for conductors
Action::new('generatePdf')
    ->setPermission('ROLE_CONDUCTOR')
    ->linkToCrudAction('generatePdf');
```

#### Voter-Based Access (Advanced)
```php
// In SetlistVoter
protected function supports(string $attribute, mixed $subject): bool
{
    return $subject instanceof Setlist
        && in_array($attribute, ['EDIT', 'DELETE', 'PERFORM']);
}

protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
{
    $user = $token->getUser();
    $setlist = $subject;

    // ROLE_MEMBER can't edit
    if (!$this->security->isGranted('ROLE_CONDUCTOR')) {
        return false;
    }

    // Can only edit own organization's setlists
    if ($setlist->getOrganization() !== $user->getOrganization()) {
        return false;
    }

    return match($attribute) {
        'EDIT' => $setlist->getStatus() === 'draft',
        'DELETE' => $setlist->getStatus() === 'draft',
        'PERFORM' => $setlist->getStatus() === 'finalized',
    };
}
```

**Demo Flow**:
1. Login as ROLE_MEMBER → see read-only views
2. Login as ROLE_LIBRARIAN → can manage sheets, not setlists
3. Login as ROLE_CONDUCTOR → can manage both
4. Login as ROLE_ADMIN → can do everything

---

### 10. Multi-Tenancy
**What**: Data isolation by Organization

**Implementation Strategy**:

#### Query Extension (Automatic Filtering)
```php
class OrganizationExtension extends QueryCollectionExtensionInterface
{
    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        Operation $operation = null,
        array $context = []
    ): void {
        $user = $this->security->getUser();
        if (!$user instanceof Member) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        // Apply to all organization-scoped entities
        if (in_array($resourceClass, [Sheet::class, Setlist::class, Person::class])) {
            $queryBuilder
                ->andWhere("$rootAlias.organization = :organization")
                ->setParameter('organization', $user->getOrganization());
        }
    }
}
```

#### Entity Listener (Auto-Set Organization)
```php
class OrganizationListener
{
    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        $user = $this->security->getUser();

        if (!$user instanceof Member) {
            return;
        }

        // Auto-set organization on new entities
        if (method_exists($entity, 'setOrganization') && !$entity->getOrganization()) {
            $entity->setOrganization($user->getOrganization());
        }
    }
}
```

**Demo Flow**:
1. Create Organization A and Organization B
2. Create User A (belongs to Org A) and User B (belongs to Org B)
3. Login as User A → create sheets → see only Org A sheets
4. Login as User B → create sheets → see only Org B sheets
5. No data leakage between organizations

**Security Notes**:
- Applied at query level (can't be bypassed)
- Works with all EasyAdmin filters and searches
- Voters enforce access control
- Admin users can switch organizations (if needed)

---

### 11. JS Integration (Embedded)
**What**: Inline JavaScript for simple enhancements

**Demonstration**: Duration Auto-Format in Sheet Form

**Implementation**:
```javascript
// templates/admin/field/duration_field.html.twig
{% block _sheet_duration_field %}
    {{ form_widget(field) }}
    <script>
        (function() {
            const input = document.querySelector('#{{ field.vars.id }}');

            input.addEventListener('blur', function() {
                let value = this.value.trim();

                // Convert "3.5" to "3:30"
                if (value.match(/^\d+\.\d+$/)) {
                    const parts = value.split('.');
                    const minutes = parseInt(parts[0]);
                    const seconds = Math.round(parseFloat('0.' + parts[1]) * 60);
                    this.value = `${minutes}:${seconds.toString().padStart(2, '0')}`;
                }

                // Convert "3" to "3:00"
                if (value.match(/^\d+$/)) {
                    this.value = `${value}:00`;
                }
            });
        })();
    </script>
{% endblock %}
```

**Usage in CRUD**:
```php
yield TextField::new('duration')
    ->setFormTemplate('admin/field/duration_field.html.twig');
```

**Features Shown**:
- Embedded JS in Twig template
- DOM manipulation
- Event listeners
- Input formatting

---

### 12. JS Integration (Stimulus)
**What**: Stimulus controller for complex interactions

**Demonstration**: Drag-and-Drop Setlist Item Reordering

**Stimulus Controller**:
```javascript
// assets/controllers/sortable_controller.js
import { Controller } from '@hotwired/stimulus';
import Sortable from 'sortablejs';

export default class extends Controller {
    static values = {
        url: String
    }

    connect() {
        this.sortable = new Sortable(this.element, {
            animation: 150,
            handle: '.drag-handle',
            onEnd: this.updatePositions.bind(this)
        });
    }

    async updatePositions(event) {
        const items = Array.from(this.element.querySelectorAll('[data-id]'));
        const positions = items.map((item, index) => ({
            id: item.dataset.id,
            position: index + 1
        }));

        try {
            const response = await fetch(this.urlValue, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ positions })
            });

            if (response.ok) {
                this.showSuccess('Order updated!');
            }
        } catch (error) {
            this.showError('Failed to update order');
        }
    }

    showSuccess(message) {
        // Show toast notification
    }

    showError(message) {
        // Show error notification
    }
}
```

**Template Usage**:
```twig
{# templates/admin/setlist_items.html.twig #}
<div data-controller="sortable"
     data-sortable-url-value="{{ path('admin_setlist_reorder', {id: setlist.id}) }}">
    {% for item in setlist.items %}
        <div class="setlist-item" data-id="{{ item.id }}">
            <span class="drag-handle">⋮⋮</span>
            <span class="position">{{ item.position }}</span>
            <span class="name">{{ item.name }}</span>
            <span class="sheet">{{ item.sheet.title }}</span>
        </div>
    {% endfor %}
</div>
```

**Backend Endpoint**:
```php
#[Route('/admin/setlist/{id}/reorder', name: 'admin_setlist_reorder', methods: ['POST'])]
public function reorderItems(Request $request, Setlist $setlist): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    foreach ($data['positions'] as $position) {
        $item = $this->setlistItemRepository->find($position['id']);
        if ($item && $item->getSetlist() === $setlist) {
            $item->setPosition($position['position']);
        }
    }

    $this->entityManager->flush();

    return new JsonResponse(['success' => true]);
}
```

**Features Shown**:
- Stimulus controller setup
- External library integration (SortableJS)
- AJAX requests
- Real-time UI updates
- Backend integration

---

## Implementation Order

### Phase 1: Foundation
1. Set up Symfony project
2. Create all entities with Doctrine
3. Configure Doctrine Extensions (Timestampable, Blameable)
4. Set up EasyAdmin with basic CRUD

### Phase 2: Live Coding Features
5. Custom filter (Difficulty + Status)
6. Custom action (Add to Setlist)

### Phase 3: Advanced Features (Pre-built)
7. Multi-tenancy (Query Extension + Listeners)
8. Role-based access (Voters + Security)
9. Custom field (SheetReference collection)
10. File/image handling
11. Custom actions (Generate PDF, Mark as Performed)
12. JS integration (embedded duration format)
13. JS integration (Stimulus drag-and-drop)
14. Export with filters
15. Specific queries (dashboard widgets)

---

## Testing Checklist

Before the talk, verify each feature:

- [ ] Specific queries return correct results
- [ ] Custom filters work correctly
- [ ] Export includes filtered data
- [ ] Batch actions work on multiple selections
- [ ] Custom field (SheetReference) adds/removes items
- [ ] File uploads work (PDF, images)
- [ ] All many-to-one dropdowns populate correctly
- [ ] Custom actions trigger successfully
- [ ] Role-based access restricts correctly
- [ ] Multi-tenancy isolates data between organizations
- [ ] Embedded JS (duration format) works on blur
- [ ] Stimulus controller (drag-drop) reorders items
- [ ] All features work across different browsers
- [ ] Mobile responsiveness (if relevant)

---

## Fallback Plan

If live coding fails:

1. Have git branches ready: `git checkout step-2-filters`
2. Have screenshots of each feature
3. Have recorded GIFs/videos of interactions
4. Explain the code even if demo doesn't work
5. Repository link for attendees to try later

The key message: "EasyAdmin is extensible and powerful" - code examples prove this even if live demo has issues.
