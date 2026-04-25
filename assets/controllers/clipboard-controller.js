import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = { text: String };

    copy() {
        navigator.clipboard.writeText(this.textValue).then(() => {
            const icon = this.element.querySelector('i');
            if (!icon) return;
            const original = icon.className;
            icon.className = 'fa-solid fa-check';
            setTimeout(() => { icon.className = original; }, 1500);
        });
    }
}
