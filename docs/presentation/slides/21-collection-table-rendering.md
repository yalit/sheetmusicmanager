---
layout: two-cols
---

# Table rendering

<div class="mt-2">
  <div class="text-md font-semibold uppercase tracking-wide opacity-40 mb-2">Problem</div>
  <p class="text-md opacity-80">EA's default collection blocks render entries as accordion rows. We need <code>&lt;tr&gt;</code> rows and the prototype in a <code>&lt;template&gt;</code> tag — not a data attribute (escaping issues).</p>
  <div v-click="1" class="text-md font-semibold uppercase tracking-wide opacity-40 mb-2">Solution</div>

  <v-clicks>

  - `table_collection_widget` — renders `<table>` + existing entries as `<tr>` rows
  - Prototype in `<template data-table-prototype>` — safe from HTML escaping

  </v-clicks>
</div>

::right::

<div class="mt-[100px]" v-click="1">

````md magic-move {at:2}
```twig {1,3-10,13-19}
{% block table_collection_widget %}
<table> <thead> <tr>
    {% for child in form.vars.prototype %}
        {% if child.vars.block_prefixes
        {# skip EA layout fields  #}
        |filter(p => p starts with 'ea_form_')
        |length == 0 %}
            <th>{{ child.vars.label }}</th>
        {% endif %}
    {% endfor %}
</tr> </thead>
<tbody data-table-collection-body>
    {% for entry in form %}
        <tr data-row-index="{{ loop.index0 }}">
            {% for child in entry %}
                <td>...</td>
            {% endfor %}
        </tr>
    {% endfor %}
</tbody> </table>
{% endblock %}
```

```twig {3-5,12-14}
{# prototype stored in <template> #}
{% if form.vars.prototype is defined %}
    <template data-table-prototype>
        <tr data-row-index="__index__">
            {% for child in form.vars.prototype %}
                {% if child.vars.block_prefixes
                {# skip EA layout fields  #}
                |filter(p => p starts with 'ea_form_')
                |length == 0 %}
                    <th>{{ child.vars.label }}</th>
                {% endif %}
            {% endfor %}
        </tr>
    </template>
{% endif %}
```
````

</div>

<!--
The EA layout field filter is critical — fieldset open/close markers appear as form children and would generate ghost columns if not filtered. The prototype is put in a <template> tag rather than a data-prototype attribute because the HTML in the row widgets gets double-escaped when placed inside an attribute value — the <template> approach avoids that entirely.
-->
