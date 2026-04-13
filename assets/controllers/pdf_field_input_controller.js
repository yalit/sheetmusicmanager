import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['fileInput', 'fileList', 'existingFileList', 'emptyMessage', 'kept', 'removed', 'template'];

    managedFiles = [];

    connect() {
        this.managedFiles = [];
        this.#updateEmptyMessage();
    }

    fileChanged() {
        this.managedFiles.push(...this.fileInputTarget.files);
        this.#syncInputFiles();
        this.#renderFileList();
    }

    deleteExisting({ currentTarget, params: { file } }) {
        currentTarget.closest('.loaded_file').remove();

        let kept = JSON.parse(this.keptTarget.value);
        kept = kept.filter(({ filename }) => filename !== file.filename);
        this.keptTarget.value = JSON.stringify(kept);

        const removed = JSON.parse(this.removedTarget.value);
        removed.push(file);
        this.removedTarget.value = JSON.stringify(removed);

        this.#updateEmptyMessage();
    }

    deleteNew({ params: { filename } }) {
        this.managedFiles = this.managedFiles.filter(f => f.name !== filename);
        this.#syncInputFiles();
        this.#renderFileList();
    }

    #syncInputFiles() {
        const dt = new DataTransfer();
        this.managedFiles.forEach(f => dt.items.add(f));
        this.fileInputTarget.files = dt.files;
    }

    #renderFileList() {
        this.fileListTarget.innerHTML = '';

        this.managedFiles.forEach(file => {
            const clone = this.templateTarget.content.cloneNode(true);
            clone.firstElementChild.outerHTML =
                clone.firstElementChild.outerHTML
                    .replace(/__FILENAME__/g, file.name)
                    .replace(/__FILESIZE__/g, this.#formatFileSize(file.size));
            this.fileListTarget.appendChild(clone);
        });

        this.#updateEmptyMessage();
    }

    #updateEmptyMessage() {
        const existingCount = this.hasExistingFileListTarget
            ? this.existingFileListTarget.querySelectorAll('.loaded_file').length
            : 0;
        const hasFiles = this.managedFiles.length > 0 || existingCount > 0;

        this.emptyMessageTarget.style.display   = hasFiles ? 'none' : '';
        this.fileListTarget.style.display       = this.managedFiles.length > 0 ? '' : 'none';
    }

    #formatFileSize(bytes) {
        if (bytes < 1024)        return bytes + ' o';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' Ko';
        return (bytes / (1024 * 1024)).toFixed(1) + ' Mo';
    }
}
