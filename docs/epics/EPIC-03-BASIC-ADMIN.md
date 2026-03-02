# Epic 3: Basic EasyAdmin CRUD

**Branch**: `epic/03-easyadmin`
**Status**: ✅ Complete
**Estimated Effort**: 2-3 hours
**Dependencies**: Epic 2 (Entity Layer)

**Git Tag After Completion**: `step-1-base` ⭐ **TALK STARTS HERE**

---

## Goal

Set up EasyAdmin with basic CRUD controllers for all entities. This creates the starting point for the talk demonstration.

---

## Stories

### Story 3.1: Create Dashboard Controller

**Tasks**:
- [x] Generate dashboard
- [x] Configure dashboard title and menu
- [x] Set default page (redirects to Sheet index)
- [x] Add menu sections
- [x] Configure dashboard layout (`renderContentMaximized`, form themes, pagination)

**Acceptance Criteria**:
- [x] Dashboard controller created
- [x] Menu configured with all entities (Organization N/A — entity dropped)
- [x] Menu organized into logical sections
- [x] Dashboard accessible at `/admin`

**Deliverables**:
- [x] `src/Controller/Admin/DashboardController.php`

---

### Story 3.2: Create Organization CRUD Controller

> ⚠️ **N/A** — `Organization` entity was not created in the entity layer (Epic 2 deviated from plan). Skipped.

---

### Story 3.3: Create Person CRUD Controller

> ℹ️ `type` is not a property of Person — a person's role depends on context (sheet credit). Handled via `PersonType` entity on `CreditedPerson` instead. `Organization` entity was dropped.

**Tasks**:
- [x] Generate CRUD controller
- [x] Configure fields (name)
- [x] Person type handled via PersonType entity on CreditedPerson
- [x] Configure filters (TextFilter on name)
- [x] Configure search (name)

**Acceptance Criteria**:
- [x] CRUD controller created
- [x] Person type as configurable entity (`PersonTypeCrudController` added to menu)
- [x] Filters configured
- [x] Search enabled

**Deliverables**:
- [x] `src/Controller/Admin/PersonCrudController.php`
- [x] `src/Controller/Admin/PersonTypeCrudController.php`

---

### Story 3.4: Create Sheet CRUD Controller

> ℹ️ Entity deviated from plan: no `genre`, `difficulty`, `status`, `composer`, `arranger` fields. Has `tags`, `refs`, `files` (multi-PDF) instead. Controller adapted accordingly.

**Tasks**:
- [x] Generate CRUD controller
- [x] Configure all text fields
- [x] Configure credits (Person + PersonType) as inline table collection
- [x] Configure file uploads (PDF via custom PDFField)
- [x] Configure refs as custom ChoiceAutoCompleteStringField
- [x] Add filters (TextFilter on title) and search (title, tags)

**Acceptance Criteria**:
- [x] CRUD controller created
- [x] Fields configured (adapted to actual entity)
- [x] File uploads working (custom multi-PDF field)
- [x] Credits (Person + PersonType) as inline table via CollectionTableField
- [x] Filters and search configured

**Deliverables**:
- [x] `src/Controller/Admin/SheetCrudController.php`
- [x] `src/Controller/Admin/CreditedPersonCrudController.php`

---

### Story 3.5: Create Setlist CRUD Controller

**Tasks**:
- [x] Generate CRUD controller
- [x] Configure fields (title, date)
- [x] Configure date field with default sort DESC
- [x] Inline SetlistItem collection as table (enhancement over plan)
- [x] Position auto-assigned server-side from row order
- [x] Configure filters (TextFilter on title, DateTimeFilter on date) and search (title)

**Acceptance Criteria**:
- [x] CRUD controller created
- [x] Fields configured (title, date)
- [x] Inline SetlistItem collection as table
- [x] Filters and search configured

**Deliverables**:
- [x] `src/Controller/Admin/SetlistCrudController.php`

---

### Story 3.6: Create SetlistItem CRUD Controller

> ℹ️ **Implemented differently**: not exposed as a standalone menu entry. Used exclusively as the entry form config for the inline table collection in SetlistCrudController via `useEntryCrudForm()`.

**Acceptance Criteria**:
- [x] Controller created (technical, not in menu)
- [x] Associations configured (sheet)
- [x] Position auto-assigned server-side from row order

**Deliverables**:
- [x] `src/Controller/Admin/SetlistItemCrudController.php`

---

### Story 3.7: Create Member CRUD Controller

**Tasks**:
- [x] Generate CRUD controller
- [x] Configure fields (name, email, role)
- [x] Configure password field (plainPassword, hashed via MemberPasswordHasherSubscriber)
- [x] Single role per member as MemberRole enum (Member/Contributor/Librarian/Admin)
- [x] Configure filters (TextFilter on name/email, ChoiceFilter on role) and search (name, email)

**Acceptance Criteria**:
- [x] CRUD controller created
- [x] Password hashed on save (MemberPasswordHasherSubscriber)
- [x] Role configured as single-choice MemberRole enum
- [x] Email field configured
- [x] Filters and search configured

**Deliverables**:
- [x] `src/Controller/Admin/MemberCrudController.php`
- [x] `src/EventSubscriber/MemberPasswordHasherSubscriber.php`
- [x] `src/Enum/MemberRole.php`

---

### Story 3.8: Configure Menu & Navigation

**Tasks**:
- [x] Organize menu into logical sections (Administration, Partitions, Performances)
- [x] Add Font Awesome icons to menu items

**Acceptance Criteria**:
- [x] Menu organized into sections
- [x] Icons added to all menu items
- [x] Menu items navigate correctly

**Deliverables**:
- [x] Enhanced DashboardController

---

### Story 3.9: Basic Styling & UX Polish

**Tasks**:
- [x] Set entity labels (singular/plural) on all controllers
- [x] Configure default page size (25, globally in DashboardController)
- [x] `renderContentMaximized()` enabled globally
- [x] Form themes configured

**Acceptance Criteria**:
- [x] Entity labels configured on all controllers
- [x] `renderContentMaximized()` enabled globally
- [x] Form themes configured
- [x] Default pagination configured

**Deliverables**:
- [x] Polished CRUD controllers
- [x] Enhanced dashboard

---

## Epic Acceptance Criteria

- [x] Dashboard controller created and configured
- [x] All relevant CRUD controllers created (Organization N/A — entity dropped)
- [x] Menu organized with icons
- [x] All entities can be created, read, updated, deleted
- [x] File uploads working (PDF)
- [x] Credits (Person + PersonType) working as inline table on Sheet
- [x] Search and filters working on all entities
- [x] Passwords hashed for members (MemberPasswordHasherSubscriber)
- [x] Clean, professional UI
- [x] Smoke tested
- [x] Ready for demo/talk

---

## Next Epic

**Epic 4**: Authentication & Security Layer
- OR -
**Epic 7**: Custom Filters (if jumping to live-coding features first)
