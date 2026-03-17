# Implementation Epics & User Stories

## Branch Strategy Overview

```
main (production-ready, complete demo)
  ├── epic/01-setup (fresh Symfony install)
  ├── epic/02-entities (entities + migrations)
  ├── epic/03-basic-admin (basic EasyAdmin CRUD)
  │
  ├── step-1-base (TALK STARTS HERE) ← Tag for talk starting point
  │   - Basic entities working
  │   - Basic CRUD operational
  │   - Demo data seeded
  │   - Clean, ready to live code
  │
  ├── step-2-custom-filters (AFTER LIVE CODING #1)
  │   - Custom filter implemented
  │   - Safety net if live coding fails
  │
  ├── step-3-custom-actions (AFTER LIVE CODING #2)
  │   - Custom action implemented
  │   - Safety net if live coding fails
  │
  └── step-4-complete (ALL FEATURES) ← Walkthrough starts here
      - All advanced features pre-built
      - Used for walkthroughs in talk
```

---

## Epic Breakdown

### Epic 0: Planning & Documentation
**Goal**: Complete project planning and documentation
**Branch**: `epic/00-planning`
**Status**: ✅ COMPLETE

#### Stories:
- [x] Brainstorm application concept
- [x] Define entity model
- [x] Map EasyAdmin features to implementation
- [x] Plan talk structure (30 min hybrid format)
- [x] Create epics and stories
- [x] Define branch strategy

**Deliverables**:
- `docs/TALK_PLANNING.md`
- `docs/ENTITY_MODEL.md`
- `docs/FEATURE_DEMONSTRATIONS.md`
- `docs/EPICS_AND_STORIES.md`

---

### Epic 1: Project Setup & Foundation
**Goal**: Fresh Symfony installation with all dependencies
**Branch**: `epic/01-setup`
**Talk Relevance**: Pre-talk setup, not shown in detail

#### Stories:
- [ ] **Story 1.1**: Install Symfony 7.x
  - Initialize new Symfony project
  - Verify installation works
  - Configure `.env` file

- [ ] **Story 1.2**: Install Core Dependencies
  - Doctrine ORM
  - EasyAdmin Bundle
  - Maker Bundle
  - Security Bundle
  - Form component

- [ ] **Story 1.3**: Install Doctrine Extensions
  - Install `stof/doctrine-extensions-bundle`
  - Configure Timestampable behavior
  - Configure Blameable behavior
  - Configure for future Loggable extension

- [ ] **Story 1.4**: Configure Database
  - Set up MySQL/PostgreSQL connection
  - Create database
  - Verify connection

- [ ] **Story 1.5**: Set Up Asset Management
  - Choose: AssetMapper vs Webpack Encore
  - Install and configure
  - Verify assets compile

- [ ] **Story 1.6**: Install JS Dependencies
  - Stimulus JS (for controllers)
  - SortableJS (for drag-and-drop)
  - Configure Stimulus bridge

- [ ] **Story 1.7**: Project Structure Setup
  - Create directory structure (`src/Entity`, `src/Controller/Admin`, etc.)
  - Configure namespaces
  - Set up templates directory structure

- [ ] **Story 1.8**: Git & Version Control
  - Initialize git repository
  - Create `.gitignore`
  - Initial commit

**Acceptance Criteria**:
- Symfony welcome page loads
- Database connection works
- All bundles installed and configured
- Assets compile successfully
- Git repository initialized

**Deliverables**:
- Working Symfony installation
- All dependencies configured
- Clean git history

---

### Epic 2: Entity Layer & Database
**Goal**: Create all entities with proper relationships and Doctrine configuration
**Branch**: `epic/02-entities`
**Talk Relevance**: Quick overview in talk (1 minute)

#### Stories:
- [ ] **Story 2.1**: Create Organization Entity
  - Fields: name, type, logo
  - Timestampable + Blameable traits
  - Relationships: One-to-Many (Person, Sheet, Setlist, Member)

- [ ] **Story 2.2**: Create Person Entity
  - Fields: name, type (enum)
  - Timestampable + Blameable traits
  - Relationships: Many-to-One (Organization), One-to-Many (Sheets as composer/arranger)

- [ ] **Story 2.3**: Create Sheet Entity
  - Fields: title, genre, difficulty, duration, key_signature, status, notes
  - File fields: pdf_file, cover_image
  - JSON field: references (SheetReference DTO array)
  - Timestampable + Blameable traits
  - Relationships: Many-to-One (Organization, Person x2), One-to-Many (SetlistItem)

- [ ] **Story 2.4**: Create SheetReference DTO
  - Properties: reference_code, reference_type
  - Not a database entity (embedded in Sheet)
  - Serialization for JSON storage

- [ ] **Story 2.5**: Create Setlist Entity
  - Fields: name, event_date, occasion, status, notes
  - Timestampable + Blameable traits
  - Relationships: Many-to-One (Organization), One-to-Many (SetlistItem)

- [ ] **Story 2.6**: Create SetlistItem Entity
  - Fields: position, name, notes
  - Timestampable trait only
  - Relationships: Many-to-One (Setlist, Sheet)
  - Unique constraint: (setlist_id, position)

- [ ] **Story 2.7**: Create Member Entity (User)
  - Fields: name, email, password, roles
  - Implements UserInterface
  - Timestampable trait
  - Relationships: Many-to-One (Organization)

- [ ] **Story 2.8**: Create Enums
  - OrganizationType (choir, band, orchestra)
  - PersonType (composer, arranger, both)
  - SheetDifficulty (beginner, intermediate, advanced)
  - SheetStatus (active, archived)
  - SetlistStatus (draft, finalized, performed)

- [ ] **Story 2.9**: Create Doctrine Migrations
  - Generate migrations for all entities
  - Review and adjust migrations
  - Add indexes for performance
  - Run migrations

- [ ] **Story 2.10**: Create Repositories
  - Custom repository methods for specific queries
  - `SheetRepository::findUnusedSheets()`
  - `PersonRepository::findMostUsedComposer()`
  - `SheetRepository::findByDifficultyAndGenre()`

**Acceptance Criteria**:
- All entities created with correct fields
- All relationships properly mapped
- Migrations run without errors
- Database schema matches entity definitions
- Repository methods return expected results

**Deliverables**:
- 7 entity classes + 1 DTO
- 5 enum classes
- Doctrine migrations
- Custom repository methods

---

### Epic 3: Basic EasyAdmin CRUD
**Goal**: Set up EasyAdmin with basic CRUD for all entities
**Branch**: `epic/03-basic-admin`
**Talk Relevance**: Starting point for talk demo (3-8 min section)

#### Stories:
- [ ] **Story 3.1**: Create Dashboard Controller
  - Generate EasyAdmin dashboard
  - Configure menu items for all entities
  - Set up dashboard layout

- [ ] **Story 3.2**: Create Organization CRUD
  - Basic CRUD controller
  - Configure fields (name, type, logo)
  - List view + form view

- [ ] **Story 3.3**: Create Person CRUD
  - Basic CRUD controller
  - Configure fields (name, type)
  - Show organization relationship
  - Filter by type

- [ ] **Story 3.4**: Create Sheet CRUD
  - Basic CRUD controller
  - Configure all fields
  - Show composer/arranger associations
  - Basic file upload for PDF and cover image
  - Display references field (simple)

- [ ] **Story 3.5**: Create Setlist CRUD
  - Basic CRUD controller
  - Configure fields (name, date, occasion, status)
  - Show organization relationship

- [ ] **Story 3.6**: Create SetlistItem CRUD
  - Basic CRUD controller
  - Configure fields (position, name, notes)
  - Show setlist and sheet associations
  - Order by position

- [ ] **Story 3.7**: Create Member CRUD
  - Basic CRUD controller
  - Configure fields (name, email, roles)
  - Password handling (hash on save)
  - Show organization relationship

- [ ] **Story 3.8**: Configure Menu & Navigation
  - Organize menu sections
  - Add icons to menu items
  - Set default page

- [ ] **Story 3.9**: Basic Styling & UX
  - Set EasyAdmin theme
  - Configure page titles
  - Set up breadcrumbs

**Acceptance Criteria**:
- All entities have CRUD interfaces
- Can create, read, update, delete all entities
- Relationships display correctly
- Forms validate properly
- Navigation works smoothly

**Deliverables**:
- Dashboard controller
- 7 CRUD controllers
- Basic admin interface working

**Git Tag**: `step-1-base` (TALK STARTS HERE)

---

### Epic 4: Authentication & Security Layer
**Goal**: Implement authentication, authorization, and role-based access
**Branch**: `epic/04-security`
**Talk Relevance**: Demonstrated in walkthrough (15-26 min)

#### Stories:
- [ ] **Story 4.1**: Configure Security Bundle
  - Set up `security.yaml`
  - Configure password hashing (bcrypt/argon2)
  - Set up user provider (Member entity)

- [ ] **Story 4.2**: Create Login System
  - Login form
  - Authentication controller
  - Remember me functionality
  - Logout route

- [ ] **Story 4.3**: Create Role Hierarchy
  - ROLE_MEMBER (base role)
  - ROLE_LIBRARIAN (can manage sheets)
  - ROLE_CONDUCTOR (can manage sheets + setlists)
  - ROLE_ADMIN (full access)

- [ ] **Story 4.4**: Implement EasyAdmin Permission System
  - Configure permissions on CRUD actions
  - Sheet: NEW/EDIT requires ROLE_LIBRARIAN
  - Setlist: NEW/EDIT requires ROLE_CONDUCTOR
  - Delete requires ROLE_ADMIN

- [ ] **Story 4.5**: Create Voters
  - SheetVoter: organization-scoped access
  - SetlistVoter: organization-scoped + status-based access
  - Check user belongs to same organization

- [ ] **Story 4.6**: Protect Custom Actions
  - Add role checks to custom actions
  - Display actions conditionally based on roles

- [ ] **Story 4.7**: Create Test Users
  - One user per role type
  - All belonging to different organizations
  - Seed via fixtures

**Acceptance Criteria**:
- Login/logout works
- Roles correctly restrict access
- Users only see their organization's data
- Voters enforce business rules
- Test users can demonstrate different access levels

**Deliverables**:
- Configured security system
- Login/logout functionality
- Role hierarchy
- Voters for entity access
- Test users for demo

---

### Epic 5: Multi-Tenancy Implementation
**Goal**: Implement organization-based data isolation
**Branch**: `epic/05-multi-tenancy`
**Talk Relevance**: Demonstrated in walkthrough (15-26 min)

#### Stories:
- [ ] **Story 5.1**: Create Query Extension for Organization Filtering
  - Doctrine extension to auto-filter by organization
  - Apply to Sheet, Setlist, Person entities
  - Get current user's organization

- [ ] **Story 5.2**: Create Entity Listener for Auto-Setting Organization
  - Listen to prePersist event
  - Auto-set organization on new entities
  - Get from current authenticated user

- [ ] **Story 5.3**: Update CRUD Controllers
  - Hide organization field from forms (auto-set)
  - Show organization in list/detail views
  - Ensure queries respect organization scope

- [ ] **Story 5.4**: Test Multi-Tenancy
  - Create entities as User A → visible to User A only
  - Switch to User B → can't see User A's data
  - Verify no data leakage

- [ ] **Story 5.5**: Add Organization Switcher (Admin Only)
  - Optional: admin can impersonate other organizations
  - For demo purposes during talk

**Acceptance Criteria**:
- All queries automatically filtered by organization
- Users can only see their organization's data
- New entities automatically assigned to user's organization
- No SQL injection or data leakage possible
- Multi-tenancy works across all CRUD operations

**Deliverables**:
- Query extension for organization scoping
- Entity listener for auto-setting
- Updated CRUD controllers
- Test scenarios proving isolation

---

### Epic 6: File & Image Handling
**Goal**: Implement file uploads for PDFs and images
**Branch**: `epic/06-files`
**Talk Relevance**: Demonstrated in walkthrough (15-26 min)

#### Stories:
- [ ] **Story 6.1**: Choose File Upload Strategy
  - Option A: VichUploaderBundle
  - Option B: Custom upload handling
  - Implement chosen approach

- [ ] **Story 6.2**: Configure Sheet PDF Upload
  - Upload directory: `public/uploads/sheets/`
  - Allowed: PDF only, max 10MB
  - Random filename generation
  - Download link in admin

- [ ] **Story 6.3**: Configure Sheet Cover Image Upload
  - Upload directory: `public/uploads/covers/`
  - Allowed: JPG, PNG, max 2MB
  - Generate thumbnail
  - Preview in admin

- [ ] **Story 6.4**: Configure Organization Logo Upload
  - Upload directory: `public/uploads/logos/`
  - Allowed: JPG, PNG, max 1MB
  - Display in dashboard header

- [ ] **Story 6.5**: Create Custom Field Templates
  - PDF download link template
  - Image preview template
  - File upload widget

- [ ] **Story 6.6**: Add Validation
  - File type validation
  - File size validation
  - Error messages

- [ ] **Story 6.7**: Handle File Deletion
  - Delete physical file when entity deleted
  - Delete old file when uploading new one

**Acceptance Criteria**:
- Can upload PDF files for sheets
- Can upload cover images for sheets
- Can upload logos for organizations
- Files stored in correct directories
- Thumbnails generated for images
- Download links work correctly
- Validation prevents invalid uploads

**Deliverables**:
- File upload configuration
- Custom field templates
- Validation rules
- File deletion handling

---

### Epic 7: Custom Filters (LIVE CODING)
**Goal**: Implement custom filter extensions
**Branch**: `epic/07-filters`
**Talk Relevance**: LIVE CODED (8-15 min) + Safety net

#### Stories:
- [ ] **Story 7.1**: Create "Has PDF" Filter (Pre-built)
  - Simple filter checking if pdfFile IS NOT NULL
  - Add to Sheet CRUD controller
  - Test filter works

- [ ] **Story 7.2**: Prepare for Live Coding (Difficulty + Status Filter)
  - Create empty filter class file (template ready)
  - Have documentation open
  - Rehearse typing the implementation
  - Know which methods to implement

- [ ] **Story 7.3**: Implement Difficulty + Status Filter (Live Coded)
  - **THIS WILL BE LIVE CODED DURING TALK**
  - Create custom filter class
  - Implement `apply()` method
  - Add form fields for difficulty and status
  - Register in Sheet CRUD controller

- [ ] **Story 7.4**: Test & Polish Filter
  - Test filter with various combinations
  - Style filter form if needed
  - Add placeholder text

**Acceptance Criteria**:
- "Has PDF" filter works correctly
- Difficulty + Status filter can be live coded in 3-4 minutes
- Filter applies correct WHERE clauses
- Form displays properly
- Results filter correctly

**Deliverables**:
- HasPdfFilter class (pre-built)
- DifficultyStatusFilter class (template + complete version)
- Updated Sheet CRUD controller

**Git Tag**: `step-2-custom-filters` (SAFETY NET after live coding)

---

### Epic 8: Custom Actions (LIVE CODING)
**Goal**: Implement custom actions on entities
**Branch**: `epic/08-actions`
**Talk Relevance**: LIVE CODED (8-15 min) + Walkthrough (15-26 min)

#### Stories:
- [ ] **Story 8.1**: Prepare "Add to Setlist" Action for Live Coding
  - Create action template
  - Prepare modal form
  - Rehearse implementation

- [ ] **Story 8.2**: Implement "Add to Setlist" Batch Action (Live Coded)
  - **THIS WILL BE LIVE CODED DURING TALK**
  - Create action class
  - Implement batch action logic
  - Show modal with setlist selection
  - Add selected sheets as SetlistItems
  - Success message

- [ ] **Story 8.3**: Implement "Archive Sheets" Batch Action (Pre-built)
  - Batch action to set status = 'archived'
  - Confirmation modal
  - Success message

- [ ] **Story 8.4**: Implement "Generate Setlist PDF" Action (Pre-built)
  - Single entity action on Setlist
  - Generate PDF with all sheets
  - Download response
  - Only show if status = 'finalized'

- [ ] **Story 8.5**: Implement "Mark as Performed" Action (Pre-built)
  - Single entity action on Setlist
  - Change status from 'finalized' to 'performed'
  - Confirmation modal
  - Only show if status = 'finalized'

- [ ] **Story 8.6**: Implement "Duplicate Setlist" Action (Pre-built)
  - Clone setlist with all items
  - Change name to "[Original] - Copy"
  - Set status to 'draft'

- [ ] **Story 8.7**: Implement "Preview Sheet" Action (Pre-built)
  - Opens PDF in new tab
  - Only show if PDF file exists

**Acceptance Criteria**:
- "Add to Setlist" can be live coded in 3-4 minutes
- All actions work correctly
- Actions display conditionally
- Modals work properly
- Success/error messages display

**Deliverables**:
- AddToSetlistAction (template + complete)
- ArchiveSheetsAction
- GeneratePdfAction
- MarkAsPerformedAction
- DuplicateSetlistAction
- PreviewSheetAction

**Git Tag**: `step-3-custom-actions` (SAFETY NET after live coding)

---

### Epic 9: Custom Fields & Form Extensions
**Goal**: Implement custom field types and form layouts
**Branch**: `epic/09-custom-fields`
**Talk Relevance**: Demonstrated in walkthrough (15-26 min)

#### Stories:
- [ ] **Story 9.1**: Create SheetReference Form Type
  - Custom form type for DTO
  - Fields: reference_code, reference_type
  - Choice field for reference_type

- [ ] **Story 9.2**: Implement SheetReference Collection Field
  - CollectionField in Sheet CRUD
  - Allow add/remove items
  - Store as JSON in database
  - Display in list view

- [ ] **Story 9.3**: Create Custom Field Template for Difficulty
  - Visual representation (stars or badges)
  - Color coding (green/yellow/red)

- [ ] **Story 9.4**: Customize Sheet Form Layout
  - Group fields into sections
  - Conditional fields (show key_signature only if advanced)
  - Help text and placeholders

- [ ] **Story 9.5**: Customize Setlist Form Layout
  - Date picker for event_date
  - Status with visual indicators

**Acceptance Criteria**:
- SheetReference collection works (add/remove)
- References save as JSON correctly
- Custom fields display properly
- Form layouts are intuitive
- Conditional fields work

**Deliverables**:
- SheetReferenceType form class
- Custom field templates
- Customized form layouts

---

### Epic 10: JavaScript Integration
**Goal**: Implement both embedded JS and Stimulus controllers
**Branch**: `epic/10-javascript`
**Talk Relevance**: Demonstrated in walkthrough (15-26 min)

#### Stories:
- [ ] **Story 10.1**: Implement Duration Auto-Format (Embedded JS)
  - Custom template for duration field
  - Inline JavaScript for formatting
  - Convert "3.5" → "3:30"
  - Convert "3" → "3:00"
  - Trigger on blur event

- [ ] **Story 10.2**: Create Sortable Stimulus Controller
  - New Stimulus controller: `sortable_controller.js`
  - Integrate SortableJS library
  - Handle drag events
  - Send position updates via AJAX

- [ ] **Story 10.3**: Implement Drag-and-Drop for Setlist Items
  - Custom template for setlist items list
  - Add Stimulus controller attributes
  - Drag handles on each item
  - Visual feedback during drag

- [ ] **Story 10.4**: Create Backend Endpoint for Reordering
  - Controller action: `reorderItems`
  - Receive position updates JSON
  - Update SetlistItem positions
  - Return success response

- [ ] **Story 10.5**: Add Toast Notifications
  - Show success message after reorder
  - Show error if AJAX fails
  - Simple toast component

- [ ] **Story 10.6**: Test JS Features
  - Test duration formatting in different browsers
  - Test drag-and-drop in different browsers
  - Test AJAX error handling

**Acceptance Criteria**:
- Duration field auto-formats on blur
- Setlist items can be reordered by dragging
- Position updates save to database
- Toast notifications display
- Works in Chrome, Firefox, Safari

**Deliverables**:
- Duration field custom template with embedded JS
- Sortable Stimulus controller
- Reorder endpoint
- Toast notification system

---

### Epic 11: Advanced Features
**Goal**: Implement export, specific queries, and other advanced features
**Branch**: `epic/11-advanced`
**Talk Relevance**: Demonstrated in walkthrough (15-26 min)

#### Stories:
- [ ] **Story 11.1**: Implement Export with Filters
  - Export action on Sheet list
  - Apply current filters to export
  - Generate CSV file
  - Download response

- [ ] **Story 11.2**: Create Dashboard Widgets
  - Total sheets count
  - Total setlists count
  - Most used composer (custom query)
  - Recent activity

- [ ] **Story 11.3**: Implement Specific Queries
  - "Unused Sheets" query (never in a setlist)
  - "Most Used Composer" query
  - "Sheets by Difficulty and Genre" query
  - Display results in dashboard or dedicated pages

- [ ] **Story 11.4**: Add Search Functionality
  - Global search across sheets
  - Search by title, composer, genre
  - Search by reference codes (JSON field)

- [ ] **Story 11.5**: Optimize Queries
  - Add database indexes
  - Eager loading for associations
  - Query result caching where appropriate

**Acceptance Criteria**:
- Export generates correct CSV with filtered data
- Dashboard shows useful statistics
- Specific queries return correct results
- Search works across multiple fields
- Performance is acceptable

**Deliverables**:
- Export action
- Dashboard widgets
- Custom query methods
- Search implementation
- Performance optimizations

---

### Epic 12: Demo Data & Testing
**Goal**: Create realistic demo data and test all features
**Branch**: `epic/12-demo-data`
**Talk Relevance**: Pre-talk setup, shown throughout demo

#### Stories:
- [ ] **Story 12.1**: Create Data Fixtures
  - 2 Organizations (choir + band)
  - 10-15 Persons (composers/arrangers)
  - 30-40 Sheets (various difficulties, genres)
  - 5-8 Setlists (different statuses)
  - 20-30 SetlistItems
  - 6 Members (different roles, organizations)

- [ ] **Story 12.2**: Create Realistic Sheet Data
  - Real composer names (Bach, Mozart, modern)
  - Real song titles
  - Realistic genres (Classical, Jazz, Pop, Sacred)
  - Mix of difficulties
  - Some with PDFs, some without

- [ ] **Story 12.3**: Create Realistic Setlist Data
  - "Christmas Concert 2025"
  - "Easter Sunday Mass"
  - "Jazz Night at the Club"
  - "Spring Recital"
  - Mix of draft/finalized/performed

- [ ] **Story 12.4**: Upload Sample Files
  - Sample PDF files for sheets
  - Sample cover images
  - Sample organization logos

- [ ] **Story 12.5**: Create Test Scenarios
  - User A (choir, ROLE_MEMBER)
  - User B (choir, ROLE_LIBRARIAN)
  - User C (choir, ROLE_CONDUCTOR)
  - User D (band, ROLE_ADMIN)
  - Test each user can/cannot do expected actions

- [ ] **Story 12.6**: Write Automated Tests (Optional)
  - Unit tests for repository methods
  - Functional tests for CRUD operations
  - Security tests for multi-tenancy

- [ ] **Story 12.7**: Manual Testing Checklist
  - Test all features work
  - Test all roles work correctly
  - Test multi-tenancy isolation
  - Test file uploads
  - Test JS features
  - Browser compatibility

**Acceptance Criteria**:
- Fixtures create realistic, varied data
- Demo data tells a story
- All test users demonstrate different access levels
- All features testable with demo data
- No errors during fixture loading

**Deliverables**:
- Fixture classes with demo data
- Sample PDF and image files
- Test user credentials document
- Manual testing checklist
- Optional: automated tests

**Git Tag**: `step-4-complete` (WALKTHROUGH STARTS HERE)

---

### Epic 13: Talk Preparation & Polish
**Goal**: Final polish, rehearsal, and talk preparation
**Branch**: `main`
**Talk Relevance**: Pre-talk preparation

#### Stories:
- [ ] **Story 13.1**: Create Slide Deck
  - Title slide
  - Problem/solution slides
  - Entity diagram slide
  - Feature demonstration slides
  - Resources/conclusion slide

- [ ] **Story 13.2**: Rehearse Live Coding Sections
  - Practice custom filter 5+ times
  - Practice custom action 5+ times
  - Time each section
  - Prepare backup if something fails

- [ ] **Story 13.3**: Verify All Git Branches/Tags
  - Test checking out each step
  - Ensure each step runs correctly
  - Verify data is seeded at each step

- [ ] **Story 13.4**: Prepare Demo Environment
  - Clean database
  - Fresh fixtures load
  - Browser windows pre-opened
  - IDE files bookmarked
  - Terminal commands ready

- [ ] **Story 13.5**: Create Backup Materials
  - Screenshots of each feature
  - GIF recordings of interactions
  - Code snippets in slides
  - Recorded video of full demo (worst case)

- [ ] **Story 13.6**: Test Full Talk Flow
  - Complete 30-minute run-through
  - Time each section
  - Adjust timing if needed
  - Test switching branches

- [ ] **Story 13.7**: Prepare Q&A Answers
  - Review anticipated questions
  - Prepare code examples for common questions
  - Know where to find documentation

- [ ] **Story 13.8**: Create Attendee Resources
  - README with setup instructions
  - Link to each git branch
  - Link to documentation
  - Contact info for questions

**Acceptance Criteria**:
- Can complete talk in 30 minutes
- Live coding sections smooth
- Branch switching works flawlessly
- Backup materials ready
- Attendee resources prepared

**Deliverables**:
- Slide deck
- Rehearsed talk flow
- Verified git branches
- Demo environment ready
- Backup materials
- Attendee resources

---

## Implementation Timeline Recommendations

### Phase 1: Core Foundation (Epics 1-3)
**Goal**: Get to `step-1-base` (talk starting point)
- Epic 1: Project Setup (1-2 hours)
- Epic 2: Entity Layer (3-4 hours)
- Epic 3: Basic Admin (2-3 hours)
**Total**: ~8 hours

### Phase 2: Live Coding Features (Epics 7-8)
**Goal**: Build features you'll live code, then rehearse
- Epic 7: Custom Filters (2-3 hours to build, 2-3 hours to rehearse)
- Epic 8: Custom Actions (3-4 hours to build, 2-3 hours to rehearse)
**Total**: ~12 hours

### Phase 3: Advanced Features (Epics 4-6, 9-11)
**Goal**: Pre-build all walkthrough features
- Epic 4: Security (3-4 hours)
- Epic 5: Multi-Tenancy (2-3 hours)
- Epic 6: Files (2-3 hours)
- Epic 9: Custom Fields (2-3 hours)
- Epic 10: JavaScript (3-4 hours)
- Epic 11: Advanced (3-4 hours)
**Total**: ~20 hours

### Phase 4: Demo Data & Testing (Epic 12)
**Goal**: Realistic data for compelling demo
- Epic 12: Demo Data & Testing (4-5 hours)
**Total**: ~5 hours

### Phase 5: Talk Preparation (Epic 13)
**Goal**: Polish and rehearse
- Epic 13: Talk Prep & Polish (6-8 hours)
**Total**: ~7 hours

**Grand Total**: ~52 hours of focused work

---

## Success Criteria

### Technical Success:
- [ ] All features implemented and working
- [ ] Multi-tenancy properly isolates data
- [ ] Security/roles work correctly
- [ ] File uploads work reliably
- [ ] JavaScript features work across browsers
- [ ] Demo data is realistic and compelling

### Talk Success:
- [ ] Can complete talk in 30 minutes
- [ ] Live coding sections are smooth (or can switch to backup)
- [ ] Walkthrough sections are clear and compelling
- [ ] Attendees understand EasyAdmin's power
- [ ] Repository is useful to attendees afterward

### Learning Outcomes for Attendees:
- [ ] Understand EasyAdmin basics
- [ ] See extension points (filters, actions, fields)
- [ ] Learn multi-tenancy pattern
- [ ] See real-world security implementation
- [ ] Get inspired to use EasyAdmin in their projects

---

## Notes

- Start with Epic 1-3 to get a working foundation
- Epics 7-8 are critical for live coding - practice extensively
- Epics 4-6, 9-11 can be done in any order
- Epic 12 should be done last (demo data)
- Epic 13 is all about rehearsal and polish
- Budget extra time for rehearsing live coding sections
- Create git tags at each major milestone for easy switching during talk
