# Custom Filters in EasyAdmin

## Anatomy of a custom filter

```php
class MyFilter implements FilterInterface
{
    use FilterTrait;

    public static function new(string $propertyName, string $label): self
    {
        return (new self())
            ->setFilterFqcn(__CLASS__)   // required — tells EA which class handles this filter
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(/* EA filter type */)
            ->setFormTypeOptions([/* ... */]);
    }

    public function apply(
        QueryBuilder $queryBuilder,
        FilterDataDto $filterDataDto,
        ?FieldDto $fieldDto,
        EntityDto $entityDto
    ): void {
        $value      = $filterDataDto->getValue();
        $comparison = $filterDataDto->getComparison(); // 'IN', 'NOT IN', '=', '!=', …
        $alias      = $queryBuilder->getRootAliases()[0];
        // build WHERE clause with andWhere() + named parameters
    }
}
```

Key rules:
- `setFilterFqcn(__CLASS__)` is mandatory — EA uses it to re-instantiate the filter on subsequent requests.
- Always use `andWhere()`, never `where()`, so multiple active filters compose correctly.
- Always guard against empty/null values before touching the QueryBuilder.

---

## Choosing the form type

Use **EasyAdmin's own filter form types** (not raw Symfony form types) — they handle the
comparison operator dropdown and value serialisation automatically.

| Need | Form type |
|---|---|
| Yes / No / Any | `BooleanFilterType` |
| Single or multi select from a list | `ChoiceFilterType` |
| Free-text contains/not-contains | `TextFilterType` |
| Numeric comparison | `NumericFilterType` |
| Date range | `DateTimeFilterType` |

All live in `EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\`.

---

## Pattern 1 — Boolean filter (`HasPdfFilter`)

For columns where you want to filter on presence/absence of a value.

```php
->setFormType(BooleanFilterType::class)
```

`getValue()` returns `true`, `false`, or `null` (= no filter applied). Handle the `null` case
by simply not adding any clause.

```php
if ($filterDataDto->getValue() === true) {
    $queryBuilder->andWhere("$alias.files IS NOT NULL AND $alias.files != ''");
} elseif ($filterDataDto->getValue() === false) {
    $queryBuilder->andWhere("$alias.files IS NULL OR $alias.files = ''");
}
// null → no-op
```

---

## Pattern 2 — Choice filter with dynamic options (`TagFilter`)

When choices come from the database, pass them in via the `new()` factory — the controller
already has the repository, so no injection is needed on the filter itself.

```php
// In SheetCrudController::configureFilters()
->add(TagFilter::new('tags', 'Tag', fn() => $this->sheetRepository->getAllTags()))
```

Pass choices as a callable so the DB call is deferred to render time, not class-load time.
Store them as `ChoiceFilterType` options:

```php
->setFormType(ChoiceFilterType::class)
->setFormTypeOptions(['value_type_options.choices' => $tagChoices()])
```

`ChoiceFilterType` exposes a comparison operator (IN / NOT IN) in the UI for free. Read it
in `apply()` to support both "contains" and "does not contain":

```php
$comparison = $filterDataDto->getComparison(); // 'IN' or 'NOT IN'
$like = $comparison === 'IN' ? 'LIKE' : 'NOT LIKE';
$queryBuilder->andWhere("$alias.tags $like :tag")
             ->setParameter('tag', "%{$value}%");
```

For multiple selected values, each needs its own named parameter:

```php
$parameters = new ArrayCollection();
foreach ($value as $k => $item) {
    $whereQueries[] = "$alias.tags $like :tag_$k";
    $parameters->add(new Parameter("tag_$k", "%{$item}%"));
}
$queryBuilder->andWhere(implode(' OR ', $whereQueries))
             ->setParameters($parameters);
```

Extra fluent configurators (e.g. `allowMultiple()`) can be added by calling
`setFormTypeOption()` and returning `$this`.

---

## SIMPLE_ARRAY and LIKE queries

`Sheet.tags` is a `SIMPLE_ARRAY` column — Doctrine stores `['jazz', 'piano']` as the
plain string `jazz,piano`. A `LIKE '%jazz%'` query works but will also match a hypothetical
`jazz-piano` tag. Acceptable for a demo or low-cardinality tag sets; for production,
migrate tags to a join table and use an `IN` clause on the related entity.
