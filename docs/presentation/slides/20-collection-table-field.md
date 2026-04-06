---
layout: two-cols
---

# CollectionTableField

<div class="mt-2">
  <div class="text-md font-semibold uppercase tracking-wide opacity-40 mb-2">Problem</div>
  <p class="text-md opacity-80">We need to configure the table layout and define what fields each row contains — without losing EA's built-in collection machinery.</p>
  <div v-click="1" class="text-md font-semibold uppercase tracking-wide opacity-40 mb-2">Solution</div>

  <v-clicks>

  - Factory returning `CollectionField` — keeps EA's `CollectionConfigurator`
  - with custom `TableCollectionType` for the specific display
  - `useEntryCrudForm()` — row fields come from a dedicated CRUD controller

  </v-clicks>
</div>

::right::

<div class="mt-[100px]" v-click="1">

````md magic-move {at:2}
```php {1,2,8|1,2,9,10}
// Factory — decorate CollectionField
class CollectionTableField
{
    public static function new(
        string $propertyName,
        ?string $label = null
    ): CollectionField {
        return CollectionField::new($propertyName, $label)
            // defines a specific block name
            ->setFormType(TableCollectionType::class);
    }
}
```

```php {3-4}
// SheetCrudController
yield CollectionTableField::new('credit', 'Credits')
   // new in EasyAdmin
   ->useEntryCrudForm(CreditedPersonCrudController::class)
   ->allowAdd()
   ->allowDelete()
   ->hideOnIndex();
```
````

</div>

<div v-click="3">

````md magic-move {at:4}
```php {1,2,3,9-12}
// CreditedPersonCrudController - only for table rows 
class CreditedPersonCrudController 
      extends AbstractCrudController
{
    public static function getEntityFqcn(): string {
        return CreditedPerson::class;
    }

    public function configureFields(string $pageName) {
        yield AssociationField::new('person', 'Person');
        yield AssociationField::new('personType', 'Type');
    }
}
```
````

</div>

<!--
Returning CollectionField instead of implementing FieldInterface is the key choice here — it means EA's CollectionConfigurator runs and processes allowAdd(), allowDelete(), useEntryCrudForm() etc. for free. The entry CRUD controller is a lightweight object: its only job is to answer configureFields(). EA reads those fields and uses them as the prototype for new rows.
-->
