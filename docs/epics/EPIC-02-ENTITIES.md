# Epic 2: Entity Layer & Database

**Branch**: `epic/02-entities`
**Status**: ⏳ Pending
**Estimated Effort**: 3-4 hours
**Dependencies**: Epic 1 (Project Setup)

---

## Goal

Create all Doctrine entities with proper fields, relationships, enums, and repository methods. Generate and execute migrations to create the database schema.

---

## Stories

### Story 2.1: Create Organization Entity

**Description**: Create the Organization entity for multi-tenancy.

**Tasks**:
- [X] Generate entity: `php bin/console make:entity Organization`
- [X] Add fields: name, type, logo
- [X] Add Timestampable trait
- [X] Add Blameable trait
- [ ] Define relationships
- [X] Add validation constraints

**Entity Code** (`src/Entity/Organization.php`):
```php
<?php

namespace App\Entity;

use App\Repository\OrganizationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OrganizationRepository::class)]
#[Gedmo\Loggable]
class Organization
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    private ?string $type = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logo = null;

    #[ORM\OneToMany(mappedBy: 'organization', targetEntity: Person::class)]
    private Collection $people;

    #[ORM\OneToMany(mappedBy: 'organization', targetEntity: Sheet::class)]
    private Collection $sheets;

    #[ORM\OneToMany(mappedBy: 'organization', targetEntity: Setlist::class)]
    private Collection $setlists;

    #[ORM\OneToMany(mappedBy: 'organization', targetEntity: Member::class)]
    private Collection $members;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[Gedmo\Blameable(on: 'create')]
    #[ORM\ManyToOne(targetEntity: Member::class)]
    private ?Member $createdBy = null;

    #[Gedmo\Blameable(on: 'update')]
    #[ORM\ManyToOne(targetEntity: Member::class)]
    private ?Member $updatedBy = null;

    public function __construct()
    {
        $this->people = new ArrayCollection();
        $this->sheets = new ArrayCollection();
        $this->setlists = new ArrayCollection();
        $this->members = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }

    // Getters and setters...
}
```

**Acceptance Criteria**:
- Entity created with all fields
- Relationships defined
- Timestampable and Blameable configured
- Validation constraints added
- `__toString()` method implemented

**Deliverables**:
- `src/Entity/Organization.php`
- `src/Repository/OrganizationRepository.php`

---

### Story 2.2: Create Person Entity

**Description**: Create the Person entity for composers and arrangers.

**Tasks**:
- [X] Generate entity: `php bin/console make:entity Person`
- [X] Add fields: name, type
- [X] Add Timestampable and Blameable traits
- [X] Define relationship to Organization
- [X] Define inverse relationships to Sheet
- [X] Add validation constraints

**Key Fields**:
- `name`: string(255), NOT NULL
- `type`: string(20), NOT NULL (composer/arranger/both)
- `organization`: ManyToOne → Organization

**Acceptance Criteria**:
- Entity created with all fields
- Bidirectional relationship with Organization
- Inverse relationships with Sheet (composer, arranger)
- Timestampable and Blameable configured

**Deliverables**:
- `src/Entity/Person.php`
- `src/Repository/PersonRepository.php`

---

### Story 2.3: Create Sheet Entity

**Description**: Create the Sheet entity for music scores.

**Tasks**:
- [X] Generate entity: `php bin/console make:entity Sheet`
- [X] Add fields: title, genre, difficulty, duration, key_signature, status, notes
- [X] Add file fields: pdf_file, cover_image
- [X] Add JSON field: references (array type)
- [X] Define relationships
- [X] Add Timestampable and Blameable
- [X] Add validation constraints

**Key Fields**:
- `title`: string(255), NOT NULL
- `genre`: string(100), nullable
- `difficulty`: string(20), nullable
- `duration`: string(50), nullable
- `key_signature`: string(50), nullable
- `status`: string(20), NOT NULL, default 'active'
- `notes`: text, nullable
- `references`: json, nullable (array of SheetReference DTOs)
- `pdf_file`: string(255), nullable
- `cover_image`: string(255), nullable
- `composer`: ManyToOne → Person
- `arranger`: ManyToOne → Person
- `organization`: ManyToOne → Organization

**Acceptance Criteria**:
- Entity created with all fields
- JSON field for references configured
- Multiple relationships to Person (composer, arranger)
- Timestampable and Blameable configured

**Deliverables**:
- `src/Entity/Sheet.php`
- `src/Repository/SheetRepository.php`

---

### Story 2.4: Create SheetReference DTO

**Description**: Create a Data Transfer Object for sheet references (not a database entity).

**Tasks**:
- [X] Create DTO class
- [X] Add properties: reference_code, reference_type
- [X] Implement serialization for JSON storage
- [X] Add validation

**DTO Code** (`src/DTO/SheetReference.php`):
```php
<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class SheetReference
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    public string $referenceCode = '';

    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['catalog', 'publisher', 'internal'])]
    public string $referenceType = '';

    public function __construct(string $referenceCode = '', string $referenceType = '')
    {
        $this->referenceCode = $referenceCode;
        $this->referenceType = $referenceType;
    }

    public function toArray(): array
    {
        return [
            'reference_code' => $this->referenceCode,
            'reference_type' => $this->referenceType,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['reference_code'] ?? '',
            $data['reference_type'] ?? ''
        );
    }
}
```

**Acceptance Criteria**:
- DTO class created
- Properties defined
- Serialization methods implemented
- Validation constraints added

**Deliverables**:
- `src/DTO/SheetReference.php`

---

### Story 2.5: Create Setlist Entity

**Description**: Create the Setlist entity for performance collections.

**Tasks**:
- [X] Generate entity: `php bin/console make:entity Setlist`
- [X] Add fields: name, event_date, occasion, status, notes
- [X] Define relationships
- [X] Add Timestampable and Blameable
- [X] Add validation constraints

**Key Fields**:
- `name`: string(255), NOT NULL
- `event_date`: date, nullable
- `occasion`: string(255), nullable
- `status`: string(20), NOT NULL, default 'draft'
- `notes`: text, nullable
- `organization`: ManyToOne → Organization
- `items`: OneToMany → SetlistItem

**Acceptance Criteria**:
- Entity created with all fields
- Relationships defined
- Timestampable and Blameable configured

**Deliverables**:
- `src/Entity/Setlist.php`
- `src/Repository/SetlistRepository.php`

---

### Story 2.6: Create SetlistItem Entity

**Description**: Create the SetlistItem join entity linking setlists to sheets.

**Tasks**:
- [X] Generate entity: `php bin/console make:entity SetlistItem`
- [X] Add fields: position, name, notes
- [X] Define relationships to Setlist and Sheet
- [X] Add Timestampable (no Blameable)
- [X] Add unique constraint on (setlist_id, position)
- [X] Add validation constraints

**Key Fields**:
- `position`: integer, NOT NULL
- `name`: string(255), nullable (e.g., "Entrance", "Offertory")
- `notes`: text, nullable
- `setlist`: ManyToOne → Setlist
- `sheet`: ManyToOne → Sheet

**Unique Constraint**:
```php
#[ORM\UniqueConstraint(name: 'unique_position_per_setlist', columns: ['setlist_id', 'position'])]
```

**Acceptance Criteria**:
- Entity created with all fields
- Unique constraint on position per setlist
- Timestampable configured
- Ordered by position by default

**Deliverables**:
- `src/Entity/SetlistItem.php`
- `src/Repository/SetlistItemRepository.php`

---

### Story 2.7: Create Member Entity (User)

**Description**: Create the Member entity implementing UserInterface for authentication.

**Tasks**:
- [X] Generate entity: `php bin/console make:user Member`
- [X] Add fields: name, email, password, roles
- [X] Implement UserInterface
- [X] Add relationship to Organization
- [X] Add Timestampable
- [X] Add validation constraints

**Key Fields**:
- `name`: string(255), NOT NULL
- `email`: string(180), UNIQUE, NOT NULL
- `password`: string(255), NOT NULL (hashed)
- `roles`: json, NOT NULL (array)
- `organization`: ManyToOne → Organization

**UserInterface Methods**:
- `getUserIdentifier()`: return email
- `getRoles()`: return roles array
- `eraseCredentials()`: clear sensitive data

**Acceptance Criteria**:
- Entity implements UserInterface
- Email is unique
- Roles stored as JSON array
- Timestampable configured
- Password will be hashed

**Deliverables**:
- `src/Entity/Member.php`
- `src/Repository/MemberRepository.php`

## Epic Acceptance Criteria

- [ ] All 7 entities created
- [ ] 1 DTO created
- [ ] All relationships properly mapped
- [ ] Timestampable and Blameable configured
- [ ] Validation constraints added
- [ ] Migrations generated and executed
- [ ] Database indexes added
- [ ] Schema validation passes
- [ ] No Doctrine errors

---

## Testing Checklist

```bash
# Validate entities
php bin/console doctrine:schema:validate

# Check for mapping errors
php bin/console doctrine:mapping:info

# Verify all entities are mapped
php bin/console doctrine:mapping:info | grep "App\\\Entity"

# Test repository methods (in tests or manually)
# Create a test command to verify queries work
```

---

## Deliverables

- [ ] 7 Entity files in `src/Entity/`
- [ ] 7 Repository files in `src/Repository/`
- [ ] 1 DTO file in `src/DTO/`
- [ ] Migration files in `migrations/`
- [ ] Database schema created

---

## Notes

- Use PHP 8.1+ attributes for Doctrine mapping
- Prefer enums over string constants for type safety
- Add `__toString()` methods to entities for EasyAdmin display
- Keep repository methods focused and organization-scoped
- Test migrations on a fresh database before committing

---

## Next Epic

**Epic 3**: Basic EasyAdmin CRUD
