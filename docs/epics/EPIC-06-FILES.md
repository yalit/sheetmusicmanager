# Epic 6: File & Image Handling

**Branch**: `epic/06-files` (implemented in `epic/03-easyadmin`)
**Status**: ✅ Complete
**Dependencies**: Epic 3 (Basic Admin)

---

## Goal

PDF file upload, storage and management for the Sheet entity.

---

## Stories

### Story 6.1: File Upload Strategy ✅

Custom implementation chosen over VichUploaderBundle.

- `src/Admin/Fields/PDFField.php` — EasyAdmin field factory
- `src/Admin/Type/SheetFileType.php` — thin form type (extends FileType, multiple PDFs, no transformer)
- Upload destination configured via `SHEET_UPLOAD_DIR` env var (default `public/uploads/sheets/`)

---

### Story 6.2: Sheet PDF Upload ✅

- `Sheet.files` — `SIMPLE_ARRAY` of filenames (display/index)
- `Sheet.uploadedFiles` — transient `array` of `UploadedFile` (form binding)
- `PDFField::new('files')` for index/detail; `PDFField::new('uploadedFiles')` for forms
- File I/O handled in `SheetCrudController::persistEntity()` / `updateEntity()`
- Multiple files supported; `accept="application/pdf"` enforced client-side

---

### Story 6.3: Sheet Cover Image 🚫 Dropped

Not a requirement for the demo.

---

### Story 6.4: Organisation Logo 🚫 Dropped

Organisation entity deferred with Epic 5.

---

### Story 6.5: Custom Display Templates ✅

`templates/admin/fields/pdf.html.twig` — lists filenames on index/detail.

---

### Story 6.6: File Validation ✅

Standard Symfony constraint on the transient `Sheet::$uploadedFiles` property:

- `#[Assert\All([new Assert\File(maxSize: '10M', mimeTypes: ['application/pdf'])])]`
- Validated by the form framework at submit time — no file is moved to disk if validation fails
- Covered by `tests/Entity/SheetUploadValidationTest.php`

---

### Story 6.7: File Deletion ✅

Handled in `SheetCrudController::updateEntity()` via hidden fields
`app_pdf_field_kept` and `app_pdf_field_removed`. Removed files are deleted from
disk during the update.

---

## Next Epic

**Epic 7**: Custom Filters (LIVE CODING)
