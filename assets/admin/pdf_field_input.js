class PdfFieldInput {
    constructor(container) {
        this.input = container.querySelector('#app_pdf_field_input')
        this.loadedFileHolder = container.querySelector('.loaded_files')
        this.existingFileHolder = container.querySelector('.loaded_files_existing')
        this.emptyMessage = container.querySelector('.loaded_files_empty')
        this.keptInput = container.querySelector('#app_pdf_field_kept')
        this.removedInput = container.querySelector('#app_pdf_field_removed')
        this.template = document.getElementById('pdf_field_file')
        this.managedFiles = []

        this.input.addEventListener('change', () => {
            this.managedFiles.push(...this.input.files)
            this.syncInputFiles()
            this.renderFileList()
        })

        this.existingFileHolder?.querySelectorAll('[data-file]').forEach(el => {
            el.addEventListener('click', () => {
                el.closest('.loaded_file').remove()
                let kept = JSON.parse(this.keptInput.value)
                let file = JSON.parse(el.dataset.file)

                kept = kept.filter(({filename}) => filename !== file.filename)
                this.keptInput.value = JSON.stringify(kept)

                let removed = JSON.parse(this.removedInput.value)
                removed.push(file)
                this.removedInput.value = JSON.stringify(removed)
                this.updateEmptyMessage()
            })
        })

        this.updateEmptyMessage()
    }

    formatFileSize(bytes) {
        if (bytes < 1024) return bytes + ' o'
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' Ko'
        return (bytes / (1024 * 1024)).toFixed(1) + ' Mo'
    }

    syncInputFiles() {
        const dt = new DataTransfer()
        this.managedFiles.forEach(f => dt.items.add(f))
        this.input.files = dt.files
    }

    updateEmptyMessage() {
        const existingCount = this.existingFileHolder
            ? this.existingFileHolder.querySelectorAll('.loaded_file').length
            : 0
        const hasFiles = this.managedFiles.length > 0 || existingCount > 0
        this.emptyMessage.style.display = hasFiles ? 'none' : ''
        this.loadedFileHolder.style.display = this.managedFiles.length > 0 ? '' : 'none'
    }

    renderFileList() {
        this.loadedFileHolder.innerHTML = ''

        this.managedFiles.forEach(file => {
            const clone = this.template.content.cloneNode(true)
            clone.firstElementChild.outerHTML =
                clone.firstElementChild.outerHTML
                    .replace(/__FILENAME__/g, file.name)
                    .replace(/__FILESIZE__/g, this.formatFileSize(file.size))
            this.loadedFileHolder.appendChild(clone)
        })

        this.loadedFileHolder.querySelectorAll('[data-new-filename]').forEach(el => {
            el.addEventListener('click', () => {
                let filename = el.dataset.newFilename

                this.managedFiles = this.managedFiles.filter(f => f.name !== filename)
                this.syncInputFiles()
                this.renderFileList()
            })
        })

        this.updateEmptyMessage()
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('app_pdf_field')
    if (container) new PdfFieldInput(container)
})
