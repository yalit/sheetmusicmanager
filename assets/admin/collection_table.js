import Sortable from 'sortablejs';

class CollectionTable {
    #container;
    #tbody;

    constructor(container) {
        this.#container = container;
        this.#tbody = container.querySelector('[data-table-collection-body]');

        this.#initDeleteButtons(container.querySelectorAll('.table-collection-delete'));
        this.#initAddButton();

        if (container.dataset.allowSort === 'true') {
            this.#initSortable();
        }
    }

    #initDeleteButtons(buttons) {
        buttons.forEach((btn) => btn.addEventListener('click', () => btn.closest('tr').remove()));
    }

    #initAddButton() {
        const addBtn = this.#container.querySelector('.table-collection-add');
        if (!addBtn) return;

        addBtn.addEventListener('click', () => this.#addRow());
    }

    #addRow() {
        const templateEl = this.#container.querySelector('template[data-table-prototype]');
        if (!templateEl) return;

        let numItems = parseInt(this.#container.dataset.numItems ?? '0', 10);
        const placeholder = this.#container.dataset.formTypeNamePlaceholder;
        const labelRegexp = new RegExp(`${placeholder}label__`, 'g');
        const nameRegexp = new RegExp(placeholder, 'g');

        const newRowHtml = templateEl.innerHTML
            .replace(labelRegexp, numItems)
            .replace(nameRegexp, numItems);

        this.#container.dataset.numItems = ++numItems;
        this.#tbody.insertAdjacentHTML('beforeend', newRowHtml);

        const newRow = this.#tbody.lastElementChild;
        this.#initDeleteButtons(newRow.querySelectorAll('.table-collection-delete'));

        document.dispatchEvent(new CustomEvent('ea.collection.item-added', { detail: { newElement: newRow } }));
    }

    #initSortable() {
        Sortable.create(this.#tbody, {
            handle: '.drag-handle',
            animation: 150,
            onEnd: () => this.#reindexRows(),
        });
    }

    #reindexRows() {
        this.#tbody.querySelectorAll('tr').forEach((row, newIndex) => {
            const oldIndex = row.dataset.rowIndex;
            if (oldIndex === undefined) return;

            row.querySelectorAll('input, select, textarea').forEach((field) => {
                if (field.name) field.name = field.name.replace(`[${oldIndex}]`, `[${newIndex}]`);
                if (field.id) field.id = field.id.replace(`_${oldIndex}_`, `_${newIndex}_`);
            });

            row.querySelectorAll('label').forEach((label) => {
                if (label.htmlFor) label.htmlFor = label.htmlFor.replace(`_${oldIndex}_`, `_${newIndex}_`);
            });

            row.dataset.rowIndex = newIndex;
        });
    }
}

function initCollectionTables() {
    document.querySelectorAll('[data-table-collection-field]').forEach((container) => {
        if (container.classList.contains('table-collection-processed')) return;
        container.classList.add('table-collection-processed');
        new CollectionTable(container);
    });
}

document.addEventListener('DOMContentLoaded', initCollectionTables);
document.addEventListener('ea.collection.item-added', initCollectionTables);
