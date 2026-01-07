# Sheet Music Manager - EasyAdmin Talk Planning

## Talk Overview

**Duration**: 30 minutes
**Format**: Hybrid (30% live coding, 70% walkthrough)
**Focus**: Inspire + Teach
**Audience**: PHP developers, Symfony community

---

## Application Concept

A sheet music management system for bands, choirs, and musical groups to:
- Catalog their music sheets
- Organize composers and arrangers
- Create setlists for performances
- Manage multi-tenant organizations

---

## Entity Model

### 1. Organization (Multi-tenancy)
**Purpose**: Tenant isolation for multi-organization support

**Fields**:
- `name` (string)
- `type` (string/enum: choir, band, orchestra)
- `logo` (image file)
- Timestampable: `created_at`, `updated_at`
- Blameable: `created_by`, `updated_by` → Member

**Relationships**:
- One-to-Many: → Person, Sheet, Setlist, Member

---

### 2. Person (Composer/Arranger)
**Purpose**: Unified entity for composers and arrangers

**Fields**:
- `name` (string)
- `type` (enum: composer, arranger, both)
- Timestampable: `created_at`, `updated_at`
- Blameable: `created_by`, `updated_by` → Member

**Relationships**:
- Many-to-One: → Organization
- One-to-Many: → Sheet (as composer)
- One-to-Many: → Sheet (as arranger)

---

### 3. Sheet (Music Score)
**Purpose**: The actual music piece/score

**Fields**:
- `title` (string, required)
- `genre` (string: Jazz, Classical, Pop, Gospel, etc.)
- `difficulty` (enum: beginner, intermediate, advanced)
- `duration` (string: "3:45" or "3 minutes")
- `key_signature` (string: "C Major", "G minor")
- `status` (enum: active, archived)
- `notes` (text)
- `references` (array of SheetReference DTO - stored as JSON)
- `pdf_file` (file upload)
- `cover_image` (image upload)
- Timestampable: `created_at`, `updated_at`
- Blameable: `created_by`, `updated_by` → Member

**Relationships**:
- Many-to-One: → Person (composer)
- Many-to-One: → Person (arranger)
- Many-to-One: → Organization
- One-to-Many: → SetlistItem

---

### 4. SheetReference (DTO/Value Object)
**Purpose**: Multiple reference codes per sheet (catalog numbers, publisher codes, etc.)

**Structure**:
```php
class SheetReference
{
    public string $reference_code;  // e.g., "BW-123", "CAT-456"
    public string $reference_type;  // e.g., "catalog", "publisher", "internal"
}
```

**Storage**: JSON array in Sheet entity

---

### 5. Setlist
**Purpose**: Collection of sheets for a specific performance/event

**Fields**:
- `name` (string)
- `event_date` (date)
- `occasion` (string: "Christmas Concert 2025")
- `status` (enum: draft, finalized, performed)
- `notes` (text)
- Timestampable: `created_at`, `updated_at`
- Blameable: `created_by`, `updated_by` → Member

**Relationships**:
- Many-to-One: → Organization
- One-to-Many: → SetlistItem

---

### 6. SetlistItem (Join Entity)
**Purpose**: Links sheets to setlists with position and context

**Fields**:
- `position` (integer: order in setlist)
- `name` (string: "Entrance", "Offertory", "Communion" - for liturgical/play context)
- `notes` (text: performance-specific notes)
- Timestampable: `created_at`, `updated_at`

**Relationships**:
- Many-to-One: → Setlist
- Many-to-One: → Sheet

---

### 7. Member (User)
**Purpose**: System users with authentication and organization membership

**Fields**:
- `name` (string)
- `email` (string, unique)
- `password` (hashed string)
- `roles` (array: ROLE_ADMIN, ROLE_CONDUCTOR, ROLE_LIBRARIAN, ROLE_MEMBER)
- Timestampable: `created_at`, `updated_at`

**Implements**: `Symfony\Component\Security\Core\User\UserInterface`

**Relationships**:
- Many-to-One: → Organization

---

## EasyAdmin Feature Demonstrations

### Feature Mapping

| Feature | Where to Demonstrate | Entity/Area |
|---------|---------------------|-------------|
| **Basic CRUD** | Initial showcase | All entities |
| **Specific queries** | Custom repository queries | "Sheets never in a setlist"<br>"Most used composer" |
| **Filter extension** | Custom filters | "Has PDF file"<br>"Difficulty + Status combined" |
| **Export with filters** | Export action | Sheet catalog with active filters |
| **Action extension** | Batch actions | "Archive selected sheets"<br>"Add sheets to setlist" |
| **Field extension** | Custom field type | SheetReference collection field |
| **Form extension** | Custom form layout | Sheet form with conditional fields |
| **File handling** | Upload fields | Sheet: PDF + cover image<br>Organization: logo |
| **Many-to-One relationships** | Entity relations | Sheet → Composer/Arranger<br>All → Organization |
| **Custom actions** | Entity actions | "Generate Setlist PDF"<br>"Preview Sheet"<br>"Mark as Performed" |
| **Role-based access** | Security/voters | ROLE_CONDUCTOR: manage setlists<br>ROLE_LIBRARIAN: manage sheets<br>ROLE_MEMBER: read-only |
| **Multi-tenancy** | Query extension | Organization scoping on all queries |
| **JS Integration (embedded)** | Inline JS | Duration auto-format in Sheet form |
| **JS Integration (Stimulus)** | Controller | Drag-and-drop reordering in SetlistItem |

---

## Technical Stack

### Core
- PHP 8.2+
- Symfony 7.x (latest stable)
- EasyAdmin 4.x
- Doctrine ORM
- MySQL/PostgreSQL

### Extensions
- **stof/doctrine-extensions-bundle** (Gedmo)
  - Timestampable behavior
  - Blameable behavior
  - (Extensible to Loggable for audit trail later)

### Frontend
- Symfony AssetMapper (or Webpack Encore)
- Stimulus JS framework
- Custom embedded JavaScript

---

## Talk Structure (30 minutes)

### [0-3 min] Hook & Context
- **Problem statement**: Building admin panels is repetitive
- **Solution**: EasyAdmin handles 80%, extensible for 20%
- **Demo app**: Sheet Music Manager overview
- **Goal**: Show what's possible in 30 minutes

### [3-8 min] Foundation (Walkthrough)
- Show running application (2 min)
  - Browse sheets, view setlist
  - Quick navigation through admin
- Entity model overview (1 min)
  - Show diagram/slide of relationships
- Setup time: "15 minutes to get here" (1 min)
- Show basic CRUD configuration (1 min)
  - One entity controller code

### [8-15 min] Live Coding (2 Features)

#### Live Feature 1: Custom Filter (3-4 min)
**Goal**: Filter sheets by difficulty + status combined
- Create empty filter class
- Implement `apply()` method
- Add to CRUD controller
- Refresh admin → filter appears
- Use it → filtered results!

#### Live Feature 2: Custom Action (3-4 min)
**Goal**: "Add to Setlist" batch action
- Create action class
- Implement logic
- Configure in CRUD controller
- Select sheets → trigger action → modal → success!

### [15-26 min] Advanced Showcase (Walkthrough)

#### Multi-tenancy (2 min)
- Show query extension code
- Demo: login as User A → see Organization A data
- Switch to User B → see Organization B data only
- "Automatic filtering on every query"

#### JS Integration (3 min)
- **Method 1**: Embedded JS in form
  - Show duration field auto-formatting code
  - Type "3.5" → becomes "3:30"
- **Method 2**: Stimulus controller
  - Show drag-and-drop controller code
  - Reorder setlist items by dragging
  - "Both approaches work, choose what fits"

#### Role-based Access (2 min)
- Show security configuration
- Demo different roles:
  - ROLE_MEMBER: read-only
  - ROLE_LIBRARIAN: manage sheets
  - ROLE_CONDUCTOR: manage setlists + sheets
  - ROLE_ADMIN: everything

#### File Handling (2 min)
- Show PDF upload configuration
- Upload a sheet PDF
- Show preview in admin
- Download file
- "Vich Uploader or custom - both easy"

#### Export with Filters (2 min)
- Apply filters on sheet list
- Click export button
- Download CSV/Excel
- Open file → filtered data exported
- "Filters apply to exports automatically"

### [26-30 min] Wrap-Up

#### Key Takeaways (1 min)
- EasyAdmin is powerful out-of-the-box
- Extension system covers edge cases
- Real applications in production use this

#### Resources (1 min)
- GitHub repo with all code
- Step-by-step branches (step-1, step-2, etc.)
- EasyAdmin documentation links

#### Q&A (2 min)
- Open floor for questions

---

## Git Branch Strategy

To support the hybrid format with safety nets:

```
main (or demo-final)
  ↓ Complete application with all features

step-0-fresh
  ↓ Fresh Symfony install

step-1-base
  ↓ Entities created + basic EasyAdmin CRUD
  ↓ START YOUR TALK HERE

step-2-custom-filter
  ↓ After live coding custom filter
  ↓ SAFETY NET: if live coding fails, checkout this

step-3-custom-action
  ↓ After live coding custom action
  ↓ SAFETY NET: if live coding fails, checkout this

step-4-advanced
  ↓ All advanced features implemented
  ↓ Walkthroughs start here
```

**Usage during talk**:
- Start at `step-1-base`
- Live code to step-2, step-3
- If something breaks: `git stash && git checkout step-3`
- Continue walkthrough from step-4

---

## Preparation Checklist

### Before Talk
- [ ] Rehearse live coding sections 3-5 times
- [ ] Test all git checkouts work smoothly
- [ ] Prepare demo data (10-15 sheets, 3-4 setlists)
- [ ] Create 2-3 test users with different roles
- [ ] Test all features work in demo environment
- [ ] Prepare backup slides (in case of catastrophic failure)
- [ ] Clean browser cache, logout all sessions

### Demo Environment
- [ ] Local development server ready
- [ ] Database seeded with realistic data
- [ ] Multiple browser windows/tabs pre-opened
- [ ] IDE ready with relevant files bookmarked
- [ ] Terminal ready with common commands in history
- [ ] Disable notifications, close unnecessary apps
- [ ] Font sizes increased for visibility

### Backup Plan
- [ ] Recorded video of working features (just in case)
- [ ] Screenshots of key moments
- [ ] Slides explaining code if live demo fails

---

## Key Messages

1. **EasyAdmin is production-ready**: Not just for prototypes
2. **Extension points everywhere**: Filters, fields, forms, actions, queries
3. **Symfony ecosystem integration**: Security, forms, Doctrine - all work together
4. **Real-world features**: Multi-tenancy, roles, file handling - not toy examples
5. **Developer experience**: Fast to build, easy to maintain

---

## Q&A Preparation

Anticipated questions:

**Q: Can EasyAdmin handle complex business logic?**
A: Yes, through custom actions, event listeners, and service integration.

**Q: How does performance scale?**
A: Standard Doctrine optimization applies. Use pagination, lazy loading, query optimization.

**Q: Can I customize the UI heavily?**
A: Yes, templates are overridable, custom themes supported.

**Q: Multi-tenancy - is it secure?**
A: Yes, when properly implemented with query extensions and voter checks.

**Q: What about API endpoints?**
A: EasyAdmin is for admin panels. Use API Platform or custom controllers for APIs.

**Q: Can I use this with existing projects?**
A: Absolutely! Install bundle, configure, done. No migration needed.

---

## Resources for Attendees

- **GitHub Repo**: [Your repo URL here]
- **EasyAdmin Docs**: https://symfony.com/bundles/EasyAdminBundle/current/index.html
- **Symfony Docs**: https://symfony.com/doc/current/index.html
- **Stimulus JS**: https://stimulus.hotwired.dev/
- **Doctrine Extensions**: https://github.com/doctrine-extensions/DoctrineExtensions

---

## Post-Talk TODO

- [ ] Share slides/repo link
- [ ] Write blog post with detailed walkthrough
- [ ] Create video tutorial for each feature
- [ ] Collect feedback from attendees
- [ ] Update repo based on questions asked
