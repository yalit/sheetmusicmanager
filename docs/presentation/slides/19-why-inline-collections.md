---
layout: two-cols
---

# Why a custom collection?

<div class="mt-2">
  <div class="text-md font-semibold uppercase tracking-wide opacity-40 mb-2">Problem</div>
  <p class="text-md opacity-80">EA's default <code>CollectionField</code> renders each entry as an accordion panel — unusable for compact tabular data like credits or setlist items.</p>
  <div v-click="1" class="text-md font-semibold uppercase tracking-wide opacity-40 mb-2">Solution</div>
  <p v-click class="text-md">Decorate the CollectionField</p>

  <v-clicks>

  - Render entries in a `<table>` instead
  - Column headers derived from field labels
  - Add / delete rows inline, no page reload
  - Optional drag-and-drop reordering

  </v-clicks>
</div>

::right::

<div class="mt-[100px]">


````md magic-move {at:1}
```
Default CollectionField ← accordion
┌
│ ▶ Entry #1                   
│ ▶ Entry #2                  
│ ▶ Entry #3                  
└
```

```
CollectionTableField
┌──────────────┬──────────────┐
│ Person       │ Type         │  ← table header
├──────────────┼──────────────┤
│ [input]      │ [select]     │  ← inline rows
│ [input]      │ [select]     │
└──────────────┴──────────────┘
  + Add item
```
```` 

</div>

<!--
The accordion is fine when each entry has many fields and needs breathing room. For a credits list with two fields (person + type) or a setlist with one field (sheet), it's massive overkill and wastes vertical space. The table layout keeps everything compact and scannable.
-->
