# Epic 12: Demo Data & Testing

**Branch**: `epic/12-demo-data`
**Status**: ✅ Complete
**Dependencies**: All previous epics (1-11)

---

## Goal

Enrich existing fixtures with realistic demo data and document a manual testing checklist for the talk demonstration.

---

## Current State

All fixture classes already exist. The data is minimal (test-oriented):

| Fixture | Current state |
|---|---|
| `MemberFixtures` | 4 members, one per role, `role@sheetmusic.test` / `password` |
| `PersonTypeFixtures` | 3 types: Composer, Arranger, Conductor |
| `PersonFixtures` | 3 persons: Bach, Mozart, Brahms |
| `SheetFixtures` | 2 sheets: Toccata, Eine Kleine Nachtmusik |
| `SetlistFixtures` | 4 setlists (one per role), no `SetListItem`s |

---

## Stories

### Story 12.1: Enrich Person Fixtures

**Description**: Add more composers and arrangers to make the sheet credits feel realistic.

**Constraints**:
- `Person` has only `name` (string)
- `PersonType` (Composer, Arranger, Conductor) is a separate entity, already populated
- `CreditedPerson` links Person + PersonType + Sheet — no direct `type` on Person itself

**Target**: 10–15 persons covering classical and jazz repertoire.

**Deliverables**:
- Updated `src/DataFixtures/PersonFixtures.php`

---

### Story 12.2: Enrich Sheet Fixtures

**Description**: Expand from 2 to ~20 sheets with varied tags, refs, notes, and credits.

**Constraints**:
- `Sheet` has: `title`, `refs` (SIMPLE_ARRAY), `tags` (SIMPLE_ARRAY), `notes` (text), `files` (StoredFile[]), `credit` (Collection<CreditedPerson>)
- No `genre`, `difficulty`, `key`, `status` fields — do not invent them
- Existing `SHEETS` constant (`SHEETS[0][0]` = 'Toccata...', `SHEETS[1][0]` = 'Eine Kleine...') is used by tests — **must not be removed or reordered**
- Credits require persisting `CreditedPerson` entities (person + personType + sheet)

**Target**: ~20 sheets with varied tags, at least half with refs, several with notes and credits.

**Deliverables**:
- Updated `src/DataFixtures/SheetFixtures.php`

---

### Story 12.3: Add SetlistItem Fixtures

**Description**: Populate the existing setlists with `SetListItem`s linking to sheets.

**Constraints**:
- `SetListItem` has: `setlist`, `sheet`, `position` (int), `name` (string, optional), `notes` (text)
- `Setlist` has: `title`, `date` (\DateTime), `notes` (text)
- Existing setlists have no items and no dates — add dates and items
- `SetlistFixtures` depends on `MemberFixtures`; after this story it also depends on `SheetFixtures`

**Target**: Each setlist has 3–6 items with realistic positions and names.

**Deliverables**:
- Updated `src/DataFixtures/SetlistFixtures.php`

---

### Story 12.4: Manual Testing Checklist

**Description**: Create a concise checklist to validate all features before the talk.

**Target**: `docs/TESTING_CHECKLIST.md` covering:
- Login credentials (all 4 roles)
- CRUD operations per entity
- Role-based access (what each role can/cannot do)
- Custom actions (duplicate, PDF export, merge, add to setlist)
- CSV export and search/filter
- DnD reorder on setlist items

**Deliverables**:
- `docs/TESTING_CHECKLIST.md`

---

## Epic Acceptance Criteria

- [x] 10–15 persons in fixtures
- [x] ~20 sheets with varied data and credits
- [x] All setlists have items with positions
- [x] `bin/console doctrine:fixtures:load` runs without errors
- [x] Existing tests still pass after fixture changes
- [x] Testing checklist complete

---

## Next Epic

**Epic 13**: Talk Preparation & Polish
