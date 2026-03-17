# Entity Model - Sheet Music Manager

## Entity Relationship Diagram (Text Format)

```
Organization
    ↓ (One-to-Many)
    ├── Person
    ├── Sheet
    ├── Setlist
    └── Member

Person
    ↓ (One-to-Many)
    └── Sheet (as composer or arranger)

Sheet
    ├── references: SheetReference[] (embedded DTO)
    ├── ManyToOne → Person (composer)
    ├── ManyToOne → Person (arranger)
    ├── ManyToOne → Organization
    └── OneToMany → SetlistItem

Setlist
    ├── ManyToOne → Organization
    └── OneToMany → SetlistItem

SetlistItem
    ├── ManyToOne → Setlist
    └── ManyToOne → Sheet

Member (implements UserInterface)
    └── ManyToOne → Organization
```

---

## Complete Entity Specifications

### Organization

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | integer | PK, Auto | Primary key |
| name | string(255) | NOT NULL | Organization name |
| type | string(50) | NOT NULL | Type: choir, band, orchestra |
| logo | string(255) | NULL | Logo file path |
| created_at | datetime | NOT NULL | Auto (Timestampable) |
| updated_at | datetime | NOT NULL | Auto (Timestampable) |
| created_by | Member | NULL | Auto (Blameable) |
| updated_by | Member | NULL | Auto (Blameable) |

**Doctrine Extensions**: Timestampable, Blameable

---

### Person

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | integer | PK, Auto | Primary key |
| name | string(255) | NOT NULL | Person name |
| type | string(20) | NOT NULL | composer, arranger, both |
| organization_id | integer | FK, NOT NULL | Foreign key to Organization |
| created_at | datetime | NOT NULL | Auto (Timestampable) |
| updated_at | datetime | NOT NULL | Auto (Timestampable) |
| created_by | Member | NULL | Auto (Blameable) |
| updated_by | Member | NULL | Auto (Blameable) |

**Relationships**:
- ManyToOne: organization (Organization)
- OneToMany: sheets_composed (Sheet, mapped by composer)
- OneToMany: sheets_arranged (Sheet, mapped by arranger)

**Doctrine Extensions**: Timestampable, Blameable

---

### Sheet

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | integer | PK, Auto | Primary key |
| title | string(255) | NOT NULL | Sheet title |
| genre | string(100) | NULL | Genre (Jazz, Classical, etc.) |
| difficulty | string(20) | NULL | beginner, intermediate, advanced |
| duration | string(50) | NULL | Duration (e.g., "3:45") |
| key_signature | string(50) | NULL | Key (e.g., "C Major") |
| status | string(20) | NOT NULL, DEFAULT 'active' | active, archived |
| notes | text | NULL | Additional notes |
| references | json | NULL | Array of SheetReference DTOs |
| pdf_file | string(255) | NULL | PDF file path |
| cover_image | string(255) | NULL | Cover image path |
| composer_id | integer | FK, NULL | Foreign key to Person |
| arranger_id | integer | FK, NULL | Foreign key to Person |
| organization_id | integer | FK, NOT NULL | Foreign key to Organization |
| created_at | datetime | NOT NULL | Auto (Timestampable) |
| updated_at | datetime | NOT NULL | Auto (Timestampable) |
| created_by | Member | NULL | Auto (Blameable) |
| updated_by | Member | NULL | Auto (Blameable) |

**Relationships**:
- ManyToOne: composer (Person)
- ManyToOne: arranger (Person)
- ManyToOne: organization (Organization)
- OneToMany: setlist_items (SetlistItem)

**Doctrine Extensions**: Timestampable, Blameable

---

### SheetReference (DTO - Not a database table)

```php
class SheetReference
{
    public string $reference_code;  // e.g., "BW-123"
    public string $reference_type;  // e.g., "catalog", "publisher"
}
```

**Storage**: Stored as JSON in Sheet.references field

**Example JSON**:
```json
[
    {
        "reference_code": "BW-123",
        "reference_type": "catalog"
    },
    {
        "reference_code": "PUB-2024-456",
        "reference_type": "publisher"
    }
]
```

---

### Setlist

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | integer | PK, Auto | Primary key |
| name | string(255) | NOT NULL | Setlist name |
| event_date | date | NULL | Event date |
| occasion | string(255) | NULL | Occasion/event name |
| status | string(20) | NOT NULL, DEFAULT 'draft' | draft, finalized, performed |
| notes | text | NULL | Additional notes |
| organization_id | integer | FK, NOT NULL | Foreign key to Organization |
| created_at | datetime | NOT NULL | Auto (Timestampable) |
| updated_at | datetime | NOT NULL | Auto (Timestampable) |
| created_by | Member | NULL | Auto (Blameable) |
| updated_by | Member | NULL | Auto (Blameable) |

**Relationships**:
- ManyToOne: organization (Organization)
- OneToMany: items (SetlistItem)

**Doctrine Extensions**: Timestampable, Blameable

---

### SetlistItem

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | integer | PK, Auto | Primary key |
| position | integer | NOT NULL | Order in setlist |
| name | string(255) | NULL | Context name (e.g., "Entrance") |
| notes | text | NULL | Performance notes |
| setlist_id | integer | FK, NOT NULL | Foreign key to Setlist |
| sheet_id | integer | FK, NOT NULL | Foreign key to Sheet |
| created_at | datetime | NOT NULL | Auto (Timestampable) |
| updated_at | datetime | NOT NULL | Auto (Timestampable) |

**Relationships**:
- ManyToOne: setlist (Setlist)
- ManyToOne: sheet (Sheet)

**Doctrine Extensions**: Timestampable

**Notes**:
- Position determines order in setlist
- Name provides liturgical/contextual label
- Could be enhanced with drag-and-drop reordering (Stimulus)

---

### Member

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | integer | PK, Auto | Primary key |
| name | string(255) | NOT NULL | Member name |
| email | string(180) | UNIQUE, NOT NULL | Email (username) |
| password | string(255) | NOT NULL | Hashed password |
| roles | json | NOT NULL | Array of roles |
| organization_id | integer | FK, NOT NULL | Foreign key to Organization |
| created_at | datetime | NOT NULL | Auto (Timestampable) |
| updated_at | datetime | NOT NULL | Auto (Timestampable) |

**Implements**: `UserInterface`

**Relationships**:
- ManyToOne: organization (Organization)

**Roles**:
- `ROLE_ADMIN`: Full access
- `ROLE_CONDUCTOR`: Manage setlists + sheets
- `ROLE_LIBRARIAN`: Manage sheets only
- `ROLE_MEMBER`: Read-only

**Doctrine Extensions**: Timestampable

**Security Notes**:
- Used for Symfony authentication
- Used for Blameable behavior (created_by, updated_by)
- Organization scoping enforced via voters/query extensions

---

## Database Indexes (Recommended)

### Performance Optimization

```sql
-- Sheet indexes
CREATE INDEX idx_sheet_status ON sheet(status);
CREATE INDEX idx_sheet_organization ON sheet(organization_id);
CREATE INDEX idx_sheet_composer ON sheet(composer_id);
CREATE INDEX idx_sheet_arranger ON sheet(arranger_id);

-- Setlist indexes
CREATE INDEX idx_setlist_organization ON setlist(organization_id);
CREATE INDEX idx_setlist_event_date ON setlist(event_date);
CREATE INDEX idx_setlist_status ON setlist(status);

-- SetlistItem indexes
CREATE INDEX idx_setlistitem_setlist ON setlist_item(setlist_id);
CREATE INDEX idx_setlistitem_sheet ON setlist_item(sheet_id);
CREATE INDEX idx_setlistitem_position ON setlist_item(setlist_id, position);

-- Member indexes
CREATE UNIQUE INDEX idx_member_email ON member(email);
CREATE INDEX idx_member_organization ON member(organization_id);

-- Person indexes
CREATE INDEX idx_person_organization ON person(organization_id);
CREATE INDEX idx_person_type ON person(type);
```

---

## Sample Data Structure

### Example Organization
```yaml
Organization:
  name: "St. Mary's Choir"
  type: "choir"
  logo: "uploads/logos/st-marys.png"
```

### Example Person (Composer)
```yaml
Person:
  name: "Johann Sebastian Bach"
  type: "composer"
  organization: → St. Mary's Choir
```

### Example Sheet
```yaml
Sheet:
  title: "Ave Maria"
  genre: "Sacred"
  difficulty: "intermediate"
  duration: "4:30"
  key_signature: "D Major"
  status: "active"
  notes: "Latin text, 4-part harmony"
  references:
    - { reference_code: "CAT-001", reference_type: "catalog" }
    - { reference_code: "PUB-Bach-123", reference_type: "publisher" }
  pdf_file: "uploads/sheets/ave-maria.pdf"
  cover_image: "uploads/covers/ave-maria.jpg"
  composer: → Johann Sebastian Bach
  arranger: null
  organization: → St. Mary's Choir
```

### Example Setlist
```yaml
Setlist:
  name: "Christmas Concert 2025"
  event_date: "2025-12-24"
  occasion: "Christmas Eve Mass"
  status: "finalized"
  notes: "Remember to prepare lighting cues"
  organization: → St. Mary's Choir
  items:
    - { position: 1, name: "Entrance", sheet: "Ave Maria", notes: "Start softly" }
    - { position: 2, name: "Offertory", sheet: "Silent Night", notes: "" }
    - { position: 3, name: "Communion", sheet: "O Holy Night", notes: "Soprano solo" }
```

### Example Member
```yaml
Member:
  name: "John Conductor"
  email: "john@stmarys.org"
  password: "[hashed]"
  roles: ["ROLE_CONDUCTOR"]
  organization: → St. Mary's Choir
```

---

## Validation Rules

### Sheet
- `title`: required, max 255 characters
- `difficulty`: must be one of: beginner, intermediate, advanced
- `status`: must be one of: active, archived
- `pdf_file`: optional, must be PDF, max 10MB
- `cover_image`: optional, must be image (jpg, png), max 2MB

### Setlist
- `name`: required, max 255 characters
- `status`: must be one of: draft, finalized, performed
- `event_date`: optional, must be valid date

### SetlistItem
- `position`: required, integer, min 1
- `setlist`: required
- `sheet`: required
- Unique constraint: (setlist_id, position)

### Member
- `email`: required, valid email, unique
- `password`: required, min 8 characters (before hashing)
- `roles`: required, must contain at least one valid role

### Organization
- `name`: required, max 255 characters
- `type`: required, max 50 characters

### Person
- `name`: required, max 255 characters
- `type`: required, must be one of: composer, arranger, both

---

## Business Rules

1. **Multi-tenancy**: All entities except Member are scoped by Organization
2. **Soft delete**: Use `status: archived` instead of deleting sheets
3. **Setlist items must be ordered**: Use position field for ordering
4. **Security**: Member can only access data from their Organization
5. **Blameable**: All main entities track who created/updated them
6. **Person type**: A person can be composer AND arranger (type: both)
7. **Sheet references**: Can have 0 to N references (stored as JSON array)

---

## Future Enhancements (Not in initial demo)

- **Instruments**: Track which instruments are required for each sheet
- **Borrowing**: Track who has borrowed physical copies
- **Loggable**: Full audit trail using Doctrine Extensions
- **Versions**: Track sheet versions/revisions
- **Tags**: Add tagging system for flexible categorization
- **Comments**: Allow members to comment on sheets
- **Ratings**: Member ratings for difficulty/quality
