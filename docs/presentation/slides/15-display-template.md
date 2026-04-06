---
layout: two-cols
---

# Display on list & detail

<div class="mt-2">
  <div class="text-md font-semibold uppercase tracking-wide opacity-40 mb-2">Problem</div>
  <p class="text-md opacity-80">EA can't render <code>StoredFile[]</code> — it needs to be told where the template is and what variables are available.</p>
  <div v-click="1" class="text-md font-semibold uppercase tracking-wide opacity-40 mb-2">Solution</div>
  <p v-click="1" class="text-md">Define a custom Twig template</p>
  <v-clicks>

  - with `setTemplatePath()` - link to the custom twig template
  - `field.value` holds the resolved property value
  - can use a custom Twig function to render the path to the file

  </v-clicks>
</div>

::right::

<div class="mt-[100px]" v-click="1">

````md magic-move {at:2}
```twig  {1|2,4,8|5-7}
{# templates/admin/fields/pdf.html.twig #}
{# @var field \EasyCorp\Bundle\...\Dto\FieldDto #}
<div>
    {% for file in field.value %}
        <a href="{{ webpath(file) }}">
            {{ file.name }}
        </a>
    {% endfor %}
</div>
```
````

</div>

<!--
field.value is the resolved StoredFile array. webpath() is a custom Twig extension that turns a StoredFile into a public URL. The key pattern here: the same field appears twice in configureFields() — once with hideOnForm() for the display side, once with onlyOnForms() for the upload side. EasyAdmin picks the right one per page type. This is how you cleanly separate read rendering from write input for the same data.
-->
