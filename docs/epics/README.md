# Implementation Epics - Index

This directory contains detailed implementation stories for all 14 epics of the Sheet Music Manager project.

---

## Epic Overview

| Epic | Name | Effort | Status | Git Tag | Talk Phase |
|------|------|--------|--------|---------|------------|
| [Epic 0](./EPIC-00-PLANNING.md) | Planning & Documentation | 4-6h | ✅ Complete | - | Pre-work |
| [Epic 1](./EPIC-01-SETUP.md) | Project Setup & Foundation | 1-2h | ⏳ Pending | - | Pre-work |
| [Epic 2](./EPIC-02-ENTITIES.md) | Entity Layer & Database | 3-4h | ⏳ Pending | - | Pre-work |
| [Epic 3](./EPIC-03-BASIC-ADMIN.md) | Basic EasyAdmin CRUD | 2-3h | ⏳ Pending | `step-1-base` ⭐ | **TALK STARTS** |
| [Epic 4](./EPIC-04-SECURITY.md) | Authentication & Security | 3-4h | ⏳ Pending | - | Walkthrough |
| [Epic 5](./EPIC-05-MULTI-TENANCY.md) | Multi-Tenancy | 2-3h | ⏳ Pending | - | Walkthrough |
| [Epic 6](./EPIC-06-FILES.md) | File & Image Handling | 2-3h | ⏳ Pending | - | Walkthrough |
| [Epic 7](./EPIC-07-FILTERS.md) | Custom Filters | 5h | ⏳ Pending | `step-2-custom-filters` 🔴 | **LIVE CODE #1** |
| [Epic 8](./EPIC-08-ACTIONS.md) | Custom Actions | 7h | ⏳ Pending | `step-3-custom-actions` 🔴 | **LIVE CODE #2** |
| [Epic 9](./EPIC-09-CUSTOM-FIELDS.md) | Custom Fields & Forms | 2-3h | ⏳ Pending | - | Walkthrough |
| [Epic 10](./EPIC-10-JAVASCRIPT.md) | JavaScript Integration | 3-4h | ⏳ Pending | - | Walkthrough |
| [Epic 11](./EPIC-11-ADVANCED.md) | Advanced Features | 3-4h | ⏳ Pending | `step-4-complete` 🟢 | **WALKTHROUGH** |
| [Epic 12](./EPIC-12-DEMO-DATA.md) | Demo Data & Testing | 4-5h | ⏳ Pending | - | Pre-talk |
| [Epic 13](./EPIC-13-TALK-PREP.md) | Talk Preparation | 6-8h | ⏳ Pending | - | Pre-talk |

**Total Estimated Effort**: ~52 hours

---

## Implementation Phases

### Phase 1: Core Foundation (Epics 1-3)
**Goal**: Get to `step-1-base` (talk starting point)
- Epic 1: Project Setup (1-2 hours)
- Epic 2: Entity Layer (3-4 hours)
- Epic 3: Basic Admin (2-3 hours)
**Total**: ~8 hours
**Deliverable**: Working basic CRUD admin interface

### Phase 2: Live Coding Features (Epics 7-8)
**Goal**: Build and rehearse live coding features
- Epic 7: Custom Filters (5 hours including rehearsal)
- Epic 8: Custom Actions (7 hours including rehearsal)
**Total**: ~12 hours
**Deliverable**: Two features ready to live code + safety nets

### Phase 3: Advanced Features (Epics 4-6, 9-11)
**Goal**: Pre-build all walkthrough features
- Epic 4: Security (3-4 hours)
- Epic 5: Multi-Tenancy (2-3 hours)
- Epic 6: Files (2-3 hours)
- Epic 9: Custom Fields (2-3 hours)
- Epic 10: JavaScript (3-4 hours)
- Epic 11: Advanced (3-4 hours)
**Total**: ~20 hours
**Deliverable**: All advanced features implemented

### Phase 4: Demo Preparation (Epics 12-13)
**Goal**: Polish and prepare for talk
- Epic 12: Demo Data (4-5 hours)
- Epic 13: Talk Prep (6-8 hours)
**Total**: ~12 hours
**Deliverable**: Ready to present

---

## Critical Path

### Minimum Viable Demo (16 hours)
For a basic working demo without all features:
1. Epic 1: Setup (2h)
2. Epic 2: Entities (4h)
3. Epic 3: Basic Admin (3h)
4. Epic 7: Filters (light version, 2h)
5. Epic 12: Demo Data (3h)
6. Epic 13: Rehearsal (2h)

### Full Featured Demo (52 hours)
All epics completed as specified.

---

## Git Branch & Tag Strategy

```
main (production-ready)
  │
  ├── epic/01-setup
  ├── epic/02-entities
  ├── epic/03-basic-admin
  │   └── TAG: step-1-base ⭐ TALK STARTS HERE
  │
  ├── epic/04-security
  ├── epic/05-multi-tenancy
  ├── epic/06-files
  │
  ├── epic/07-filters
  │   └── TAG: step-2-custom-filters 🔴 SAFETY NET
  │
  ├── epic/08-actions
  │   └── TAG: step-3-custom-actions 🔴 SAFETY NET
  │
  ├── epic/09-custom-fields
  ├── epic/10-javascript
  ├── epic/11-advanced
  │   └── TAG: step-4-complete 🟢 WALKTHROUGH STARTS
  │
  ├── epic/12-demo-data
  └── epic/13-talk-prep
```

---

## Epic Descriptions

### Epic 0: Planning & Documentation ✅
Complete project planning including entity model, feature mapping, talk structure, and branch strategy.

**Status**: Complete
**Deliverables**: All planning documents created

---

### Epic 1: Project Setup & Foundation
Fresh Symfony 7.x installation with all required dependencies (EasyAdmin, Doctrine Extensions, Stimulus JS).

**Key Stories**:
- Install Symfony 7.x
- Install core dependencies
- Configure database
- Set up asset management
- Initialize git repository

**Deliverables**: Working Symfony installation

---

### Epic 2: Entity Layer & Database
Create all 7 entities, 5 enums, 1 DTO, with proper relationships and Doctrine configuration.

**Key Stories**:
- Create Organization, Person, Sheet, Setlist, SetlistItem, Member entities
- Create enums for type-safe values
- Generate and execute migrations
- Create custom repository methods

**Deliverables**: Complete database schema

---

### Epic 3: Basic EasyAdmin CRUD ⭐
Set up EasyAdmin with basic CRUD for all entities. This is the talk starting point.

**Key Stories**:
- Create Dashboard controller
- Create CRUD controllers for all 7 entities
- Configure menu and navigation
- Basic file upload setup
- Polish UX

**Deliverables**: Working admin interface
**Git Tag**: `step-1-base`

---

### Epic 4: Authentication & Security
Implement Symfony security with login, role hierarchy, voters, and EasyAdmin permissions.

**Key Stories**:
- Configure security bundle
- Create login system
- Implement role hierarchy (4 roles)
- Create voters for entity-level access
- Create test users

**Deliverables**: Working authentication and RBAC

---

### Epic 5: Multi-Tenancy
Implement organization-based data isolation with automatic filtering.

**Key Stories**:
- Create query extension for organization filtering
- Create entity listener to auto-set organization
- Update CRUD controllers
- Test data isolation

**Deliverables**: Organization-scoped data access

---

### Epic 6: File & Image Handling
Implement file uploads for PDFs and images using VichUploader.

**Key Stories**:
- Set up VichUploader
- Configure PDF uploads for sheets
- Configure image uploads (covers, logos)
- Create custom field templates
- Handle file deletion

**Deliverables**: Working file upload system

---

### Epic 7: Custom Filters 🔴
Implement custom EasyAdmin filters. Includes one live-coded filter.

**Key Stories**:
- Create "Has PDF" filter (pre-built)
- Prepare for live coding
- Implement "Difficulty + Status" filter (LIVE CODED)
- Rehearse live coding

**Deliverables**: Custom filters + live coding template
**Git Tag**: `step-2-custom-filters` (safety net)

---

### Epic 8: Custom Actions 🔴
Implement custom EasyAdmin actions. Includes one live-coded action.

**Key Stories**:
- Prepare "Add to Setlist" action (LIVE CODED)
- Implement batch archive action
- Implement "Generate PDF" action
- Implement "Mark as Performed" action
- Rehearse live coding

**Deliverables**: Custom actions + live coding template
**Git Tag**: `step-3-custom-actions` (safety net)

---

### Epic 9: Custom Fields & Form Extensions
Implement custom field types and enhanced form layouts.

**Key Stories**:
- Create SheetReference form type
- Implement collection field
- Create custom field templates
- Customize form layouts
- Add conditional fields

**Deliverables**: Professional form customization

---

### Epic 10: JavaScript Integration
Implement both embedded JavaScript and Stimulus controllers.

**Key Stories**:
- Duration auto-format (embedded JS)
- Drag-and-drop Stimulus controller
- Backend reorder endpoint
- Toast notifications
- Test across browsers

**Deliverables**: Interactive JavaScript features

---

### Epic 11: Advanced Features 🟢
Implement export, dashboard widgets, custom queries, and search.

**Key Stories**:
- Export with filters
- Dashboard widgets
- Specific queries (unused sheets, most used composer)
- Global search
- Performance optimization

**Deliverables**: Production-ready advanced features
**Git Tag**: `step-4-complete` (walkthrough starting point)

---

### Epic 12: Demo Data & Testing
Create realistic demo data and comprehensive testing.

**Key Stories**:
- Create fixtures for all entities
- Upload sample files
- Create test scenarios
- Manual testing checklist
- Optional: automated tests

**Deliverables**: Demo-ready application with realistic data

---

### Epic 13: Talk Preparation & Polish
Final preparation for the talk including slides, rehearsal, and backup materials.

**Key Stories**:
- Create slide deck
- Rehearse live coding (5+ times)
- Verify all git branches
- Prepare demo environment
- Create backup materials
- Full talk run-through
- Prepare attendee resources

**Deliverables**: Talk-ready presentation

---

## Usage

### For Implementation:
1. Start with Epic 1
2. Work through epics sequentially (1→2→3)
3. After Epic 3, choose your path:
   - **Option A**: Build live coding features first (7→8), then advanced (4→6, 9→11)
   - **Option B**: Build advanced features first (4→6, 9→11), then live coding (7→8)
4. Finish with demo data (12) and talk prep (13)

### For Review:
- Each epic file contains complete implementation details
- Use checklist format to track progress
- Reference acceptance criteria to verify completion

### For Talk Preparation:
- Focus on Epics 7, 8, and 13 for live coding and rehearsal
- Use git tags to practice branch switching
- Follow testing checklists to ensure stability

---

## Document Structure

Each epic document contains:

1. **Header**: Branch, status, effort, dependencies
2. **Goal**: What the epic achieves
3. **Stories**: Numbered stories (e.g., Story 4.1, 4.2)
   - Description
   - Tasks (with checkboxes)
   - Technical Details (code examples)
   - Acceptance Criteria
   - Deliverables
4. **Epic Acceptance Criteria**: Overall epic completion criteria
5. **Testing Checklist**: How to verify the epic is complete
6. **Deliverables**: Summary of what should exist
7. **Notes**: Additional guidance
8. **Next Epic**: What comes next

---

## Key Resources

- **Main Documentation**: `/docs/`
  - `TALK_PLANNING.md` - Complete talk structure
  - `ENTITY_MODEL.md` - Entity specifications
  - `FEATURE_DEMONSTRATIONS.md` - Feature implementation map
  - `EPICS_AND_STORIES.md` - Epic overview
  - `BRANCH_STRATEGY.md` - Git workflow for talk

- **Epic Details**: `/docs/epics/` (this directory)
  - Individual epic files with implementation details

---

## Quick Start

```bash
# Clone repository
git clone <repository-url>
cd sheetmusic-manager

# Start with Epic 1
# Follow docs/epics/EPIC-01-SETUP.md

# Track progress
# Check off tasks in each epic file as you complete them

# Create branches as you go
git checkout -b epic/01-setup
# ... work on Epic 1 ...
git commit -m "Complete Epic 1"

# Continue through all epics
```

---

## Status Legend

- ✅ Complete
- ⏳ Pending
- 🚧 In Progress
- ⭐ Talk Starting Point
- 🔴 Safety Net (live coding)
- 🟢 Walkthrough Starting Point

---

## Questions or Issues?

Refer to the main documentation in `/docs/` or the individual epic files for detailed guidance.

Happy coding! 🎵
