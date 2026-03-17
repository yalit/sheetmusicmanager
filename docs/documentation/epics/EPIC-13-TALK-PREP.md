# Epic 13: Talk Preparation & Polish

**Branch**: `main`
**Status**: ⏳ Pending
**Dependencies**: All previous epics (1-12)

---

## Goal

Final preparation for the live talk: write attendee-facing documentation, verify the repo is presentable, and rehearse the demo flow.

---

## Stories

### Story 13.1: Write README for attendees

**Description**: The project has no root `README.md`. Attendees who clone the repo after the talk need a quick-start guide.

**Content to cover**:
- What the app is (sheet music manager for choirs/bands)
- Stack: Symfony 7, EasyAdmin 4, Doctrine ORM, importmap, plain JS
- Setup commands (`composer install`, migrations, fixtures, `symfony server:start`)
- Login credentials (all 4 roles)
- Brief description of what each epic branch demonstrates

**Constraints**:
- Branches follow the `epic/XX-name` naming convention — do NOT invent `step-1/2/3/4` names
- Credentials are: `role@sheetmusic.test` / `password` for member, contributor, librarian, admin

**Deliverables**:
- `README.md` at project root

---

### Story 13.2: Verify each epic branch is usable

**Description**: Each `epic/XX` branch should be self-contained enough for attendees to explore. At minimum, `composer install` + migrations + fixtures + `symfony server:start` should produce a working app.

**Branches to verify** (in order):
- `epic/01-setup`
- `epic/02-entities`
- `epic/03-easyadmin`
- `epic/04-authentication`
- `epic/07-filters`
- `epic/08-actions`
- `epic/09-custom-fields` (skipped if not merged to main)
- `epic/10-dnd-reorder`
- `epic/11-advanced`
- `main` (final state)

**For each branch**, verify:
- [ ] `composer install` succeeds
- [ ] `symfony console doctrine:migrations:migrate` succeeds
- [ ] `symfony console doctrine:fixtures:load` succeeds
- [ ] `/` loads and is usable

**Deliverables**:
- Completed verification checklist (update this doc)

---

### Story 13.3: Prepare demo flow notes

**Description**: A concise cheat-sheet for the live demo — what to show, in what order, with rough timings. Not a full script; just enough to avoid losing track during the talk.

**Suggested flow** (adjust to fit your talk slot):
1. Quick context — what the app does, the entities (2 min)
2. Basic EasyAdmin: CRUD, menu, search — `epic/03-easyadmin` (3 min)
3. Security & roles — show what member vs admin can do (2 min)
4. Custom filter (`HasPdfFilter`) — show the code, show it in action (3 min)
5. Custom actions — duplicate setlist, PDF export, batch add to setlist (4 min)
6. CollectionTableField — inline setlist items, custom form type (3 min)
7. Drag-and-drop reorder — live demo (2 min)
8. CSV export with filters — show it respects the active filter (2 min)
9. Wrap-up + Q&A (5 min)

**Deliverables**:
- `docs/DEMO_FLOW.md`

---

### Story 13.4: Pre-talk environment checklist

**Description**: A short checklist to run through one hour before the talk.

**Deliverables**:
- Section added to `docs/TESTING_CHECKLIST.md` (or a standalone `docs/PRE_TALK.md`)

---

## Epic Acceptance Criteria

- [ ] `README.md` written and accurate
- [ ] All epic branches verified working
- [ ] Demo flow notes written
- [ ] Pre-talk checklist written
- [ ] Fixtures reload cleanly on `main`
- [ ] All 194 tests pass on `main`

---

## Next Epic

None — this is the final epic before the talk.
