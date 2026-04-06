---
layout: two-cols
transition: slide-up
---

# A CRUD controller

<v-clicks depth="1">

- `make:admin:crud PersonCrudController`
- One entity per controller
- One method to configure the CRUD class : `configureCRUD()`:
  - labels, search fields ...
- One method to configure the fields: `configureFields()`
  - what renders where ...

</v-clicks>

::right::

<div class="mt-[98px]" v-click="1">

````md magic-move {at:2}
```php {1-2,21|3-6|8-14|15-20}
class PersonCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Person::class;
    }

    public function configureCrud(Crud $crud)
    {
        return $crud
            ->setEntityLabelInSingular('Person')
            ->setEntityLabelInPlural('Persons')
            ->setSearchFields(['name']);
    }

    public function configureFields(string $pageName)
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('name', 'Name');
    }
}
```
````
</div>

<!--
This is the complete Person CRUD — nothing else needed. EA reads the Doctrine metadata and builds the list, the form, and the detail page. The $pageName argument to configureFields lets you vary what's shown per page: index, new, edit, or detail.
-->
