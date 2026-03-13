# Epic 8: Custom Actions (LIVE CODING)

**Branch**: `epic/08-actions`
**Status**: ✅ Done (pending tag + live coding rehearsal)
**Dependencies**: Epic 3 (Basic Admin)

**Git Tag After Completion**: `step-3-custom-actions` 🔴 **LIVE CODING SAFETY NET**

---

## Goal

Implement custom EasyAdmin actions working with the actual entities:

- `Sheet` — `id`, `title`, `tags`, `refs`, `files`, `notes`, `credit` (OneToMany → CreditedPerson)
- `Setlist` — `id`, `title`, `date`, `notes`, `item` (OneToMany → SetListItem)
- `SetListItem` — `setlist`, `sheet`, `position`, `name`, `notes`

No status field, no organisation/multi-tenancy, no VichUploader.

The epic covers four actions:
- **"Duplicate Setlist"** — pre-built, single-entity action
- **"Generate Setlist PDF"** — pre-built, Chromium-rendered programme PDF via GotenbergBundle
- **"Merge Setlist Sheets PDF"** — pre-built, merges uploaded sheet files into one PDF via GotenbergBundle
- **"Add to Setlist"** — live-coded, batch action with intermediate form

All four follow the custom action pattern documented in `docs/patterns/actions.md`:
dedicated `XxxAction.php` (EA config) + `XxxController.php` (HTTP) + handler (business logic),
wired with `linkToRoute()` — no business logic inside CRUD controllers.

---

## Stories

### Story 8.1: Implement "Duplicate Setlist" Action ✅

**Description**: Single-entity action on a Setlist that clones it with all its items.

**Pattern**: Simple action (no user input).

**Implementation**:
- `src/Admin/Action/DuplicateSetlistAction.php` — EA config, `linkToRoute('admin_duplicate_setlist')`
- `src/Admin/Action/DuplicateSetlist.php` — DTO carrying the source `Setlist`
- `src/Admin/Action/DuplicateSetlistHandler.php` — clones via `SetlistFactory::clone()`, saves via repository
- `src/Controller/Action/DuplicateSetlistController.php` — resolves entity via ParamConverter, calls handler, redirects to edit
- `src/Entity/Factory/SetlistFactory.php` + `SetlistItemFactory.php` — entity construction and cloning

**Behaviour**:
- Cloned setlist copies title, notes, and all items with their positions
- Date is reset to today
- Redirects to the edit page of the new setlist with a success flash

**Registered in**: `SetlistCrudController::configureActions()` on `PAGE_INDEX` and `PAGE_DETAIL`

---

### Story 8.2: Implement "Generate Setlist PDF" Action ✅

**Description**: Single-entity action that generates a Chromium-rendered programme PDF
for a Setlist via GotenbergBundle.

**Pattern**: Simple action (no user input).

**Implementation**:
- `src/Admin/Action/GenerateSetlistPdfAction.php` — EA config, `linkToRoute('admin_generate_setlist_pdf')`
- `src/Controller/Action/GenerateSetlistPdfController.php` — streams the PDF response via `GotenbergPdfInterface::html()`
- `templates/admin/pdf/setlist.html.twig` — standalone HTML document (no Symfony base template), rendered by Gotenberg's headless Chrome

**Behaviour**:
- Downloads `setlist-{id}.pdf`
- PDF lists all setlist items in order (position, sheet title, item name)
- No crash on empty setlist (renders an empty table)

**Registered in**: `SetlistCrudController::configureActions()` on `PAGE_INDEX` and `PAGE_DETAIL`

---

### Story 8.3: Implement "Merge Setlist Sheets PDF" Action ✅

**Description**: Single-entity action that merges all uploaded sheet PDF files in a Setlist
into a single downloadable PDF via GotenbergBundle's merge API.

**Pattern**: Simple action (no user input).

**Implementation**:
- `src/Admin/Action/MergeSetlistSheetsPdfAction.php` — EA config, `linkToRoute('admin_merge_setlist_sheets_pdf')`
- `src/Controller/Action/MergeSetlistSheetsPdfController.php` — collects file paths via `SheetFileStorage`, calls `GotenbergPdfInterface::merge()`, streams result
- `src/Storage/SheetFileStorage.php` — centralises all file path resolution (extracted in a prior refactor)

**Behaviour**:
- Iterates items in setlist order; for each item collects all sheet files
- Skips files that are missing on disk (silently)
- If no files are found at all: adds a warning flash and redirects back to the edit page
- Otherwise downloads `partitions-{id}.pdf`

**Registered in**: `SetlistCrudController::configureActions()` on `PAGE_INDEX` and `PAGE_DETAIL`

---

### Story 8.4: Implement "Add to Setlist" Batch Action ✅

**Description**: Batch action on the Sheet index. Lets the user select sheets, then pick
a target Setlist via an intermediate form before confirming.

**Pattern**: Form + Symfony Messenger variant (needs user input before executing).

**Implementation**:
- `src/Admin/Action/AddSheetsToSetlistAction.php` — EA config, `linkToRoute('admin_add_to_setlist')`
- `src/Message/AddSheetsToSetlist.php` — Messenger Message DTO (`#[AsMessage]`), public `$setlist` + `$sheets[]` with Validator constraints
- `src/Message/Factory/AddSheetsToSetListFactory.php` — hydrates Message from raw `batchActionEntityIds[]`, resolves entities via `SheetRepository`
- `src/Form/AddSheetsToSetlistType.php` — `AbstractType` with `data_class = AddSheetsToSetlist`; setlist picker via `EntityType`, sheets via `HiddenType` + transformer
- `src/Form/DataTransformer/SheetToStringDataTransformer.php` — bridges `Sheet[]` ↔ JSON string for the hidden field
- `src/MessageHandler/AddSheetsToSetlistHandler.php` — `#[AsMessageHandler]`, appends items with sequential positions after existing ones
- `src/Controller/Action/AddToSetlistController.php` — two-POST flow: factory → form → `handleRequest`; on valid submit dispatches message and redirects to setlist edit
- `templates/admin/action/add_to_setlist.html.twig` — intermediate form rendered between the two POSTs

**Two-POST flow**:
1. EA batch action fires a POST with `batchActionEntityIds[]` → factory hydrates Message → form rendered with pre-populated hidden field
2. User picks a setlist and submits → `isSubmitted() && isValid()` → message dispatched → redirect to setlist edit

**Registered in**: `SheetCrudController::configureActions()` as a batch action on `PAGE_INDEX`

---

### Story 8.5: Document Custom Action Pattern ✅

**Deliverables**:
- `docs/patterns/actions.md` — full pattern documentation for both variants (simple and form + Messenger)

---

## Epic Acceptance Criteria

- [x] "Duplicate Setlist" action working on index and detail
- [x] "Generate Setlist PDF" action working end-to-end (requires Gotenberg Docker service)
- [x] "Merge Setlist Sheets PDF" action working end-to-end (requires Gotenberg Docker service)
- [x] "Add to Setlist" batch action working end-to-end with intermediate form
- [x] Custom action pattern documented (`docs/patterns/actions.md`)
- [ ] Safety net branch tagged as `step-3-custom-actions`
- [ ] All existing tests still pass
- [ ] Live coding rehearsed 5+ times, under 4 minutes

---

## Live Coding Rehearsal Checklist

```
Rehearsal 1: [ ] Time: ____ mins
Rehearsal 2: [ ] Time: ____ mins
Rehearsal 3: [ ] Time: ____ mins
Rehearsal 4: [ ] Time: ____ mins
Rehearsal 5: [ ] Time: ____ mins
```

---

## Next Epic

**Epic 9**: Custom Fields & Form Extensions
