---
layout: two-cols
---

# Form type

<div class="mt-2">
  <div class="text-md font-semibold uppercase tracking-wide opacity-40 mb-2">Problem</div>
  <p class="text-md opacity-80">We need a Symfony form type that handles file uploads and makes existing files available to the form theme block.</p>
  <div v-click="1" class="text-md font-semibold uppercase tracking-wide opacity-40 mb-2">Solution</div>
  <p v-click="1" class="text-md">Implement a custom Symfony Form Type</p>
  <v-clicks>

  - `getParent(FileType)` — inherits upload handling for free
  - `getBlockPrefix()` — names the Twig block
  - `buildView()` — moves options into Twig vars for the block

  </v-clicks>
</div>

::right::

<div class="mt-[100px]" v-click="1">

````md magic-move {at:2}
```php {3-6|7-9|11-19}
final class SheetFileType extends AbstractType
{
    public function getParent(): string {
        return FileType::class; 
    }

    public function getBlockPrefix(): string {
        return 'app_pdf_field'; 
    }

    public function buildView( FormView $view,
        FormInterface $form,
        array $options
    ): void {
        parent::buildView($view, $form, $options);
        $files = $options['existing_files'];
        // provides data to the view
        $view->vars['existing_files'] = $files;
    }
}
```
````

</div>

<!--
getParent() means SheetFileType inherits FileType's data handling — the uploaded file lands as a UploadedFile object without any extra work. getBlockPrefix() is the naming convention Symfony uses to find the right Twig block: it looks for {prefix}_widget in the active form themes. buildView() is the bridge: options set on the field via setFormTypeOption() reach the Twig block through view vars.
-->
