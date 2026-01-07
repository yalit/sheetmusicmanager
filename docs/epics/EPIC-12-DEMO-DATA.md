# Epic 12: Demo Data & Testing

**Branch**: `epic/12-demo-data`
**Status**: ⏳ Pending
**Estimated Effort**: 4-5 hours
**Dependencies**: All previous epics (1-11)

---

## Goal

Create realistic, comprehensive demo data and test all features to ensure the application is ready for the talk demonstration.

---

## Stories

### Story 12.1: Create Organization Fixtures

**Description**: Create realistic organization data for two different types of organizations.

**Tasks**:
- [ ] Create OrganizationFixtures class
- [ ] Add choir organization
- [ ] Add band organization
- [ ] Upload sample logos
- [ ] Set up fixture references

**Technical Details**:

**Organization Fixtures** (`src/DataFixtures/OrganizationFixtures.php`):
```php
<?php

namespace App\DataFixtures;

use App\Entity\Organization;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class OrganizationFixtures extends Fixture
{
    public const ORG_CHOIR = 'org-choir';
    public const ORG_BAND = 'org-band';

    public function load(ObjectManager $manager): void
    {
        // Organization 1: Choir
        $choir = new Organization();
        $choir->setName("St. Mary's Choir");
        $choir->setType('choir');
        $choir->setLogoName('st-marys-logo.png'); // Place in public/uploads/logos/

        $manager->persist($choir);
        $this->addReference(self::ORG_CHOIR, $choir);

        // Organization 2: Band
        $band = new Organization();
        $band->setName('City Jazz Band');
        $band->setType('band');
        $band->setLogoName('jazz-band-logo.png'); // Place in public/uploads/logos/

        $manager->persist($band);
        $this->addReference(self::ORG_BAND, $band);

        $manager->flush();
    }
}
```

**Acceptance Criteria**:
- Two organizations created
- Different types (choir, band)
- Logos referenced (files to be added manually)
- Fixture references set for use in other fixtures

**Deliverables**:
- `src/DataFixtures/OrganizationFixtures.php`
- Sample logo files

---

### Story 12.2: Create Member Fixtures

**Description**: Create test users with different roles for both organizations.

**Tasks**:
- [ ] Create MemberFixtures class
- [ ] Create users for each role type
- [ ] Assign to different organizations
- [ ] Document credentials

**Technical Details**:

**Member Fixtures** (`src/DataFixtures/MemberFixtures.php`):
```php
<?php

namespace App\DataFixtures;

use App\Entity\Member;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class MemberFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        $choirOrg = $this->getReference(OrganizationFixtures::ORG_CHOIR);
        $bandOrg = $this->getReference(OrganizationFixtures::ORG_BAND);

        // St. Mary's Choir Users
        $users = [
            [
                'name' => 'John Member',
                'email' => 'member@stmarys.org',
                'roles' => ['ROLE_MEMBER'],
                'org' => $choirOrg,
                'ref' => 'user-choir-member',
            ],
            [
                'name' => 'Sarah Librarian',
                'email' => 'librarian@stmarys.org',
                'roles' => ['ROLE_LIBRARIAN'],
                'org' => $choirOrg,
                'ref' => 'user-choir-librarian',
            ],
            [
                'name' => 'Michael Conductor',
                'email' => 'conductor@stmarys.org',
                'roles' => ['ROLE_CONDUCTOR'],
                'org' => $choirOrg,
                'ref' => 'user-choir-conductor',
            ],

            // City Jazz Band Users
            [
                'name' => 'Admin User',
                'email' => 'admin@cityjazz.org',
                'roles' => ['ROLE_ADMIN'],
                'org' => $bandOrg,
                'ref' => 'user-band-admin',
            ],
            [
                'name' => 'Jazz Member',
                'email' => 'member@cityjazz.org',
                'roles' => ['ROLE_MEMBER'],
                'org' => $bandOrg,
                'ref' => 'user-band-member',
            ],
        ];

        foreach ($users as $userData) {
            $user = new Member();
            $user->setName($userData['name']);
            $user->setEmail($userData['email']);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
            $user->setRoles($userData['roles']);
            $user->setOrganization($userData['org']);

            $manager->persist($user);
            $this->addReference($userData['ref'], $user);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [OrganizationFixtures::class];
    }
}
```

**Acceptance Criteria**:
- Five test users created
- Different roles represented
- Both organizations have users
- All passwords set to 'password123'
- References set for blameable fields

**Deliverables**:
- `src/DataFixtures/MemberFixtures.php`

---

### Story 12.3: Create Person Fixtures (Composers/Arrangers)

**Description**: Create realistic composer and arranger data.

**Tasks**:
- [ ] Create PersonFixtures class
- [ ] Add famous classical composers
- [ ] Add modern composers
- [ ] Add arrangers
- [ ] Distribute across organizations

**Technical Details**:

**Person Fixtures** (`src/DataFixtures/PersonFixtures.php`):
```php
<?php

namespace App\DataFixtures;

use App\Entity\Person;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PersonFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $choirOrg = $this->getReference(OrganizationFixtures::ORG_CHOIR);
        $bandOrg = $this->getReference(OrganizationFixtures::ORG_BAND);

        // Choir composers
        $choirComposers = [
            ['name' => 'Johann Sebastian Bach', 'type' => 'composer'],
            ['name' => 'Wolfgang Amadeus Mozart', 'type' => 'composer'],
            ['name' => 'Franz Schubert', 'type' => 'composer'],
            ['name' => 'John Rutter', 'type' => 'composer'],
            ['name' => 'Morten Lauridsen', 'type' => 'composer'],
            ['name' => 'Eric Whitacre', 'type' => 'composer'],
            ['name' => 'David Willcocks', 'type' => 'both'],
            ['name' => 'John Williams', 'type' => 'arranger'],
        ];

        foreach ($choirComposers as $index => $data) {
            $person = new Person();
            $person->setName($data['name']);
            $person->setType($data['type']);
            $person->setOrganization($choirOrg);

            $manager->persist($person);
            $this->addReference('choir-person-' . $index, $person);
        }

        // Band composers
        $bandComposers = [
            ['name' => 'Duke Ellington', 'type' => 'composer'],
            ['name' => 'Miles Davis', 'type' => 'composer'],
            ['name' => 'Charlie Parker', 'type' => 'composer'],
            ['name' => 'John Coltrane', 'type' => 'composer'],
            ['name' => 'Thelonious Monk', 'type' => 'composer'],
            ['name' => 'Bill Evans', 'type' => 'both'],
            ['name' => 'Quincy Jones', 'type' => 'arranger'],
        ];

        foreach ($bandComposers as $index => $data) {
            $person = new Person();
            $person->setName($data['name']);
            $person->setType($data['type']);
            $person->setOrganization($bandOrg);

            $manager->persist($person);
            $this->addReference('band-person-' . $index, $person);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [OrganizationFixtures::class];
    }
}
```

**Acceptance Criteria**:
- 15+ composers/arrangers created
- Mix of famous classical and modern names
- Appropriate types (composer, arranger, both)
- Distributed across organizations
- References set for sheet fixtures

**Deliverables**:
- `src/DataFixtures/PersonFixtures.php`

---

### Story 12.4: Create Sheet Fixtures with Realistic Data

**Description**: Create comprehensive sheet data with variety in all fields.

**Tasks**:
- [ ] Create SheetFixtures class
- [ ] Add 30-40 sheets total
- [ ] Vary difficulty, genre, status
- [ ] Add reference codes
- [ ] Add sample PDF references
- [ ] Assign composers and arrangers

**Technical Details**:

**Sheet Fixtures** (`src/DataFixtures/SheetFixtures.php`):
```php
<?php

namespace App\DataFixtures;

use App\Entity\Sheet;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SheetFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $choirOrg = $this->getReference(OrganizationFixtures::ORG_CHOIR);

        // Choir sheets
        $choirSheets = [
            [
                'title' => 'Ave Maria',
                'genre' => 'Sacred',
                'difficulty' => 'intermediate',
                'duration' => '4:30',
                'key' => 'D Major',
                'composer' => 'choir-person-0', // Bach
                'status' => 'active',
                'refs' => [
                    ['reference_code' => 'CAT-001', 'reference_type' => 'catalog'],
                    ['reference_code' => 'BWV-232', 'reference_type' => 'publisher'],
                ],
            ],
            [
                'title' => 'Requiem in D Minor',
                'genre' => 'Classical',
                'difficulty' => 'advanced',
                'duration' => '90:00',
                'key' => 'D Minor',
                'composer' => 'choir-person-1', // Mozart
                'status' => 'active',
                'refs' => [
                    ['reference_code' => 'K-626', 'reference_type' => 'catalog'],
                ],
            ],
            [
                'title' => 'Ave Verum Corpus',
                'genre' => 'Sacred',
                'difficulty' => 'beginner',
                'duration' => '3:15',
                'key' => 'D Major',
                'composer' => 'choir-person-1', // Mozart
                'status' => 'active',
                'refs' => [
                    ['reference_code' => 'K-618', 'reference_type' => 'catalog'],
                ],
            ],
            [
                'title' => 'The Lord Bless You and Keep You',
                'genre' => 'Sacred',
                'difficulty' => 'beginner',
                'duration' => '2:45',
                'key' => 'F Major',
                'composer' => 'choir-person-3', // John Rutter
                'status' => 'active',
                'refs' => [],
            ],
            [
                'title' => 'Lux Aurumque',
                'genre' => 'Contemporary',
                'difficulty' => 'intermediate',
                'duration' => '3:45',
                'key' => 'C Minor',
                'composer' => 'choir-person-5', // Eric Whitacre
                'status' => 'active',
                'refs' => [
                    ['reference_code' => 'EW-2000', 'reference_type' => 'publisher'],
                ],
            ],
            [
                'title' => 'O Magnum Mysterium',
                'genre' => 'Sacred',
                'difficulty' => 'advanced',
                'duration' => '6:30',
                'key' => 'E flat Major',
                'composer' => 'choir-person-4', // Morten Lauridsen
                'status' => 'active',
                'refs' => [],
            ],
            [
                'title' => 'Silent Night (arranged)',
                'genre' => 'Christmas',
                'difficulty' => 'beginner',
                'duration' => '3:00',
                'key' => 'C Major',
                'composer' => 'choir-person-2', // Schubert (original)
                'arranger' => 'choir-person-6', // Willcocks
                'status' => 'active',
                'refs' => [
                    ['reference_code' => 'XMAS-001', 'reference_type' => 'internal'],
                ],
            ],
            [
                'title' => 'Mass in B Minor (Kyrie)',
                'genre' => 'Classical',
                'difficulty' => 'advanced',
                'duration' => '12:00',
                'key' => 'B Minor',
                'composer' => 'choir-person-0', // Bach
                'status' => 'active',
                'refs' => [
                    ['reference_code' => 'BWV-232', 'reference_type' => 'catalog'],
                ],
            ],
            [
                'title' => 'Old Practice Piece',
                'genre' => 'Exercise',
                'difficulty' => 'beginner',
                'duration' => '2:00',
                'key' => 'C Major',
                'composer' => null,
                'status' => 'archived',
                'refs' => [],
            ],
        ];

        foreach ($choirSheets as $index => $data) {
            $sheet = new Sheet();
            $sheet->setTitle($data['title']);
            $sheet->setGenre($data['genre']);
            $sheet->setDifficulty($data['difficulty']);
            $sheet->setDuration($data['duration']);
            $sheet->setKeySignature($data['key']);
            $sheet->setStatus($data['status']);
            $sheet->setReferences($data['refs']);
            $sheet->setOrganization($choirOrg);

            if (isset($data['composer']) && $data['composer']) {
                $sheet->setComposer($this->getReference($data['composer']));
            }

            if (isset($data['arranger']) && $data['arranger']) {
                $sheet->setArranger($this->getReference($data['arranger']));
            }

            // Some sheets have PDFs (reference only - files to be added manually)
            if ($index % 3 === 0) {
                $sheet->setPdfFileName('sample-sheet-' . $index . '.pdf');
            }

            // Some sheets have covers
            if ($index % 4 === 0) {
                $sheet->setCoverImageName('sample-cover-' . $index . '.jpg');
            }

            $manager->persist($sheet);
            $this->addReference('choir-sheet-' . $index, $sheet);
        }

        // Add similar data for band organization
        // ... (jazz standards, etc.)

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            OrganizationFixtures::class,
            PersonFixtures::class,
        ];
    }
}
```

**Acceptance Criteria**:
- 30-40 sheets created
- Variety in all fields
- Mix of difficulties and genres
- Some with PDFs, some without
- Some with covers, some without
- References included
- Realistic data

**Deliverables**:
- `src/DataFixtures/SheetFixtures.php`

---

### Story 12.5: Create Setlist and SetlistItem Fixtures

**Description**: Create realistic setlists with sheets.

**Tasks**:
- [ ] Create SetlistFixtures class
- [ ] Add 5-8 setlists
- [ ] Different statuses (draft, finalized, performed)
- [ ] Add realistic occasions
- [ ] Create SetlistItems linking sheets
- [ ] Order items by position

**Technical Details**:

**Setlist Fixtures** (`src/DataFixtures/SetlistFixtures.php`):
```php
<?php

namespace App\DataFixtures;

use App\Entity\Setlist;
use App\Entity\SetlistItem;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SetlistFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $choirOrg = $this->getReference(OrganizationFixtures::ORG_CHOIR);

        // Christmas Concert 2025
        $christmas = new Setlist();
        $christmas->setName('Christmas Concert 2025');
        $christmas->setOccasion('Annual Christmas Eve Service');
        $christmas->setEventDate(new \DateTime('2025-12-24'));
        $christmas->setStatus('finalized');
        $christmas->setOrganization($choirOrg);
        $manager->persist($christmas);

        // Add items to Christmas setlist
        $christmasSheets = [
            ['sheet' => 'choir-sheet-6', 'position' => 1, 'name' => 'Processional'],
            ['sheet' => 'choir-sheet-3', 'position' => 2, 'name' => 'Opening Hymn'],
            ['sheet' => 'choir-sheet-0', 'position' => 3, 'name' => 'Anthem'],
        ];

        foreach ($christmasSheets as $itemData) {
            $item = new SetlistItem();
            $item->setSetlist($christmas);
            $item->setSheet($this->getReference($itemData['sheet']));
            $item->setPosition($itemData['position']);
            $item->setName($itemData['name']);
            $manager->persist($item);
        }

        // Easter Service 2026
        $easter = new Setlist();
        $easter->setName('Easter Sunday Service');
        $easter->setOccasion('Easter Celebration');
        $easter->setEventDate(new \DateTime('2026-04-05'));
        $easter->setStatus('draft');
        $easter->setNotes('Need to finalize order with Pastor');
        $easter->setOrganization($choirOrg);
        $manager->persist($easter);

        // Add items to Easter setlist
        $easterSheets = [
            ['sheet' => 'choir-sheet-0', 'position' => 1, 'name' => 'Entrance'],
            ['sheet' => 'choir-sheet-1', 'position' => 2, 'name' => 'Gloria'],
        ];

        foreach ($easterSheets as $itemData) {
            $item = new SetlistItem();
            $item->setSetlist($easter);
            $item->setSheet($this->getReference($itemData['sheet']));
            $item->setPosition($itemData['position']);
            $item->setName($itemData['name']);
            $manager->persist($item);
        }

        // Past Concert (Performed)
        $past = new Setlist();
        $past->setName('Autumn Concert 2024');
        $past->setOccasion('Fall Recital');
        $past->setEventDate(new \DateTime('2024-10-15'));
        $past->setStatus('performed');
        $past->setOrganization($choirOrg);
        $manager->persist($past);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            OrganizationFixtures::class,
            SheetFixtures::class,
        ];
    }
}
```

**Acceptance Criteria**:
- 5-8 setlists created
- Different statuses represented
- Realistic occasions and dates
- SetlistItems created with positions
- Items link to existing sheets

**Deliverables**:
- `src/DataFixtures/SetlistFixtures.php`

---

### Story 12.6: Upload Sample Files

**Description**: Add sample PDF and image files for realistic demo.

**Tasks**:
- [ ] Find or create sample sheet music PDFs
- [ ] Create sample cover images
- [ ] Create organization logos
- [ ] Place files in correct directories
- [ ] Update fixtures to reference files

**Acceptance Criteria**:
- Sample PDF files in `/public/uploads/sheets/`
- Sample images in `/public/uploads/covers/`
- Organization logos in `/public/uploads/logos/`
- Files referenced in fixtures
- Files display correctly in admin

**Deliverables**:
- Sample files in correct directories

---

### Story 12.7: Create Comprehensive Manual Testing Checklist

**Description**: Document all features to test before the talk.

**Tasks**:
- [ ] List all features
- [ ] Create test scenarios for each role
- [ ] Document expected outcomes
- [ ] Create testing script

**Technical Details**:

**Testing Checklist** (`docs/TESTING_CHECKLIST.md`):
```markdown
# Manual Testing Checklist

## Test Users
- member@stmarys.org / password123 (ROLE_MEMBER)
- librarian@stmarys.org / password123 (ROLE_LIBRARIAN)
- conductor@stmarys.org / password123 (ROLE_CONDUCTOR)
- admin@cityjazz.org / password123 (ROLE_ADMIN)

## Feature Tests

### Authentication
- [ ] Can log in with all test users
- [ ] Remember me checkbox works
- [ ] Can log out
- [ ] Cannot access /admin without login

### Multi-Tenancy
- [ ] Choir users only see choir data
- [ ] Band users only see band data
- [ ] Cannot access other org's data via URL

### Role-Based Access
- [ ] ROLE_MEMBER: read-only access
- [ ] ROLE_LIBRARIAN: can manage sheets
- [ ] ROLE_CONDUCTOR: can manage sheets and setlists
- [ ] ROLE_ADMIN: full access

... (continue with all features)
```

**Acceptance Criteria**:
- Comprehensive checklist created
- All features included
- Test scenarios documented
- Can be completed in 30-45 minutes

**Deliverables**:
- `docs/TESTING_CHECKLIST.md`

---

## Epic Acceptance Criteria

- [ ] Realistic demo data created
- [ ] Two organizations with complete data
- [ ] Test users for all roles
- [ ] 30-40 sheets with variety
- [ ] 5-8 setlists with items
- [ ] Sample files uploaded
- [ ] All fixtures load without errors
- [ ] Manual testing checklist complete
- [ ] All features tested and working

---

## Testing Checklist

```bash
# Load Fixtures
php bin/console doctrine:fixtures:load

# Verify Data
- [ ] Organizations created
- [ ] Test users can log in
- [ ] Sheets display with variety
- [ ] Setlists have items
- [ ] Files display correctly

# Test Features
- [ ] All CRUD operations work
- [ ] Filters work
- [ ] Custom actions work
- [ ] Multi-tenancy works
- [ ] Security works
- [ ] Files upload/download
- [ ] JavaScript features work
```

---

## Deliverables

- [ ] `src/DataFixtures/OrganizationFixtures.php`
- [ ] `src/DataFixtures/MemberFixtures.php`
- [ ] `src/DataFixtures/PersonFixtures.php`
- [ ] `src/DataFixtures/SheetFixtures.php`
- [ ] `src/DataFixtures/SetlistFixtures.php`
- [ ] Sample PDF files
- [ ] Sample image files
- [ ] Organization logos
- [ ] `docs/TESTING_CHECKLIST.md`
- [ ] `docs/TEST_USERS.md`
- [ ] Fully populated demo database

---

## Notes

- Realistic data makes demos more compelling
- Variety in data allows demonstrating all features
- Test users enable showing different access levels
- Comprehensive testing prevents surprises during talk
- Sample files make the demo feel professional

---

## Next Epic

**Epic 13**: Talk Preparation & Polish
