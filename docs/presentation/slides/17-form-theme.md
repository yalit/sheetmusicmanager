---
layout: two-cols
---

# Form theme

<div class="mt-2">
  <div class="text-md font-semibold uppercase tracking-wide opacity-40 mb-2">Problem</div>
  <p class="text-md opacity-80">We need to override EA's default widget rendering — show the file input, the existing file list, and the hidden tracking inputs.</p>
  <div v-click="1" class="text-md font-semibold uppercase tracking-wide opacity-40 mb-2">Solution</div>
  <p v-click class="text-md">Setup a custom form_theme for the specific Controller or full Dashboard</p>
  <v-clicks>

  - `{prefix}_widget` block overrides the input rendering
  - JS class handles the file handling in the form (`kept/removed`)

  </v-clicks>
</div>

::right::

<div class="mt-[100px]" v-click="1">

````md magic-move {at:2}
```php
// DashboardController
public function configureCrud(): Crud
{
    return parent::configureCrud()
        ->setFormThemes([
            'admin/form.html.twig',
            '@EasyAdmin/crud/form_theme.html.twig',
        ]);
}
```

```twig
{# templates/admin/form.html.twig #}
{% block app_pdf_field_widget %}
    {{ form_widget(form) }}

    <ul class="loaded_files_existing">
        {% for file in existing_files %}
            <li>
                <a href="{{ webpath(file) }}">
                    {{ file.name }}
                 </a>
                <span>{{ filesize(file) }}</span>
            </li>
        {% endfor %}
    </ul>
    <input type="hidden" name="app_pdf_field_kept" ...>
    <input type="hidden" name="app_pdf_field_removed" ...>
{% endblock %}
```

```js
// assets/admin/pdf_field_input.js
class PdfFieldInput {
    constructor(container) {
        this.managedFiles = []

        //  keep <input> in sync via DataTransfer
        this.input.addEventListener('change', () => {
            this.managedFiles.push(...this.input.files)
            this.syncInputFiles()
            this.renderFileList()
        })

        // update kept/removed hidden inputs
        container.querySelectorAll('[data-file]')
            .forEach(el => {
                el.addEventListener('click', () => {
                    // sync of kept and removed lists
                }
            )
        })
    }
    // ...
}
```
````

</div>

<!--
The block renders the file input, the list of existing files (passed in via existing_files from buildView()), and two hidden inputs that track which files to keep and which to delete on submit. The JS class is the glue: when the user selects new files it keeps the real file input in sync using DataTransfer (so multiple selections accumulate instead of replacing). When an existing file is deleted it updates the JSON in the hidden inputs — that's what the PHP updateEntity() reads on submit to know what to delete from storage and what to keep.
-->
