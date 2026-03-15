import Sortable from 'sortablejs';

/**
 * Initialises drag-and-drop reordering on table collection fields that have
 * data-allow-sort="true". Uses SortableJS for drag handling.
 *
 * After each drag, the form field names and IDs in every row are renumbered to
 * match the new DOM order so that syncItemPositions() assigns correct positions
 * on form save.
 */
function initSortableCollectionTables() {
    document.querySelectorAll('[data-table-collection-field][data-allow-sort="true"]').forEach((container) => {
        const tbody = container.querySelector('[data-table-collection-body]');
        if (!tbody || container.classList.contains('table-collection-sort-processed')) return;

        container.classList.add('table-collection-sort-processed');

        Sortable.create(tbody, {
            handle: '.drag-handle',
            animation: 150,
            onEnd: () => reindexRows(tbody),
        });
    });
}

/**
 * Renumbers every form field name and id in the tbody rows to match their
 * current DOM position index.
 *
 * Field names follow the pattern: parent[collection][oldIndex][field]
 * Field ids follow the pattern:   parent_collection_oldIndex_field
 *
 * We derive the old index from the row's data-row-index attribute and replace
 * only that segment to avoid touching unrelated numeric substrings.
 */
function reindexRows(tbody) {
    tbody.querySelectorAll('tr').forEach((row, newIndex) => {
        const oldIndex = row.dataset.rowIndex;
        if (oldIndex === undefined) return;

        row.querySelectorAll('input, select, textarea').forEach((field) => {
            if (field.name) {
                field.name = field.name.replace(`[${oldIndex}]`, `[${newIndex}]`);
            }
            if (field.id) {
                field.id = field.id.replace(`_${oldIndex}_`, `_${newIndex}_`);
            }
        });

        row.querySelectorAll('label').forEach((label) => {
            if (label.htmlFor) {
                label.htmlFor = label.htmlFor.replace(`_${oldIndex}_`, `_${newIndex}_`);
            }
        });

        row.dataset.rowIndex = newIndex;
    });
}

document.addEventListener('DOMContentLoaded', initSortableCollectionTables);
