import { Controller } from '@hotwired/stimulus';
import TomSelect from 'tom-select';

export default class extends Controller {
    static values = {
        choices:   Array,
        separator: { type: String,  default: ',' },
        create:    { type: Boolean, default: false },
        multiple:  { type: Boolean, default: true },
    };

    #tomSelect = null;

    connect() {
        const items   = this.element.value ? this.element.value.split(this.separatorValue) : [];
        const choices = Array.from(new Set(this.choicesValue.concat(items))).sort().filter(v => v !== '');
        const options = choices.map(s => ({ value: s, text: s }));

        this.#tomSelect = new TomSelect(this.element, {
            items,
            options,
            create:    this.createValue,
            persist:   false,
            delimiter: this.separatorValue,
            maxItems:  this.multipleValue ? null : 1,
        });
    }

    disconnect() {
        this.#tomSelect?.destroy();
        this.#tomSelect = null;
    }
}
