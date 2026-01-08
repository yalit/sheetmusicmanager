# Epic 7: Custom Filters (LIVE CODING)

**Branch**: `epic/07-filters`
**Status**: ⏳ Pending
**Estimated Effort**: 2-3 hours (build) + 2-3 hours (rehearsal)
**Dependencies**: Epic 3 (Basic Admin)

**Git Tag After Completion**: `step-2-custom-filters` 🔴 **LIVE CODING SAFETY NET**

---

## Goal

Implement custom filter extensions for EasyAdmin. This epic includes both pre-built filters and a filter that will be live-coded during the talk.

---

## Stories

### Story 7.1: Create "Has PDF" Filter (Pre-built)

**Description**: Create a simple custom filter to show only sheets with uploaded PDF files.

**Tasks**:
- [ ] Create HasPdfFilter class
- [ ] Implement apply method
- [ ] Register filter in Sheet CRUD controller
- [ ] Test filter functionality
- [ ] Verify it works before talk

**Technical Details**:

**Has PDF Filter** (`src/Filter/HasPdfFilter.php`):
```php
<?php

namespace App\Filter;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;

class HasPdfFilter implements FilterInterface
{
    use FilterTrait;

    public static function new(string $propertyName, $label = 'Has PDF'): self
    {
        return (new self())
            ->setFilterFqcn(__CLASS__)
            ->setProperty($propertyName)
            ->setLabel($label);
    }

    public function apply(
        QueryBuilder $queryBuilder,
        FilterDataDto $filterDataDto,
        ?FieldDto $fieldDto,
        EntityDto $entityDto
    ): void {
        $alias = $queryBuilder->getRootAliases()[0];

        if ($filterDataDto->getValue() === true) {
            $queryBuilder->andWhere("$alias.pdfFileName IS NOT NULL");
        } elseif ($filterDataDto->getValue() === false) {
            $queryBuilder->andWhere("$alias.pdfFileName IS NULL");
        }
    }
}
```

**Sheet CRUD Controller Update** (`src/Controller/Admin/SheetCrudController.php`):
```php
use App\Filter\HasPdfFilter;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;

public function configureFilters(Filters $filters): Filters
{
    return $filters
        ->add('title')
        ->add('genre')
        ->add('difficulty')
        ->add('status')
        ->add(HasPdfFilter::new('pdfFileName', 'Has PDF File'))
        ->add('composer')
        ->add('arranger');
}
```

**Acceptance Criteria**:
- Filter created and working
- Shows only sheets with PDF when selected
- Easy to understand implementation
- Can be used as reference during live coding

**Deliverables**:
- `src/Filter/HasPdfFilter.php`
- Updated SheetCrudController

---

### Story 7.2: Add EntityFilter with Autocomplete (NEW in v4.27.3!) 🔥

**Description**: Implement EntityFilter with autocomplete for composer and arranger - showcasing the latest EasyAdmin feature (November 2024).

**Why This Matters**:
- Demonstrates cutting-edge EasyAdmin features
- Solves real-world performance problems
- Shows modern Ajax/autocomplete integration
- Perfect for audiences with large datasets

**Tasks**:
- [ ] Add EntityFilter with autocomplete for composer
- [ ] Add EntityFilter with autocomplete for arranger
- [ ] Add EntityFilter with autocomplete for organization (optional)
- [ ] Test with realistic data (50+ composers)
- [ ] Compare with old non-autocomplete behavior
- [ ] Document the difference in talk notes

**Technical Details**:

**In SheetCrudController** (`src/Controller/Admin/SheetCrudController.php`):
```php
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

public function configureFilters(Filters $filters): Filters
{
    return $filters
        // Text filters
        ->add('title')
        ->add('genre')

        // Custom filter
        ->add(HasPdfFilter::new('pdfFileName', 'Has PDF File'))

        // EntityFilter with autocomplete - NEW in v4.27.3! 🔥
        ->add(EntityFilter::new('composer')
            ->setLabel('Composer')
            ->autocomplete()  // Ajax-powered autocomplete!
        )
        ->add(EntityFilter::new('arranger')
            ->setLabel('Arranger')
            ->autocomplete()  // Loads dynamically as you type
        )

        // Standard filters
        ->add('difficulty')
        ->add('status');
}
```

**The Difference**:

**Before v4.27.3** (without autocomplete):
```php
// This would load ALL composers at once
->add(EntityFilter::new('composer'))
// With 500 composers = slow page load, memory issues
```

**After v4.27.3** (with autocomplete):
```php
// Loads composers dynamically via Ajax
->add(EntityFilter::new('composer')->autocomplete())
// Fast even with 10,000+ composers!
```

**Demo Flow for Talk**:
1. Show filter dropdown with autocomplete
2. Type "Bach" - shows only matching composers
3. Select composer - list filters instantly
4. Mention: "This is new in EasyAdmin v4.27.3 - no more loading all entities!"
5. Show it also works on forms (AssociationField uses TomSelect)

**Talk Points**:
- "This feature was just added in November 2024"
- "Community requested it because EntityFilter was unusable with large datasets"
- "Uses same TomSelect library as forms for consistency"
- "Perfect example of EasyAdmin's active development"

**Comparison with AssociationField**:
| Feature | EntityFilter | AssociationField |
|---------|-------------|------------------|
| **Location** | Filter panel on list pages | Forms (create/edit) |
| **Purpose** | Filter/search data | Select related entity |
| **Autocomplete** | `->autocomplete()` (v4.27.3+) | Built-in (TomSelect) |
| **Use Case** | "Show me sheets by Bach" | "This sheet's composer is Bach" |

**Acceptance Criteria**:
- EntityFilter with autocomplete works for composer
- EntityFilter with autocomplete works for arranger
- Typing in filter shows dynamic results
- Performance is good with 50+ entities
- Clear visual indication of autocomplete behavior

**Deliverables**:
- Updated SheetCrudController with EntityFilter autocomplete
- Demo data with 50+ composers to showcase feature
- Talk notes explaining the feature

---

### Story 7.3: Prepare Template for Difficulty + Status Filter (Live Coding)

**Description**: Create a template/skeleton file for the filter that will be live-coded during the talk.

**Tasks**:
- [ ] Create empty filter class with comments
- [ ] Prepare boilerplate code
- [ ] Document step-by-step implementation
- [ ] Create cheat sheet for live coding
- [ ] Rehearse implementation 5+ times

**Technical Details**:

**Filter Template** (`src/Filter/DifficultyStatusFilter.TEMPLATE.php`):
```php
<?php

namespace App\Filter;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * TEMPLATE for Live Coding
 *
 * This filter allows filtering sheets by both difficulty and status combined.
 *
 * Steps to implement:
 * 1. Create the filter with FilterTrait
 * 2. Implement the static new() method
 * 3. Implement the apply() method with query builder logic
 * 4. Add form type configuration for the choice fields
 */
class DifficultyStatusFilter implements FilterInterface
{
    use FilterTrait;

    // TODO: Implement static new() method
    // This creates a new instance of the filter with configuration

    // TODO: Implement apply() method
    // This is where the actual filtering logic goes
    // - Get the filter values from $filterDataDto
    // - Build the WHERE clauses
    // - Apply to the $queryBuilder
}
```

**Live Coding Cheat Sheet** (`docs/LIVE_CODING_FILTER.md`):
```markdown
# Live Coding: Custom Filter

## Time Target: 3-4 minutes

## Steps:

### 1. Create the class (30 seconds)
- Create file: src/Filter/DifficultyStatusFilter.php
- Add namespace and use statements
- Implement FilterInterface
- Add FilterTrait

### 2. Static new() method (30 seconds)
```php
public static function new(string $propertyName, $label = null): self
{
    return (new self())
        ->setFilterFqcn(__CLASS__)
        ->setProperty($propertyName)
        ->setLabel($label ?? 'Difficulty & Status');
}
```

### 3. Apply method (2 minutes)
```php
public function apply(
    QueryBuilder $queryBuilder,
    FilterDataDto $filterDataDto,
    ?FieldDto $fieldDto,
    EntityDto $entityDto
): void {
    $alias = $queryBuilder->getRootAliases()[0];
    $value = $filterDataDto->getValue();

    if (isset($value['difficulty'])) {
        $queryBuilder
            ->andWhere("$alias.difficulty = :difficulty")
            ->setParameter('difficulty', $value['difficulty']);
    }

    if (isset($value['status'])) {
        $queryBuilder
            ->andWhere("$alias.status = :status")
            ->setParameter('status', $value['status']);
    }
}
```

### 4. Add to controller (30 seconds)
```php
->add(DifficultyStatusFilter::new('combined'))
```

### 5. Test (30 seconds)
- Refresh admin
- See filter appear
- Select difficulty + status
- See filtered results

## Talking Points:
- "Custom filters extend EasyAdmin's filtering capabilities"
- "We implement FilterInterface and use FilterTrait"
- "The apply method is where the magic happens"
- "QueryBuilder gives us full Doctrine query power"
- "This pattern works for any complex filtering logic"

## Backup Plan:
If something fails:
```bash
git stash
git checkout step-2-custom-filters
php bin/console cache:clear
```
```

**Acceptance Criteria**:
- Template file created with helpful comments
- Cheat sheet documents every step
- Implementation rehearsed multiple times
- Talking points prepared
- Backup plan ready

**Deliverables**:
- Filter template file
- Live coding cheat sheet
- Rehearsal notes

---

### Story 7.4: Implement Difficulty + Status Filter (Complete Version)

**Description**: Create the complete, working version of the filter for the safety net branch.

**Tasks**:
- [ ] Implement complete DifficultyStatusFilter
- [ ] Add form field configuration
- [ ] Test with all combinations
- [ ] Ensure it matches live coding version
- [ ] Commit to step-2-custom-filters branch

**Technical Details**:

**Complete Filter** (`src/Filter/DifficultyStatusFilter.php`):
```php
<?php

namespace App\Filter;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class DifficultyStatusFilter implements FilterInterface
{
    use FilterTrait;

    public static function new(string $propertyName, $label = null): self
    {
        return (new self())
            ->setFilterFqcn(__CLASS__)
            ->setProperty($propertyName)
            ->setLabel($label ?? 'Difficulty & Status');
    }

    public function apply(
        QueryBuilder $queryBuilder,
        FilterDataDto $filterDataDto,
        ?FieldDto $fieldDto,
        EntityDto $entityDto
    ): void {
        $alias = $queryBuilder->getRootAliases()[0];
        $value = $filterDataDto->getValue();

        if (empty($value)) {
            return;
        }

        if (isset($value['difficulty']) && $value['difficulty'] !== '') {
            $queryBuilder
                ->andWhere("$alias.difficulty = :difficulty")
                ->setParameter('difficulty', $value['difficulty']);
        }

        if (isset($value['status']) && $value['status'] !== '') {
            $queryBuilder
                ->andWhere("$alias.status = :status")
                ->setParameter('status', $value['status']);
        }
    }

    public function buildForm(FormBuilderInterface $builder): void
    {
        $builder
            ->add('difficulty', ChoiceType::class, [
                'choices' => [
                    'All' => '',
                    'Beginner' => 'beginner',
                    'Intermediate' => 'intermediate',
                    'Advanced' => 'advanced',
                ],
                'required' => false,
                'placeholder' => false,
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'All' => '',
                    'Active' => 'active',
                    'Archived' => 'archived',
                ],
                'required' => false,
                'placeholder' => false,
            ]);
    }
}
```

**Sheet CRUD Controller**:
```php
use App\Filter\DifficultyStatusFilter;

public function configureFilters(Filters $filters): Filters
{
    return $filters
        ->add('title')
        ->add('genre')
        ->add(DifficultyStatusFilter::new('combined', 'Difficulty & Status'))
        ->add(HasPdfFilter::new('pdfFileName', 'Has PDF File'))
        ->add('composer')
        ->add('arranger');
}
```

**Acceptance Criteria**:
- Complete filter implementation
- Form fields configured with choices
- Works with all combinations:
  - Difficulty only
  - Status only
  - Both together
- Matches what would be live-coded
- Ready as safety net

**Deliverables**:
- Complete `src/Filter/DifficultyStatusFilter.php`
- Updated SheetCrudController
- Tested and working

---

### Story 7.5: Create Additional Custom Filters (Optional)

**Description**: Create more custom filters for demonstration variety.

**Tasks**:
- [ ] Create "Genre + Key Signature" filter
- [ ] Create "Composer + Date Range" filter
- [ ] Create "Has Cover Image" filter
- [ ] Test all filters together

**Technical Details**:

**Has Cover Image Filter** (`src/Filter/HasCoverImageFilter.php`):
```php
<?php

namespace App\Filter;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;

class HasCoverImageFilter implements FilterInterface
{
    use FilterTrait;

    public static function new(string $propertyName, $label = 'Has Cover Image'): self
    {
        return (new self())
            ->setFilterFqcn(__CLASS__)
            ->setProperty($propertyName)
            ->setLabel($label);
    }

    public function apply(
        QueryBuilder $queryBuilder,
        FilterDataDto $filterDataDto,
        ?FieldDto $fieldDto,
        EntityDto $entityDto
    ): void {
        $alias = $queryBuilder->getRootAliases()[0];

        if ($filterDataDto->getValue() === true) {
            $queryBuilder->andWhere("$alias.coverImageName IS NOT NULL");
        } elseif ($filterDataDto->getValue() === false) {
            $queryBuilder->andWhere("$alias.coverImageName IS NULL");
        }
    }
}
```

**Genre and Key Filter** (`src/Filter/GenreKeyFilter.php`):
```php
<?php

namespace App\Filter;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class GenreKeyFilter implements FilterInterface
{
    use FilterTrait;

    public static function new(string $propertyName, $label = null): self
    {
        return (new self())
            ->setFilterFqcn(__CLASS__)
            ->setProperty($propertyName)
            ->setLabel($label ?? 'Genre & Key');
    }

    public function apply(
        QueryBuilder $queryBuilder,
        FilterDataDto $filterDataDto,
        ?FieldDto $fieldDto,
        EntityDto $entityDto
    ): void {
        $alias = $queryBuilder->getRootAliases()[0];
        $value = $filterDataDto->getValue();

        if (empty($value)) {
            return;
        }

        if (isset($value['genre']) && $value['genre'] !== '') {
            $queryBuilder
                ->andWhere("$alias.genre LIKE :genre")
                ->setParameter('genre', '%' . $value['genre'] . '%');
        }

        if (isset($value['key']) && $value['key'] !== '') {
            $queryBuilder
                ->andWhere("$alias.keySignature LIKE :key")
                ->setParameter('key', '%' . $value['key'] . '%');
        }
    }

    public function buildForm(FormBuilderInterface $builder): void
    {
        $builder
            ->add('genre', TextType::class, [
                'required' => false,
                'attr' => ['placeholder' => 'e.g. Jazz, Classical'],
            ])
            ->add('key', TextType::class, [
                'required' => false,
                'attr' => ['placeholder' => 'e.g. C Major, G minor'],
            ]);
    }
}
```

**Acceptance Criteria**:
- Additional filters created
- All filters work independently
- Multiple filters can be applied together
- Provide variety for demonstration

**Deliverables**:
- Additional custom filters
- Updated CRUD controller

---

### Story 7.6: Document Filter Pattern and Best Practices

**Description**: Create documentation explaining the custom filter pattern.

**Tasks**:
- [ ] Document FilterInterface requirements
- [ ] Explain FilterTrait usage
- [ ] Document QueryBuilder patterns
- [ ] Provide examples of different filter types
- [ ] Create troubleshooting guide

**Technical Details**:

**Documentation** (`docs/CUSTOM_FILTERS.md`):
```markdown
# Custom Filters in EasyAdmin

## Overview

Custom filters extend EasyAdmin's filtering capabilities beyond the built-in filters.

## Implementation Pattern

### 1. Create Filter Class

```php
namespace App\Filter;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;

class MyCustomFilter implements FilterInterface
{
    use FilterTrait;
}
```

### 2. Implement Required Methods

#### static new()
Creates a new filter instance with configuration:
```php
public static function new(string $propertyName, $label = null): self
{
    return (new self())
        ->setFilterFqcn(__CLASS__)
        ->setProperty($propertyName)
        ->setLabel($label ?? 'My Filter');
}
```

#### apply()
Contains the filtering logic:
```php
public function apply(
    QueryBuilder $queryBuilder,
    FilterDataDto $filterDataDto,
    ?FieldDto $fieldDto,
    EntityDto $entityDto
): void {
    $alias = $queryBuilder->getRootAliases()[0];
    $value = $filterDataDto->getValue();

    // Add your WHERE clauses
    $queryBuilder->andWhere("$alias.field = :value")
        ->setParameter('value', $value);
}
```

### 3. Add to CRUD Controller

```php
public function configureFilters(Filters $filters): Filters
{
    return $filters->add(MyCustomFilter::new('propertyName'));
}
```

## Common Patterns

### Boolean Filter (Has/Has Not)
```php
if ($filterDataDto->getValue() === true) {
    $queryBuilder->andWhere("$alias.field IS NOT NULL");
}
```

### Choice Filter
Implement `buildForm()` method:
```php
public function buildForm(FormBuilderInterface $builder): void
{
    $builder->add('field', ChoiceType::class, [
        'choices' => ['Option 1' => 'value1'],
    ]);
}
```

### Date Range Filter
```php
if (isset($value['start'])) {
    $queryBuilder->andWhere("$alias.date >= :start")
        ->setParameter('start', $value['start']);
}
if (isset($value['end'])) {
    $queryBuilder->andWhere("$alias.date <= :end")
        ->setParameter('end', $value['end']);
}
```

## Best Practices

1. **Always check for empty values** before applying filters
2. **Use named parameters** to prevent SQL injection
3. **Use andWhere()** instead of where() to combine filters
4. **Test with multiple filters** applied simultaneously
5. **Provide clear labels** for user experience

## Troubleshooting

**Filter doesn't appear:**
- Check if filter is added in `configureFilters()`
- Clear cache: `php bin/console cache:clear`

**Filter doesn't work:**
- Check property name matches entity property
- Verify QueryBuilder alias is correct
- Check for empty value handling

**Multiple filters conflict:**
- Use andWhere() instead of where()
- Use unique parameter names
```

**Acceptance Criteria**:
- Comprehensive documentation created
- Pattern explained clearly
- Examples provided
- Best practices documented
- Troubleshooting guide included

**Deliverables**:
- `docs/CUSTOM_FILTERS.md`

---

## Epic Acceptance Criteria

- [ ] "Has PDF" filter created and working
- [ ] "Difficulty + Status" filter template prepared
- [ ] "Difficulty + Status" filter complete version ready
- [ ] Additional filters created (optional)
- [ ] Filter pattern documented
- [ ] Live coding rehearsed 5+ times
- [ ] Timing under 4 minutes for live coding
- [ ] Cheat sheet ready for reference
- [ ] Safety net branch (`step-2-custom-filters`) tested
- [ ] All filters work independently and together

---

## Live Coding Rehearsal Checklist

Practice until you can complete in under 4 minutes:

```bash
# Rehearsal 1: [ ] Time: ____ mins
# Rehearsal 2: [ ] Time: ____ mins
# Rehearsal 3: [ ] Time: ____ mins
# Rehearsal 4: [ ] Time: ____ mins
# Rehearsal 5: [ ] Time: ____ mins

# Key metrics:
- [ ] Can type filter class from memory
- [ ] Can explain while typing
- [ ] Can recover from typos quickly
- [ ] Can test filter successfully
- [ ] Stay under 4 minutes consistently
```

---

## Testing Checklist

```bash
# Test Has PDF Filter
- [ ] Filter shows in Sheet list
- [ ] Filter shows only sheets with PDF
- [ ] Filter shows only sheets without PDF
- [ ] Works with other filters

# Test Difficulty + Status Filter
- [ ] Filter shows both dropdowns
- [ ] Can filter by difficulty only
- [ ] Can filter by status only
- [ ] Can filter by both together
- [ ] "All" option works correctly
- [ ] Combines with other filters

# Test Additional Filters
- [ ] All optional filters work
- [ ] Multiple filters can be applied
- [ ] Filters can be removed individually
- [ ] Results update correctly
```

---

## Deliverables

- [ ] `src/Filter/HasPdfFilter.php`
- [ ] `src/Filter/DifficultyStatusFilter.TEMPLATE.php`
- [ ] `src/Filter/DifficultyStatusFilter.php` (complete)
- [ ] `src/Filter/HasCoverImageFilter.php` (optional)
- [ ] `src/Filter/GenreKeyFilter.php` (optional)
- [ ] `docs/LIVE_CODING_FILTER.md`
- [ ] `docs/CUSTOM_FILTERS.md`
- [ ] Updated SheetCrudController
- [ ] Git tag: `step-2-custom-filters`

---

## Git Tagging

After completing and testing:

```bash
git add .
git commit -m "Epic 7: Custom filters complete (live coding safety net)"
git tag -a step-2-custom-filters -m "After live coding #1: Custom filters implemented"
git push origin epic/07-filters --tags
```

---

## Notes

- This epic is crucial for the talk - rehearse extensively
- The filter is simple enough to live code but impressive enough to demonstrate power
- Having a safety net branch ensures talk can continue smoothly
- Practice switching to safety net branch quickly if needed
- Keep talking while coding to maintain audience engagement

---

## Next Epic

**Epic 8**: Custom Actions (LIVE CODING)
