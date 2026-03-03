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
- `src/Admin/Type/SheetFileType.php` — form type (extends FileType, multiple PDFs)
- `src/DataTransformer/StringToFileTransformer.php` — `string[]` ↔ `File[]`
- Upload destination: `public/uploads/sheets/`

---

### Story 6.2: Sheet PDF Upload ✅

- `Sheet.files` — `SIMPLE_ARRAY` of filenames
- `PDFField::new('files')` used in `SheetCrudController`
- Multiple files supported
- `client-side: accept="application/pdf"`

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

Server-side validation added in `SheetFileType` via a `FormEvents::SUBMIT` listener:

- MIME type must be `application/pdf` (checked via `finfo`, not client header)
- Max size: 10 MB
- Invalid files are stripped from the event data **before** `reverseTransform()` runs,
  so nothing is written to disk when validation fails.

---

### Story 6.7: File Deletion ✅

Handled in `SheetCrudController::updateEntity()` via hidden fields
`app_pdf_field_kept` and `app_pdf_field_removed`. Removed files are deleted from
disk during the update.

---

## Next Epic

**Epic 7**: Custom Filters (LIVE CODING)
