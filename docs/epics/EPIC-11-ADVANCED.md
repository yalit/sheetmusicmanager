# Epic 11: Advanced Features

**Branch**: `epic/11-advanced`
**Status**: ✅ Complete
**Dependencies**: Epic 3 (Basic Admin), Epic 8 (Actions)

---

## Goal

Add a filter-aware CSV export action and complete search field configuration on the Sheet index.

---

## Stories

### Story 11.1: CSV Export with Filters (Sheet index)

**Description**: Add a global "Export CSV" action on the Sheet index that streams a CSV
respecting whatever search query and filters are currently active.

**Technical approach**:

Use `createIndexQueryBuilder()` (already available on `AbstractCrudController`) with the
`AdminContext`'s `SearchDto`, `EntityDto`, `FieldCollection`, and `FilterCollection`. This
reuses the exact same query pipeline as the index page — no manual filter re-application.

```php
// in SheetCrudController

use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Factory\FilterFactory;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Symfony\Component\HttpFoundation\StreamedResponse;

public function configureActions(Actions $actions): Actions
{
    $export = Action::new('export', 'Export CSV')
        ->linkToCrudAction('export')
        ->setIcon('fa fa-download')
        ->addCssClass('btn btn-secondary')
        ->createAsGlobalAction();

    return $actions
        ->add(Crud::PAGE_INDEX, $export)
        // ... existing actions
    ;
}

public function export(AdminContext $context): StreamedResponse
{
    $fields  = FieldCollection::new($this->configureFields(Crud::PAGE_INDEX));
    $filters = $this->container->get(FilterFactory::class)->create(
        $context->getCrud()->getFiltersConfig(),
        $fields,
        $context->getEntity()
    );

    $queryBuilder = $this->createIndexQueryBuilder(
        $context->getSearch(),
        $context->getEntity(),
        $fields,
        $filters
    );

    $sheets = $queryBuilder->getQuery()->getResult();

    $response = new StreamedResponse(function () use ($sheets) {
        $handle = fopen('php://output', 'w');

        fputcsv($handle, ['ID', 'Title', 'Refs', 'Tags', 'Credits', 'Files', 'Created at']);

        foreach ($sheets as $sheet) {
            fputcsv($handle, [
                $sheet->getId(),
                $sheet->getTitle(),
                implode(', ', $sheet->getRefs()),
                implode(', ', $sheet->getTags()),
                implode(' / ', $sheet->getCredit()->map(fn($c) => (string) $c)->toArray()),
                count($sheet->getFiles()),
                $sheet->getCreatedAt()?->format('Y-m-d'),
            ]);
        }

        fclose($handle);
    });

    $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
    $response->headers->set('Content-Disposition', sprintf(
        'attachment; filename="sheets-%s.csv"',
        date('Y-m-d')
    ));

    return $response;
}
```

**Acceptance Criteria**:
- Export CSV button visible on Sheet index
- Exported rows match the currently filtered/searched result set
- CSV columns: ID, Title, Refs, Tags, Credits, Files count, Created at
- File downloads with `sheets-YYYY-MM-DD.csv` filename

**Deliverables**:
- `export()` action method in `SheetCrudController`
- Export action wired in `configureActions()`

---

### Story 11.4: Extend Search Fields (Sheet index)

**Description**: `title` and `tags` are already in `setSearchFields`. Extend to also cover
`refs` and `notes` so the global search box is genuinely useful.

**Current state** (`SheetCrudController::configureCrud`):
```php
->setSearchFields(['title', 'tags'])
```

**Target**:
```php
->setSearchFields(['title', 'tags', 'refs', 'notes'])
```

`refs` and `tags` are `SIMPLE_ARRAY` columns (comma-separated strings); EA's `LIKE`
search works correctly against them.

`credit.person.name` is intentionally excluded — `credit` is a `OneToMany`, joining it
for search produces duplicate rows on the index without extra deduplication work.

**Acceptance Criteria**:
- Searching by a ref code returns matching sheets
- Searching by a word in notes returns matching sheets
- No duplicate rows on index

**Deliverables**:
- Updated `setSearchFields()` call in `SheetCrudController`

---

## Epic Acceptance Criteria

- [x] Export CSV button on Sheet index
- [x] Exported data respects active filters and search
- [x] Search covers title, tags, refs, notes
- [x] No regressions on existing filters

---

## Next Epic

**Epic 12**: Demo Data & Testing
