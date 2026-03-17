# Manual Testing Checklist

Pre-talk validation checklist. Load fixtures first:

```bash
php bin/console doctrine:fixtures:load
```

---

## Login credentials

| Role | Email | Password |
|---|---|---|
| Member | member@sheetmusic.test | password |
| Contributor | contributor@sheetmusic.test | password |
| Librarian | librarian@sheetmusic.test | password |
| Admin | admin@sheetmusic.test | password |

---

## Sheets

### CRUD

- [ ] **Member** — can view sheet index and sheet edit form (read-only fields)
- [ ] **Member** — no "New" button, no Edit/Delete row actions
- [ ] **Contributor** — can open and save a sheet edit form
- [ ] **Contributor** — no "New" button, no Delete row action
- [ ] **Librarian** — can create a new sheet (New button visible)
- [ ] **Librarian** — can delete a sheet (Delete row action visible)
- [ ] **Admin** — same permissions as Librarian

### Inline credits (CreditedPerson)

- [ ] On sheet edit form, credits table is visible
- [ ] Can add a new credit row (person + type)
- [ ] Can delete an existing credit row
- [ ] Save persists credits correctly

### Search and filters

- [ ] Search by title returns matching sheets
- [ ] Search by ref (e.g. `BWV565`) returns exactly 1 result
- [ ] Search by notes keyword returns matching sheets
- [ ] "Has PDF" filter set to Yes returns only sheets with files
- [ ] "Has PDF" filter set to No returns only sheets without files
- [ ] Title text filter narrows results

### CSV export

- [ ] "Export CSV" global button is visible for all roles
- [ ] Clicking it downloads a `.csv` file named `sheets-YYYY-MM-DD.csv`
- [ ] CSV contains header row: ID, Title, Refs, Tags, Credits, Files, Created at
- [ ] CSV rows match the current filtered/searched result set

### Batch: Add to setlist

- [ ] Select multiple sheets → "Add to setlist" batch action appears
- [ ] Submitting the form adds the selected sheets to the chosen setlist
- [ ] Duplicate sheets are not added twice

---

## Setlists

### CRUD

- [ ] **Member** — can view setlist index and create a new setlist
- [ ] **Member** — cannot edit or delete another member's setlist
- [ ] **Contributor** — can edit and delete their own setlist
- [ ] **Contributor** — cannot edit or delete another member's setlist
- [ ] **Admin** — can edit and delete any setlist

### Setlist items (inline)

- [ ] On setlist edit form, items table is visible with position, sheet, and name columns
- [ ] Can add a new item row (sheet selector + name)
- [ ] Can delete an existing item row
- [ ] Save persists items with correct positions

### Drag-and-drop reorder

- [ ] Drag handle (≡) is visible on each item row
- [ ] Dragging a row to a new position reorders the list visually
- [ ] Saving after reorder persists the new positions in the correct order

### Filters

- [ ] Title text filter narrows setlist results
- [ ] Date range filter returns setlists within the selected range

### Custom actions

- [ ] **Duplicate** — creates a copy of the setlist with all its items; new setlist appears in index
- [ ] **Export PDF** — downloads a PDF with setlist metadata (title, date, items)
- [ ] **Export partitions** — merges all sheet PDFs from the setlist into one downloadable PDF

---

## Persons

- [ ] **Member** — can view person index (read-only)
- [ ] **Librarian** — can create, edit, and delete persons
- [ ] Name search filter works

---

## Person types

- [ ] **Member** — can view person type index
- [ ] **Librarian** — can create, edit, and delete person types

---

## Members (admin only)

- [ ] **Member / Contributor / Librarian** — no "Members" menu item visible
- [ ] **Admin** — can view, create, edit, and delete members
- [ ] Role filter dropdown works
- [ ] Name and email text filters work

---

## Access control summary

| Action | Member | Contributor | Librarian | Admin |
|---|---|---|---|---|
| View sheets | yes | yes | yes | yes |
| Edit sheets | no | yes | yes | yes |
| Create/delete sheets | no | no | yes | yes |
| View setlists | yes | yes | yes | yes |
| Create setlists | yes | yes | yes | yes |
| Edit/delete own setlist | no | yes | yes | yes |
| Edit/delete any setlist | no | no | no | yes |
| View/manage persons | view only | view only | full | full |
| Manage members | no | no | no | yes |
