---
layout: two-cols
---

# JS — add / delete / sort

<div class="mt-2">
  <div class="text-md font-semibold uppercase tracking-wide opacity-40 mb-2">Problem</div>
  <p class="text-md opacity-80">Adding rows requires cloning the prototype and fixing field indexes. Drag-and-drop reordering requires renumbering field names after each drop so the controller receives positions in DOM order.</p>
  <div v-click="1" class="text-md font-semibold uppercase tracking-wide opacity-40 mb-2">Solution</div>
  <v-clicks>

  - One `CollectionTable` class — add, delete, and optional sort
  - Sort: SortableJS on `<tbody>`, resets proper index for actual positions
  - Controller: `syncItemPositions()` assigns final positions on save

  </v-clicks>
</div>

::right::

<div class="mt-[100px]" v-click="1">

````md magic-move {at:2}
```js
class CollectionTable {
    constructor(container) {
        this.#tbody = container
                   .querySelector('[data-table-...]');
        this.#initDeleteButtons()
        this.#initAddButton();
        if (container.dataset.allowSort === 'true') {
            this.#initSortable();
         }
    }

    #initDeleteButtons() 
    { // remove closest <tr> on click }

    #initAddButton() 
    { // clone and update template, append <tr /> }

    #initSortable() 
    { // SortableJS on tbody + reindexRows on drop }
}
```

```js
#initSortable() {
    Sortable.create(this.#tbody, {
        handle: '.drag-handle',
        onEnd: () => this.#reindexRows(),
    });
}

#reindexRows() {
    this.#tbody.querySelectorAll('tr')
        .forEach((row, newIndex) => {
            const oldIndex = row.dataset.rowIndex;
            row
            .querySelectorAll('input, select, textarea')
            .forEach(field => {
                field.name =  ... // old by new
                field.id   = ... // old by new
            }
        );
        row.dataset.rowIndex = newIndex;
    });
}
```

```php
class SetlistCrudController extends AbstractCrudController
{
    // …

    private function syncPositions(Setlist $setlist) {
        $position = 1;
        foreach ($setlist->getItems() as $item) {
            $item->setPosition($position++);
        }
    }

    public function persistEntity(…) {
        $this->syncItemPositions($instance);
        parent::persistEntity($entityManager, $instance);
    }

    public function updateEntity(…) {
        $this->syncItemPositions($instance);
        parent::updateEntity($entityManager, $instance);
    }
}
```
````

</div>

<!--
Sortable can't work without the collection table infrastructure — they share the same container, tbody, and row structure — so merging them into one class was the right call. The constructor is the key: it wires add, delete, and optionally sort in one place. reindexRows() replaces only the specific index segment — brackets for names, underscores for IDs — to avoid mangling other numeric substrings. syncItemPositions() iterates the Doctrine collection in submission order and assigns sequential positions — the DOM order after drag-and-drop is what Symfony deserializes.
-->
