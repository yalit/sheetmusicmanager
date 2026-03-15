# Epic 9: Custom Fields & Form Extensions

**Branch**: N/A — skipped
**Status**: ⏭️ Skipped
**Dependencies**: Epic 3 (Basic Admin)

---

## Status Note

Skipped. The features described in this epic (custom collection fields, form panels, value object display) were implemented as part of earlier epics and are already in production. The entity model in this doc (difficulty, keySignature, genre, SheetReference DTO, VichUploader, etc.) does not match the actual codebase and is obsolete.

## Original Goal (obsolete)

Implement custom field types and form extensions to handle complex data structures like the SheetReference DTO collection and conditional form fields.

---

## Stories

### Story 9.1: Create SheetReference Form Type

**Description**: Create custom form type for the SheetReference DTO.

**Tasks**:
- [ ] Create SheetReferenceType form class
- [ ] Add reference_code field
- [ ] Add reference_type choice field
- [ ] Configure form options
- [ ] Test form rendering

**Technical Details**:

**SheetReference Form Type** (`src/Form/SheetReferenceType.php`):
```php
<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SheetReferenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reference_code', TextType::class, [
                'label' => 'Reference Code',
                'attr' => [
                    'placeholder' => 'e.g., BW-123, CAT-456',
                ],
                'required' => true,
            ])
            ->add('reference_type', ChoiceType::class, [
                'label' => 'Reference Type',
                'choices' => [
                    'Catalog Number' => 'catalog',
                    'Publisher Code' => 'publisher',
                    'Internal Code' => 'internal',
                    'ISBN' => 'isbn',
                    'Other' => 'other',
                ],
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null, // DTO, not an entity
        ]);
    }
}
```

**Acceptance Criteria**:
- Form type created
- Fields configured correctly
- Choice field has appropriate options
- Form renders properly

**Deliverables**:
- `src/Form/SheetReferenceType.php`

---

### Story 9.2: Implement SheetReference Collection Field (Enhanced in v4.27.6!) 🔥

**Description**: Add collection field to Sheet CRUD for managing multiple references, using the latest CollectionField rendering enhancements.

**NEW in v4.27.6**:
- `setEntryToStringMethod()` - customize how collection items are displayed
- Automatic CrudFormType for associations
- Better control over collection rendering

**Tasks**:
- [ ] Update Sheet entity to store references as array
- [ ] Update SheetReference DTO with getDisplayString() method
- [ ] Add CollectionField to Sheet CRUD with new features
- [ ] Configure allow_add and allow_delete
- [ ] Use setEntryToStringMethod() for better display
- [ ] Test adding/removing references
- [ ] Display references in list/detail views

**Technical Details**:

**Sheet Entity References** (`src/Entity/Sheet.php`):
```php
#[ORM\Column(type: 'json', nullable: true)]
private ?array $references = [];

public function getReferences(): array
{
    return $this->references ?? [];
}

public function setReferences(?array $references): self
{
    $this->references = $references;
    return $this;
}
```

**SheetReference DTO with Display Method** (`src/DTO/SheetReference.php`):
```php
<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class SheetReference
{
    #[Assert\NotBlank]
    public string $referenceCode = '';

    #[Assert\NotBlank]
    public string $referenceType = '';

    // NEW in v4.27.6: Custom display method for collections! 🔥
    public function getDisplayString(): string
    {
        return sprintf('[%s] %s',
            strtoupper($this->referenceType),
            $this->referenceCode
        );
    }

    // Also works with __toString for backward compatibility
    public function __toString(): string
    {
        return $this->getDisplayString();
    }

    // Serialization methods...
    public function toArray(): array
    {
        return [
            'reference_code' => $this->referenceCode,
            'reference_type' => $this->referenceType,
        ];
    }

    public static function fromArray(array $data): self
    {
        $ref = new self();
        $ref->referenceCode = $data['reference_code'] ?? '';
        $ref->referenceType = $data['reference_type'] ?? '';
        return $ref;
    }
}
```

**Sheet CRUD Controller** (`src/Controller/Admin/SheetCrudController.php`):
```php
use App\Form\SheetReferenceType;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;

public function configureFields(string $pageName): iterable
{
    // ... other fields

    // References collection with NEW v4.27.6 features! 🔥
    yield CollectionField::new('references')
        ->setEntryType(SheetReferenceType::class)

        // NEW: Customize how items are displayed in lists!
        ->setEntryToStringMethod('getDisplayString')  // Shows "[CATALOG] BW-123"

        ->setFormTypeOptions([
            'entry_options' => [
                'label' => false,
            ],
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'delete_empty' => true,
        ])
        ->setLabel('Reference Codes')
        ->setHelp('Add multiple reference codes (catalog numbers, publisher codes, etc.)')
        ->hideOnIndex();

    // Display references in index view (now readable!)
    yield Field::new('references')
        ->formatValue(function ($value, $entity) {
            if (!$value || !is_array($value)) {
                return '<span class="text-muted">No references</span>';
            }

            $badges = array_map(function($ref) {
                $refObj = is_array($ref)
                    ? \App\DTO\SheetReference::fromArray($ref)
                    : $ref;
                return sprintf(
                    '<span class="badge bg-info me-1">%s</span>',
                    htmlspecialchars($refObj->getDisplayString())
                );
            }, $value);

            return implode(' ', $badges);
        })
        ->onlyOnIndex();

    // Display references in detail view
    yield Field::new('references')
        ->setTemplatePath('admin/field/references_display.html.twig')
        ->onlyOnDetail();

    // ... other fields
}
```

**Before v4.27.6** (what you would see):
```
References: SheetReference, SheetReference, SheetReference
```

**After v4.27.6** (with setEntryToStringMethod):
```
References: [CATALOG] BW-123 [PUBLISHER] PUB-456 [INTERNAL] INT-789
```

**References Display Template** (`templates/admin/field/references_display.html.twig`):
```twig
{% if value and value is iterable %}
    <div class="references-list">
        {% for ref in value %}
            <div class="badge bg-info me-2 mb-2">
                <strong>{{ ref.reference_type|title }}:</strong> {{ ref.reference_code }}
            </div>
        {% else %}
            <span class="text-muted">No references</span>
        {% endfor %}
    </div>
{% else %}
    <span class="text-muted">No references</span>
{% endif %}
```

**Acceptance Criteria**:
- Collection field works in form
- Can add multiple references
- Can remove references
- References saved as JSON
- Display template shows references nicely

**Deliverables**:
- Updated Sheet entity
- Updated SheetCrudController
- References display template

---

### Story 9.3: Create Custom Field Template for Difficulty

**Description**: Create visual representation for difficulty field with color coding.

**Tasks**:
- [ ] Create custom difficulty field template
- [ ] Add visual indicators (stars or badges)
- [ ] Color code by difficulty level
- [ ] Apply to list and detail views

**Technical Details**:

**Difficulty Display Template** (`templates/admin/field/difficulty.html.twig`):
```twig
{% set difficulty = value|lower %}

{% if difficulty == 'beginner' %}
    <span class="badge bg-success">
        <i class="fa fa-star"></i> Beginner
    </span>
{% elseif difficulty == 'intermediate' %}
    <span class="badge bg-warning">
        <i class="fa fa-star"></i><i class="fa fa-star"></i> Intermediate
    </span>
{% elseif difficulty == 'advanced' %}
    <span class="badge bg-danger">
        <i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i> Advanced
    </span>
{% else %}
    <span class="badge bg-secondary">Not Set</span>
{% endif %}
```

**Sheet CRUD Controller Update**:
```php
yield ChoiceField::new('difficulty')
    ->setChoices([
        'Beginner' => 'beginner',
        'Intermediate' => 'intermediate',
        'Advanced' => 'advanced',
    ])
    ->setTemplatePath('admin/field/difficulty.html.twig')
    ->hideOnForm();

yield ChoiceField::new('difficulty')
    ->setChoices([
        'Beginner' => 'beginner',
        'Intermediate' => 'intermediate',
        'Advanced' => 'advanced',
    ])
    ->onlyOnForms();
```

**Acceptance Criteria**:
- Difficulty displays with visual indicators
- Color coded appropriately
- Shows stars or similar icons
- Looks professional

**Deliverables**:
- `templates/admin/field/difficulty.html.twig`
- Updated field configuration

---

### Story 9.4: Customize Sheet Form Layout

**Description**: Organize Sheet form into logical sections with conditional fields.

**Tasks**:
- [ ] Group fields into sections (panels)
- [ ] Add conditional field display
- [ ] Add help text and placeholders
- [ ] Improve form UX

**Technical Details**:

**Sheet CRUD Controller**:
```php
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;

public function configureFields(string $pageName): iterable
{
    // Basic Information Panel
    yield FormField::addPanel('Basic Information')->setIcon('fa fa-info-circle');
    yield TextField::new('title')
        ->setColumns(8)
        ->setHelp('The title of the musical piece');
    yield ChoiceField::new('status')
        ->setChoices([
            'Active' => 'active',
            'Archived' => 'archived',
        ])
        ->setColumns(4);

    // Musical Details Panel
    yield FormField::addPanel('Musical Details')->setIcon('fa fa-music');
    yield TextField::new('genre')
        ->setColumns(6)
        ->setHelp('e.g., Classical, Jazz, Pop, Sacred');
    yield ChoiceField::new('difficulty')
        ->setChoices([
            'Beginner' => 'beginner',
            'Intermediate' => 'intermediate',
            'Advanced' => 'advanced',
        ])
        ->setColumns(6);

    yield TextField::new('duration')
        ->setColumns(6)
        ->setHelp('Duration in minutes:seconds (e.g., 3:45)')
        ->setFormTypeOption('attr', [
            'placeholder' => 'e.g., 3:45 or 3.5',
        ]);

    yield TextField::new('keySignature')
        ->setLabel('Key Signature')
        ->setColumns(6)
        ->setHelp('e.g., C Major, G minor')
        ->setFormTypeOption('attr', [
            'placeholder' => 'e.g., C Major',
        ]);

    // People Panel
    yield FormField::addPanel('Composer & Arranger')->setIcon('fa fa-user-tie');
    yield AssociationField::new('composer')
        ->setColumns(6)
        ->setHelp('Select the composer');
    yield AssociationField::new('arranger')
        ->setColumns(6)
        ->setHelp('Select the arranger (if applicable)');

    // References Panel
    yield FormField::addPanel('Reference Codes')->setIcon('fa fa-barcode');
    yield CollectionField::new('references')
        ->setEntryType(SheetReferenceType::class)
        ->setFormTypeOptions([
            'entry_options' => ['label' => false],
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
        ])
        ->setHelp('Add catalog numbers, publisher codes, etc.');

    // Files Panel
    yield FormField::addPanel('Files')->setIcon('fa fa-file');
    yield Field::new('pdfFile')
        ->setFormType(VichFileType::class)
        ->onlyOnForms();
    yield Field::new('coverImageFile')
        ->setFormType(VichFileType::class)
        ->onlyOnForms();

    // Notes Panel
    yield FormField::addPanel('Additional Notes')->setIcon('fa fa-sticky-note');
    yield TextareaField::new('notes')
        ->setHelp('Any additional notes or performance instructions');

    // Display-only fields
    yield DateTimeField::new('createdAt')->hideOnForm();
    yield DateTimeField::new('updatedAt')->hideOnForm();
}
```

**Conditional Field (Advanced)** - JavaScript solution in Story 10:
```php
yield TextField::new('keySignature')
    ->setFormTypeOption('attr', [
        'data-conditional' => 'difficulty',
        'data-conditional-value' => 'advanced',
    ]);
```

**Acceptance Criteria**:
- Form organized into clear sections
- Help text provided for fields
- Placeholders guide user input
- Form is intuitive and professional
- Conditional fields work (if implemented)

**Deliverables**:
- Enhanced form layout
- Organized field configuration

---

### Story 9.5: Customize Setlist Form Layout

**Description**: Improve Setlist form organization and UX.

**Tasks**:
- [ ] Add form panels for sections
- [ ] Configure date picker for event_date
- [ ] Add visual status indicators
- [ ] Add help text

**Technical Details**:

**Setlist CRUD Controller**:
```php
public function configureFields(string $pageName): iterable
{
    // Basic Information
    yield FormField::addPanel('Setlist Information')->setIcon('fa fa-list');
    yield TextField::new('name')
        ->setColumns(12)
        ->setHelp('Give this setlist a descriptive name');

    yield TextField::new('occasion')
        ->setColumns(8)
        ->setHelp('e.g., "Christmas Concert 2025", "Easter Sunday Mass"');

    yield DateField::new('eventDate')
        ->setLabel('Event Date')
        ->setColumns(4)
        ->setHelp('When will this setlist be performed?');

    // Status
    yield FormField::addPanel('Status')->setIcon('fa fa-check');
    yield ChoiceField::new('status')
        ->setChoices([
            'Draft' => 'draft',
            'Finalized' => 'finalized',
            'Performed' => 'performed',
        ])
        ->setHelp('Draft: Still being edited | Finalized: Ready for performance | Performed: Already performed')
        ->renderExpanded()
        ->renderAsBadges([
            'draft' => 'warning',
            'finalized' => 'success',
            'performed' => 'info',
        ]);

    // Notes
    yield FormField::addPanel('Additional Information')->setIcon('fa fa-sticky-note');
    yield TextareaField::new('notes')
        ->setHelp('Any notes about this performance or setlist');

    // Timestamps
    yield DateTimeField::new('createdAt')->hideOnForm();
    yield DateTimeField::new('updatedAt')->hideOnForm();
}
```

**Acceptance Criteria**:
- Form organized logically
- Date picker works well
- Status selection clear and visual
- Help text guides users
- Professional appearance

**Deliverables**:
- Enhanced Setlist form

---

## Epic Acceptance Criteria

- [ ] SheetReferenceType form created
- [ ] Collection field working for references
- [ ] References display nicely in views
- [ ] Difficulty field has visual indicators
- [ ] Sheet form organized into panels
- [ ] Setlist form organized into panels
- [ ] Help text provided throughout
- [ ] Forms are intuitive and professional
- [ ] All form customizations tested

---

## Testing Checklist

```bash
# SheetReference Collection
- [ ] Can add new reference
- [ ] Can remove reference
- [ ] Multiple references save correctly
- [ ] References display in detail view

# Difficulty Display
- [ ] Shows correct color for beginner (green)
- [ ] Shows correct color for intermediate (yellow)
- [ ] Shows correct color for advanced (red)
- [ ] Displays stars or indicators

# Form Layouts
- [ ] Sheet form has clear panels
- [ ] Setlist form has clear panels
- [ ] Help text displays correctly
- [ ] Placeholders guide input
- [ ] Forms submit successfully
- [ ] Validation works
```

---

## Deliverables

- [ ] `src/Form/SheetReferenceType.php`
- [ ] Updated Sheet entity with references
- [ ] `templates/admin/field/references_display.html.twig`
- [ ] `templates/admin/field/difficulty.html.twig`
- [ ] Enhanced Sheet form configuration
- [ ] Enhanced Setlist form configuration
- [ ] All custom field templates
- [ ] Working form extensions

---

## Notes

- Custom form types enable handling complex data structures
- CollectionField is powerful for one-to-many embedded forms
- Form panels improve UX significantly
- Visual field templates make data more digestible
- Help text and placeholders are crucial for user experience

---

## Next Epic

**Epic 10**: JavaScript Integration
