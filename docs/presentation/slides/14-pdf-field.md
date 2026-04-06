---
layout: two-cols
---

# Custom field - PDFField

<div class="mt-2">
  <div class="text-md font-semibold uppercase tracking-wide opacity-40 mb-2">Problem</div>
  <p class="text-md opacity-80">EA needs a config object to know what template to use on list/detail and what form type to render on new/edit.</p>
  <div v-click="1" class="text-md font-semibold uppercase tracking-wide opacity-40 mb-2">Solution</div>
  <p v-click class="text-md">Create a custom EasyAdmin Field (implements <code>FieldInterface</code>)</p>
  <v-clicks>

  -  use `FieldTrait` — fluent setters for free
  - `setTemplatePath()` — wires the display template
  - `setFormType()` — wires the form type
  - `setFormTypeOption()` — passes data across the field

  </v-clicks>
</div>

::right::

<div class="mt-[100px]" v-click="1">

````md magic-move {at:2}
```php {1,3|2}
class PDFField implements FieldInterface {
    use FieldTrait;
}
```

```php {9|10}
class PDFField implements FieldInterface {
    use FieldTrait;

    public static function new(string $propertyName,
        string|null $label = null
    ): self {
        return (new self())->setProperty($propertyName)
          ->setLabel($label)
          ->setTemplatePath('admin/fields/pdf.html.twig');
          ->setFormType(SheetFileType::class)
    }
}
```

```php {13-18}
class PDFField implements FieldInterface {
    use FieldTrait;

    public static function new(string $propertyName,
        string|null $label = null
    ): self {
        return (new self())->setProperty($propertyName)
          ->setLabel($label)
          ->setTemplatePath('admin/fields/pdf.html.twig');
          ->setFormType(SheetFileType::class)
    }

    /** @param StoredFile[] $data */
    public function setExistingFiles(array $data): self
    {
        $this->setFormTypeOption('existing_files', $data);
        return $this;
    }
}
```
````
</div>

<!--
FieldTrait implements all the fluent setters that build the FieldDto config object. setFormTypeOption() is the bridge between the field config layer and the Symfony form layer — whatever you pass here ends up in the form type's $options array.
-->
