# Epic 2: Entity Layer & Database

**Branch**: `epic/02-entities`
**Status**: ✅ Completed
**Actual Effort**: 3-4 hours
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
- [X] Define relationships
- [X] Add validation constraints

**Entity Code** (`src/Entity/Organization.php`):

```php
<?php

namespace App\Entity;

use App\Entity\Security\Member;use App\Entity\Setlist\Setlist;use App\Entity\Sheet\CreditedPerson;use App\Entity\Sheet\Person;use App\Entity\Sheet\Sheet;use App\Repository\OrganizationRepository;use Doctrine\Common\Collections\ArrayCollection;use Doctrine\Common\Collections\Collection;use Doctrine\ORM\Mapping as ORM;use Gedmo\Blameable\Traits\BlameableEntity;use Gedmo\Timestampable\Traits\TimestampableEntity;use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OrganizationRepository::class)]
class Organization
{
    use TimestampableEntity;
    use BlameableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\NotBlank]
    private ?string $type = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logo = null;

    #[ORM\OneToMany(mappedBy: 'organization', targetEntity: Person::class)]
    private Collection $persons;

    #[ORM\OneToMany(mappedBy: 'organization', targetEntity: Sheet::class)]
    private Collection $sheets;

    #[ORM\OneToMany(mappedBy: 'organization', targetEntity: CreditedPerson::class)]
    private Collection $creditedPeople;

    #[ORM\OneToMany(mappedBy: 'organization', targetEntity: Setlist::class)]
    private Collection $setlists;

    #[ORM\ManyToMany(targetEntity: Member::class, mappedBy: 'organizations')]
    private Collection $members;

    public function __construct()
    {
        $this->persons = new ArrayCollection();
        $this->sheets = new ArrayCollection();
        $this->creditedPeople = new ArrayCollection();
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
- [X] Add fields: name
- [X] Add Timestampable and Blameable traits
- [X] Define relationship to Organization
- [X] Define inverse relationships to CreditedPerson
- [X] Add `__toString()` method

**Key Fields**:
- `name`: string(255), NOT NULL
- `organization`: ManyToOne → Organization
- `credit`: OneToMany → CreditedPerson

**Acceptance Criteria**:
- Entity created with all fields
- Bidirectional relationship with Organization
- Inverse relationships with CreditedPerson
- Timestampable and Blameable configured
- `__toString()` method implemented

**Deliverables**:
- `src/Entity/Person.php`
- `src/Repository/PersonRepository.php`

---

### Story 2.3: Create Sheet Entity

**Description**: Create the Sheet entity for music scores.

**Tasks**:
- [X] Generate entity: `php bin/console make:entity Sheet`
- [X] Add fields: title, genre, difficulty, duration, key_signature, notes
- [X] Add file field: file
- [X] Add JSON field: refs (array type)
- [X] Define relationships
- [X] Add Timestampable and Blameable
- [X] Add validation constraints
- [X] Add `__toString()` method

**Key Fields**:
- `title`: string(255), NOT NULL
- `genre`: string(100), nullable
- `difficulty`: string(20), nullable
- `duration`: string(50), nullable
- `key_signature`: string(50), nullable
- `notes`: text, nullable
- `refs`: json (array of SheetReference DTOs)
- `file`: string(255), nullable
- `organization`: ManyToOne → Organization
- `credit`: OneToMany → CreditedPerson
- `setlist`: OneToMany → SetListItem

**Acceptance Criteria**:
- Entity created with all fields
- JSON field for references configured
- Relationships to CreditedPerson and SetListItem
- Timestampable and Blameable configured
- `__toString()` method implemented

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

### Story 2.4.5: Create CreditedPerson Entity

**Description**: Create the CreditedPerson join entity linking persons to sheets with credit type.

**Tasks**:
- [X] Generate entity: `php bin/console make:entity CreditedPerson`
- [X] Add fields: type
- [X] Define relationships to Person, Sheet, and Organization
- [X] Add Timestampable and Blameable
- [X] Add `__toString()` method

**Key Fields**:
- `type`: string(100), NOT NULL (e.g., "composer", "arranger", "lyricist")
- `person`: ManyToOne → Person
- `sheet`: ManyToOne → Sheet
- `organization`: ManyToOne → Organization

**Acceptance Criteria**:
- Entity created with all fields
- Relationships to Person, Sheet, and Organization
- Timestampable and Blameable configured
- `__toString()` method implemented

**Deliverables**:
- `src/Entity/CreditedPerson.php`
- `src/Repository/CreditedPersonRepository.php`

---

### Story 2.5: Create Setlist Entity

**Description**: Create the Setlist entity for performance collections.

**Tasks**:
- [X] Generate entity: `php bin/console make:entity Setlist`
- [X] Add fields: title, date, notes
- [X] Define relationships
- [X] Add Timestampable and Blameable
- [X] Add validation constraints
- [X] Add `__toString()` method

**Key Fields**:
- `title`: string(255), NOT NULL
- `date`: date, nullable
- `notes`: text, NOT NULL (default empty string)
- `organization`: ManyToOne → Organization
- `item`: OneToMany → SetListItem (ordered by position ASC)

**Acceptance Criteria**:
- Entity created with all fields
- Relationships defined
- Timestampable and Blameable configured
- Items collection ordered by position
- `__toString()` method implemented

**Deliverables**:
- `src/Entity/Setlist.php`
- `src/Repository/SetlistRepository.php`

---

### Story 2.6: Create SetListItem Entity

**Description**: Create the SetListItem join entity linking setlists to sheets.

**Tasks**:
- [X] Generate entity: `php bin/console make:entity SetListItem`
- [X] Add fields: position, name, notes
- [X] Define relationships to Setlist, Sheet, and Organization
- [X] Add Timestampable and Blameable
- [X] Add unique constraint on (setlist_id, position)
- [X] Add validation constraints
- [X] Add `__toString()` method
- [X] Configure default ordering by position

**Key Fields**:
- `position`: integer, NOT NULL
- `name`: string(100), nullable (e.g., "Entrance", "Offertory")
- `notes`: text, NOT NULL (default empty string)
- `setlist`: ManyToOne → Setlist
- `sheet`: ManyToOne → Sheet
- `organization`: ManyToOne → Organization

**Unique Constraint**:
```php
#[ORM\UniqueConstraint(name: 'unique_position_per_setlist', columns: ['setlist_id', 'position'])]
```

**Acceptance Criteria**:
- Entity created with all fields
- Unique constraint on position per setlist
- Timestampable and Blameable configured
- Items ordered by position by default (via OrderBy annotation on parent collection)
- `__toString()` method implemented

**Deliverables**:
- `src/Entity/SetListItem.php`
- `src/Repository/SetListItemRepository.php`

---

### Story 2.7: Create Member Entity (User)

**Description**: Create the Member entity implementing UserInterface for authentication.

**Tasks**:
- [X] Generate entity: `php bin/console make:user Member`
- [X] Add fields: name, email, password, roles
- [X] Implement UserInterface and PasswordAuthenticatedUserInterface
- [X] Add relationship to Organization (ManyToMany)
- [X] Add Timestampable
- [X] Add validation constraints
- [X] Add `__toString()` method

**Key Fields**:
- `name`: string(100), NOT NULL
- `email`: string(100), UNIQUE, NOT NULL
- `password`: string(255), NOT NULL (hashed)
- `plainPassword`: string, nullable (transient)
- `roles`: json, NOT NULL (array)
- `organizations`: ManyToMany → Organization

**UserInterface Methods**:
- `getUserIdentifier()`: return email
- `getRoles()`: return roles array (always includes DEFAULT_ROLE)
- `eraseCredentials()`: clear plainPassword

**Acceptance Criteria**:
- Entity implements UserInterface and PasswordAuthenticatedUserInterface
- Email is unique
- Roles stored as JSON array
- ManyToMany relationship with Organization
- Timestampable configured (no Blameable)
- Password will be hashed
- `__toString()` method implemented

**Deliverables**:
- `src/Entity/Member.php`
- `src/Repository/MemberRepository.php`

## Epic Acceptance Criteria

- [X] All 8 entities created (Organization, Person, Sheet, CreditedPerson, Setlist, SetListItem, Member, + BaseRepository)
- [X] 1 DTO created (SheetReference)
- [X] All relationships properly mapped
- [X] Timestampable and Blameable configured where appropriate
- [X] Validation constraints added
- [X] Migrations generated and executed
- [X] `__toString()` methods implemented for all entities
- [X] Schema validation passes
- [X] No Doctrine errors

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

- [X] 7 Entity files in `src/Entity/`
  - Organization.php
  - Person.php
  - CreditedPerson.php
  - Sheet.php
  - Setlist.php
  - SetListItem.php
  - Member.php
- [X] 8 Repository files in `src/Repository/`
  - BaseRepository.php
  - OrganizationRepository.php
  - PersonRepository.php
  - CreditedPersonRepository.php
  - SheetRepository.php
  - SetlistRepository.php
  - SetListItemRepository.php
  - MemberRepository.php
- [X] 1 DTO file in `src/DTO/`
  - SheetReference.php
- [X] Migration files in `migrations/`
- [X] Database schema created

---

## Notes

- ✅ Used PHP 8.1+ attributes for Doctrine mapping
- ✅ Added `__toString()` methods to all entities for EasyAdmin display
- ✅ Implemented multi-tenancy via Organization entity
- ✅ CreditedPerson entity allows flexible person-to-sheet credits (composer, arranger, lyricist, etc.)
- ✅ Member ↔ Organization is ManyToMany to support members in multiple organizations
- ✅ SetListItem ordered by position automatically via `#[ORM\OrderBy]` annotation
- ✅ Migrations tested and executed successfully
- Custom repository methods will be implemented in Epic 3 when needed

---

## Next Epic

**Epic 3**: Basic EasyAdmin CRUD
