//TODO : load it only when pdf_input is present...
const pdf_input = document.getElementById("app_pdf_field_input")
const container = pdf_input.parentElement
const loadedFileHolder = container.getElementsByClassName("loaded_files")[0]
const existingFileHolder = container.getElementsByClassName("loaded_files_existing")[0] ?? null
const emptyMessage = container.getElementsByClassName("loaded_files_empty")[0]
const keptInput = document.getElementById("app_pdf_field_kept")
const removedInput = document.getElementById("app_pdf_field_removed")
const template = document.getElementById("pdf_field_file")

let managedFiles = []

function formatFileSize(bytes) {
    if (bytes < 1024) return bytes + ' o'
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' Ko'
    return (bytes / (1024 * 1024)).toFixed(1) + ' Mo'
}

function syncInputFiles() {
    const dt = new DataTransfer()
    managedFiles.forEach(f => dt.items.add(f))
    pdf_input.files = dt.files
}

function updateEmptyMessage() {
    const existingCount = existingFileHolder
        ? existingFileHolder.querySelectorAll(".loaded_file").length
        : 0
    const hasFiles = managedFiles.length > 0 || existingCount > 0
    emptyMessage.style.display = hasFiles ? "none" : ""
    loadedFileHolder.style.display = managedFiles.length > 0 ? "" : "none"
}

function renderFileList() {
    loadedFileHolder.innerHTML = ""

    managedFiles.forEach(file => {
        const clone = template.content.cloneNode(true)
        clone.firstElementChild.outerHTML =
            clone.firstElementChild.outerHTML
                .replace(/__FILENAME__/g, file.name)
                .replace(/__FILESIZE__/g, formatFileSize(file.size))
        loadedFileHolder.appendChild(clone)
    })

    loadedFileHolder.querySelectorAll("[data-file-remove]").forEach(el => {
        el.addEventListener("click", () => {
            managedFiles = managedFiles.filter(f => f.name !== el.dataset.fileRemove)
            syncInputFiles()
            renderFileList()
        })
    })

    updateEmptyMessage()
}

// Handle existing file removal
if (existingFileHolder) {
    existingFileHolder.querySelectorAll("[data-existing-remove]").forEach(el => {
        el.addEventListener("click", () => {
            el.closest(".loaded_file").remove()
            let kept = JSON.parse(keptInput.value)
            kept = kept.filter(name => name !== el.dataset.existingRemove)
            keptInput.value = JSON.stringify(kept)

            let removed = JSON.parse(removedInput.value)
            removed.push(el.dataset.existingRemove)
            removedInput.value = JSON.stringify(removed)
            updateEmptyMessage()
        })
    })
}

pdf_input.addEventListener("change", () => {
    managedFiles.push(...pdf_input.files)
    syncInputFiles()
    renderFileList()
})

updateEmptyMessage()
