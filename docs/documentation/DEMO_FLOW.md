# Demo Flow

Cheat-sheet for the live demo. Not a script — just enough to stay on track.

---

## 0. Before you start

- Fixtures loaded on `main`
- Browser open on `/admin`, logged out
- IDE open on `src/Controller/Admin/`

---

## 1. Context (2 min)

- What the app is: sheet music library for a choir/band
- Entities: Sheet, Person, Setlist, SetListItem, Member
- "Let's see what EasyAdmin gives us out of the box"

---

## 2. Basic CRUD — `epic/03-easyadmin` (3 min)

Login as **member**.

- Dashboard → Sheet index: columns, search, pagination
- Open a sheet: tags, refs, files, credits (inline table)
- Setlist index: items table
- Point out: zero custom templates so far

Switch to **admin**. Show the Members menu item (not visible as member).

---

## 3. Security & roles (2 min)

Stay on `epic/04-authentication` or `main`.

- As **member**: no New/Delete buttons on Sheets
- As **contributor**: Edit button appears
- As **librarian**: New + Delete appear
- As **admin**: Members CRUD visible

"All driven by Symfony voters — EasyAdmin just calls `isGranted()`."

---

## 4. Custom filter — `HasPdfFilter` (3 min)

Sheet index → filter panel → "Has PDF" toggle.

- Show: Yes returns sheets with files, No returns sheets without
- Open `src/Filter/HasPdfFilter.php`
- Point out: `FilterInterface` + `FilterTrait`, `apply()` adds the WHERE clause
- "That's all it takes — one class, one method"

---

## 5. Custom actions (4 min)

### Simple action — Duplicate setlist

- Setlist index → click Duplicate on a setlist
- New setlist appears instantly
- Open `src/Admin/Action/DuplicateSetlistAction.php` + `src/Admin/Action/DuplicateSetlistHandler.php`
- "Action config is separate from business logic"

### Batch action — Add sheets to setlist

- Sheet index → select several sheets → "Add to setlist" batch action
- Form appears: choose target setlist → submit
- Open `src/Controller/Action/AddSheetsToSetlistController.php`
- "Batch actions get a list of selected IDs — you handle the rest"

### PDF actions

- Setlist → "Export PDF" (metadata) and "Export partitions" (merged sheet PDFs)
- Mention Gotenberg — just an HTTP call, EasyAdmin doesn't care

---

## 6. CollectionTableField — inline editing (3 min)

Edit a Sheet → Credits table.

- Add a credit row (person + type), save
- Open `src/Admin/Fields/CollectionTableField.php`
- Open `templates/admin/form.html.twig` → `table_collection_widget` block
- "EasyAdmin's collection field renders a `<ul>` — we override the block to get a `<table>`"

Edit a Setlist → Items table (same pattern, different entity).

---

## 7. Drag-and-drop reorder (2 min)

Edit a setlist with several items.

- Drag a row to a new position — notice positions update visually
- Save — reload — order persisted
- Open `assets/admin/collection_table_sortable.js`
- "SortableJS + 20 lines of JS to reindex form field names on drop"
- Open `SetlistCrudController::syncItemPositions()` — "backend just reads DOM order"

---

## 8. CSV export with filter awareness (2 min)

Sheet index → apply the "Has PDF" filter → Export CSV.

- Open the CSV: only filtered sheets appear
- Open `src/Controller/Admin/SheetCrudController::export()`
- "Reuses `createIndexQueryBuilder()` — same query as the page, filters included"

---

## 9. Wrap-up (2 min)

- EasyAdmin handles the boilerplate; every extension point is a plain Symfony class
- The patterns shown (filters, actions, custom fields) compose cleanly
- Q&A
