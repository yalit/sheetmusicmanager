import { Controller } from '@hotwired/stimulus';
import Sortable from 'sortablejs';

export default class extends Controller {
    static targets = ['body', 'prototype'];
    static values  = {
        allowSort: Boolean,
        numItems: Number,
        namePlaceholder: String,
    };

    #sortable = null;

    connect() {
        if (this.allowSortValue) {
            this.#sortable = Sortable.create(this.bodyTarget, {
                handle: '.drag-handle',
                animation: 150,
                onEnd: () => this.#reindexRows(),
            });
        }
    }

    disconnect() {
        this.#sortable?.destroy();
        this.#sortable = null;
    }

    add() {
        const placeholder = this.namePlaceholderValue;
        const index = this.numItemsValue;
        const labelRegexp = new RegExp(`${placeholder}label__`, 'g');
        const nameRegexp  = new RegExp(placeholder, 'g');

        const html = this.prototypeTarget.innerHTML
            .replace(labelRegexp, index)
            .replace(nameRegexp, index);

        this.numItemsValue = index + 1;
        this.bodyTarget.insertAdjacentHTML('beforeend', html);

        document.dispatchEvent(new CustomEvent('ea.collection.item-added', {
            detail: { newElement: this.bodyTarget.lastElementChild },
        }));
    }

    delete({ currentTarget }) {
        currentTarget.closest('tr').remove();
    }

    #reindexRows() {
        this.bodyTarget.querySelectorAll('tr').forEach((row, newIndex) => {
            const oldIndex = row.dataset.rowIndex;
            if (oldIndex === undefined) return;

            row.querySelectorAll('input, select, textarea').forEach((field) => {
                if (field.name) field.name = field.name.replace(`[${oldIndex}]`, `[${newIndex}]`);
                if (field.id)   field.id   = field.id.replace(`_${oldIndex}_`, `_${newIndex}_`);
            });

            row.querySelectorAll('label').forEach((label) => {
                if (label.htmlFor) label.htmlFor = label.htmlFor.replace(`_${oldIndex}_`, `_${newIndex}_`);
            });

            row.dataset.rowIndex = newIndex;
        });
    }
}
