function initSetlistCollectionHeader() {
    document.querySelectorAll('[data-setlist-collection]').forEach((container) => {
        if (container.querySelector('.setlist-header-row')) return;

        const collectionItems = container.querySelector('.ea-form-collection-items');
        if (!collectionItems) return;

        const header = document.createElement('div');
        header.className = 'setlist-header-row';
        header.innerHTML = `
            <div>Position</div>
            <div>Sheet</div>
            <div>Name</div>
            <div>Notes</div>
            <div></div>
        `;

        collectionItems.prepend(header);
    });
}

document.addEventListener('DOMContentLoaded', initSetlistCollectionHeader);
document.addEventListener('ea.collection.item-added', initSetlistCollectionHeader);
