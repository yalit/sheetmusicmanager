---
layout: two-cols
---

# **HasPdfFilter** — boolean filter on a JSON column


<v-clicks>

- Sheet `files` are stored as a JSON array => no built-in "is empty" filter for that
- `FilterInterface` + `FilterTrait` — two lines of boilerplate
- `new()` factory — property, label, form type (`BooleanFilterType` = Yes/No)
- `apply()` — append a raw DQL `WHERE` clause to the QueryBuilder

</v-clicks>

::right::

<div class="mt-[140px]" v-click="1">

````md magic-move {at:2}
```php {1-3,6-7}
// SheetCrudController
public function configureFilters(Filters $filters): Filters
{
    return $filters
        ->add(TextFilter::new('title'))
        ->add(HasPdfFilter::new('files', 'Has PDF'));
}
```

```php
class HasPdfFilter implements FilterInterface
{
    use FilterTrait;
}
```

```php
class HasPdfFilter implements FilterInterface
{
    use FilterTrait;

    public static function new(
        string $propertyName,
        string $label
    ): self {
        return (new self())
            ->setFilterFqcn(__CLASS__)
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(BooleanFilterType::class);
    }
}
```

```php
class HasPdfFilter implements FilterInterface
{
  //...

  public function apply(
      QueryBuilder $queryBuilder,
      FilterDataDto $filterDataDto,
      ?FieldDto $fieldDto,
      EntityDto $entityDto
  ): void {
      $alias = $queryBuilder->getRootAliases()[0];

      if ($filterDataDto->getValue() === true) {
          $queryBuilder->andWhere("$alias.files != '[]'");
      } elseif ($filterDataDto->getValue() === false) {
          $queryBuilder->andWhere("$alias.files = '[]'");
      }
  }
}
```
````
</div>

<!--
FilterTrait provides the fluent setters and the static factory plumbing — you just implement apply(). The form type drives what the user sees in the filter panel; BooleanFilterType renders a Yes/No select. apply() receives the chosen value as a typed PHP bool, not a string. The WHERE clause is raw DQL — you have full control, which is exactly what you need when the column is not a scalar.
Wired in SheetCrudController with a single line: ->add(HasPdfFilter::new('files', 'Has PDF'))
-->
