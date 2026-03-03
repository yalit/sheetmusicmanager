# Epic 7: Custom Filters (LIVE CODING)

**Branch**: `epic/07-filters`
**Status**: ⏳ Pending
**Dependencies**: Epic 3 (Basic Admin)

**Git Tag After Completion**: `step-2-custom-filters` 🔴 **LIVE CODING SAFETY NET**

---

## Goal

Implement custom filter extensions for EasyAdmin, working with the actual `Sheet` entity fields:
`title` (string), `tags` (SIMPLE_ARRAY), `refs` (SIMPLE_ARRAY), `files` (SIMPLE_ARRAY), `notes`,
`credit` (OneToMany → CreditedPerson → Person).

The epic includes one pre-built filter (`HasPdfFilter`) and one filter that will be live-coded
during the talk (`TagFilter`).

---

## Stories

### Story 7.1: Create "Has PDF" Filter (Pre-built) ⏳

**Description**: Boolean filter — show only sheets that have at least one uploaded PDF, or none.

`Sheet.files` is a `SIMPLE_ARRAY` column. Doctrine stores an empty array as `NULL` in the DB,
so the check is straightforward: `IS NOT NULL AND != ''`.

**Filter** (`src/Filter/HasPdfFilter.php`):
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

    public static function new(string $propertyName, string $label = 'Has PDF'): self
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
            $queryBuilder->andWhere("$alias.files IS NOT NULL AND $alias.files != ''");
        } elseif ($filterDataDto->getValue() === false) {
            $queryBuilder->andWhere("$alias.files IS NULL OR $alias.files = ''");
        }
    }
}
```

**SheetCrudController** — update `configureFilters()`:
```php
use App\Filter\HasPdfFilter;

public function configureFilters(Filters $filters): Filters
{
    return $filters
        ->add(TextFilter::new('title'))
        ->add(HasPdfFilter::new('files', 'Has PDF'));
}
```

**Acceptance Criteria**:
- Filter appears in the Sheet list filter panel
- "Yes" shows only sheets with at least one file
- "No" shows only sheets with no files
- Works in combination with the text filter

**Deliverables**:
- `src/Filter/HasPdfFilter.php`
- Updated `SheetCrudController::configureFilters()`

---

### Story 7.2: Prepare Template for Tag Filter (Live Coding) ⏳

**Description**: Create a skeleton file for the filter that will be live-coded during the talk.

`Sheet.tags` is a `SIMPLE_ARRAY`, stored as a comma-separated string in the DB
(e.g. `"jazz,piano,solo"`). Filtering requires a `LIKE` query; the form field presents a
dynamic choice list populated from the repository.

**Filter Template** (`src/Filter/TagFilter.TEMPLATE.php`):
```php
<?php

namespace App\Filter;

use App\Repository\SheetRepository;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * TEMPLATE for Live Coding
 *
 * This filter lets the user pick a tag from the list of all known tags and
 * filters sheets whose tags column contains that value.
 *
 * Steps to implement:
 * 1. Create the class implementing FilterInterface + FilterTrait
 * 2. Inject SheetRepository via constructor (to populate choices)
 * 3. Implement static new() with setFilterFqcn / setProperty / setLabel
 * 4. Implement buildForm() with a ChoiceType fed by $repository->getAllTags()
 * 5. Implement apply() with a LIKE query on the tags column
 */
class TagFilter implements FilterInterface
{
    use FilterTrait;

    // TODO: constructor injecting SheetRepository

    // TODO: static new() method

    // TODO: buildForm() — ChoiceType with dynamic choices from repository

    // TODO: apply() — LIKE query: "$alias.tags LIKE :tag"
}
```

**Acceptance Criteria**:
- Template file in place with helpful comments guiding each step
- File does not break the app (it is never registered)

**Deliverables**:
- `src/Filter/TagFilter.TEMPLATE.php`

---

### Story 7.3: Implement Tag Filter — Complete Version (Safety Net) ⏳

**Description**: The full working implementation committed to the safety net branch.

**Filter** (`src/Filter/TagFilter.php`):
```php
<?php

namespace App\Filter;

use App\Repository\SheetRepository;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class TagFilter implements FilterInterface
{
    use FilterTrait;

    public function __construct(private readonly SheetRepository $sheetRepository)
    {
    }

    public static function new(string $propertyName, string $label = 'Tag'): self
    {
        return (new self())
            ->setFilterFqcn(__CLASS__)
            ->setProperty($propertyName)
            ->setLabel($label);
    }

    public function buildForm(FormBuilderInterface $builder): void
    {
        $tags = $this->sheetRepository->getAllTags();
        $choices = array_combine($tags, $tags);

        $builder->add('value', ChoiceType::class, [
            'choices' => $choices,
            'required' => false,
            'placeholder' => 'Any tag',
        ]);
    }

    public function apply(
        QueryBuilder $queryBuilder,
        FilterDataDto $filterDataDto,
        ?FieldDto $fieldDto,
        EntityDto $entityDto
    ): void {
        $value = $filterDataDto->getValue();
        if (empty($value)) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];
        $queryBuilder
            ->andWhere("$alias.tags LIKE :tag")
            ->setParameter('tag', '%' . $value . '%');
    }
}
```

**Note on the LIKE approach**: `SIMPLE_ARRAY` serialises as `value1,value2,value3`. A `LIKE '%jazz%'`
will match a tag called `jazz` but would also match `jazz-piano`. Acceptable for a demo;
a production implementation could use `LIKE '%,jazz,%'` with padding on both ends,
or migrate tags to a proper join table.

**SheetCrudController** — update `configureFilters()`:
```php
use App\Filter\HasPdfFilter;
use App\Filter\TagFilter;

public function configureFilters(Filters $filters): Filters
{
    return $filters
        ->add(TextFilter::new('title'))
        ->add(HasPdfFilter::new('files', 'Has PDF'))
        ->add(TagFilter::new('tags', 'Tag'));
}
```

**Acceptance Criteria**:
- Dropdown lists all tags present in the DB
- Selecting a tag filters the sheet list correctly
- Works in combination with the other filters
- All existing tests still pass

**Deliverables**:
- `src/Filter/TagFilter.php`
- Updated `SheetCrudController::configureFilters()`

---

### Story 7.4: Document Filter Pattern ⏳

**Description**: Short doc explaining the custom filter pattern for future reference.

**Deliverables**:
- `docs/patterns/filters.md`

---

## Epic Acceptance Criteria

- [ ] `HasPdfFilter` created and working
- [ ] `TagFilter.TEMPLATE.php` prepared for live coding
- [ ] `TagFilter.php` complete version ready (safety net)
- [ ] Filter pattern documented
- [ ] Live coding rehearsed 5+ times
- [ ] Timing under 4 minutes for live coding
- [ ] Safety net branch (`step-2-custom-filters`) tagged and tested
- [ ] All filters work independently and in combination

---

## Live Coding Rehearsal Checklist

Practice until you can complete in under 4 minutes:

```
Rehearsal 1: [ ] Time: ____ mins
Rehearsal 2: [ ] Time: ____ mins
Rehearsal 3: [ ] Time: ____ mins
Rehearsal 4: [ ] Time: ____ mins
Rehearsal 5: [ ] Time: ____ mins
```

---

## Next Epic

**Epic 8**: Custom Actions (LIVE CODING)
