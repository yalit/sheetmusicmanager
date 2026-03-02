function initCollectionTables() {
    document.querySelectorAll('[data-table-collection-field]').forEach((container) => {
        const tbody = container.querySelector('[data-table-collection-body]');
        if (!tbody || container.classList.contains('table-collection-processed')) return;

        container.classList.add('table-collection-processed');

        container.querySelectorAll('.table-collection-delete').forEach((btn) => {
            btn.addEventListener('click', () => btn.closest('tr').remove());
        });

        const addBtn = container.querySelector('.table-collection-add');
        if (!addBtn) return;

        addBtn.addEventListener('click', () => {
            const templateEl = container.querySelector('template[data-table-prototype]');
            if (!templateEl) return;

            let numItems = parseInt(container.dataset.numItems ?? '0', 10);
            const placeholder = container.dataset.formTypeNamePlaceholder;
            const labelRegexp = new RegExp(`${placeholder}label__`, 'g');
            const nameRegexp = new RegExp(placeholder, 'g');

            const newRowHtml = templateEl.innerHTML
                .replace(labelRegexp, numItems)
                .replace(nameRegexp, numItems);

            container.dataset.numItems = ++numItems;
            tbody.insertAdjacentHTML('beforeend', newRowHtml);

            const newRow = tbody.lastElementChild;
            newRow?.querySelector('.table-collection-delete')?.addEventListener('click', () => newRow.remove());

            document.dispatchEvent(new CustomEvent('ea.collection.item-added', { detail: { newElement: newRow } }));
        });
    });
}

document.addEventListener('DOMContentLoaded', initCollectionTables);
document.addEventListener('ea.collection.item-added', initCollectionTables);
