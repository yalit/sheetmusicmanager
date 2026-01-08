# Epic 5: Multi-Tenancy Implementation

**Branch**: `epic/05-multi-tenancy`
**Status**: ⏳ Pending
**Estimated Effort**: 2-3 hours
**Dependencies**: Epic 4 (Security)

---

## Goal

Implement organization-based data isolation to ensure users can only access data belonging to their organization.

---

## Stories

### Story 5.1: Create Query Extension for Organization Filtering

**Description**: Implement Doctrine query extension to automatically filter all queries by the current user's organization.

**Tasks**:
- [ ] Create OrganizationExtension class
- [ ] Implement QueryCollectionExtensionInterface
- [ ] Apply filter to all organization-scoped entities
- [ ] Get current user's organization
- [ ] Register extension as service

**Technical Details**:

**Query Extension** (`src/Doctrine/Extension/OrganizationExtension.php`):
```php
<?php

namespace App\Doctrine\Extension;

use App\Entity\Organization;
use App\Entity\Person;
use App\Entity\Sheet;
use App\Entity\Setlist;
use App\Entity\Member;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use Symfony\Component\Security\Core\Security;

class OrganizationExtension
{
    public function __construct(
        private Security $security,
        private AdminContextProvider $adminContextProvider
    ) {}

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        string $entityClass
    ): void {
        $user = $this->security->getUser();

        // Only apply to authenticated users
        if (!$user instanceof Member) {
            return;
        }

        // Skip for admin viewing organizations or members
        $context = $this->adminContextProvider->getContext();
        if ($context && in_array($context->getCrud()?->getEntityFqcn(), [Organization::class, Member::class])) {
            // Admins can see all organizations/members, others see only their own
            if (!$this->security->isGranted('ROLE_ADMIN')) {
                $rootAlias = $queryBuilder->getRootAliases()[0];
                $queryBuilder
                    ->andWhere("$rootAlias.organization = :user_organization")
                    ->setParameter('user_organization', $user->getOrganization());
            }
            return;
        }

        // Apply organization filter to all organization-scoped entities
        $organizationScopedEntities = [
            Sheet::class,
            Setlist::class,
            Person::class,
        ];

        if (in_array($entityClass, $organizationScopedEntities)) {
            $rootAlias = $queryBuilder->getRootAliases()[0];
            $queryBuilder
                ->andWhere("$rootAlias.organization = :user_organization")
                ->setParameter('user_organization', $user->getOrganization());
        }
    }
}
```

**Service Configuration** (`config/services.yaml`):
```yaml
services:
    App\Doctrine\Extension\OrganizationExtension:
        tags:
            - { name: easyadmin.orm.query_extension }
```

**Acceptance Criteria**:
- Query extension created
- Automatically filters by user's organization
- Applied to Sheet, Setlist, Person entities
- Does not apply to Organization and Member (admins need to see all)
- Registered as EasyAdmin query extension

**Deliverables**:
- `src/Doctrine/Extension/OrganizationExtension.php`
- Service configuration

---

### Story 5.2: Create Entity Listener for Auto-Setting Organization

**Description**: Implement Doctrine entity listener to automatically set organization on new entities.

**Tasks**:
- [ ] Create OrganizationListener class
- [ ] Listen to prePersist event
- [ ] Auto-set organization from current user
- [ ] Apply to all organization-scoped entities
- [ ] Register listener

**Technical Details**:

**Entity Listener** (`src/EventListener/OrganizationListener.php`):
```php
<?php

namespace App\EventListener;

use App\Entity\Sheet;
use App\Entity\Setlist;
use App\Entity\Person;
use App\Entity\Member;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Security;

class OrganizationListener
{
    public function __construct(private Security $security)
    {
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        $user = $this->security->getUser();

        // Only auto-set for authenticated users
        if (!$user instanceof Member) {
            return;
        }

        // Auto-set organization on new entities
        if (method_exists($entity, 'setOrganization') && method_exists($entity, 'getOrganization')) {
            if (!$entity->getOrganization()) {
                $entity->setOrganization($user->getOrganization());
            }
        }
    }
}
```

**Service Configuration** (`config/services.yaml`):
```yaml
services:
    App\EventListener\OrganizationListener:
        tags:
            - { name: doctrine.event_listener, event: prePersist }
```

**Alternative: Using Doctrine Extensions** (`src/Entity/Sheet.php`):
```php
use Gedmo\Mapping\Annotation as Gedmo;

class Sheet
{
    // ... other properties

    /**
     * @Gedmo\Blameable(on="create", field="organization")
     */
    #[ORM\ManyToOne(targetEntity: Organization::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Organization $organization = null;

    // ... methods
}
```

**Acceptance Criteria**:
- Entity listener created
- Automatically sets organization on persist
- Gets organization from current authenticated user
- Works for Sheet, Setlist, Person entities
- Does not override manually set organization

**Deliverables**:
- `src/EventListener/OrganizationListener.php`
- Service registration

---

### Story 5.3: Update CRUD Controllers for Multi-Tenancy

**Description**: Hide organization field from forms and ensure queries respect organization scope.

**Tasks**:
- [ ] Hide organization field in forms (auto-set)
- [ ] Show organization in list/detail views (read-only)
- [ ] Update Sheet CRUD controller
- [ ] Update Setlist CRUD controller
- [ ] Update Person CRUD controller
- [ ] Test with multiple users

**Technical Details**:

**Sheet CRUD Controller** (`src/Controller/Admin/SheetCrudController.php`):
```php
public function configureFields(string $pageName): iterable
{
    yield IdField::new('id')->onlyOnIndex();
    yield TextField::new('title');
    // ... other fields

    // Organization: show on index/detail, but not on forms
    yield AssociationField::new('organization')
        ->onlyOnIndex()
        ->onlyOnDetail()
        ->setPermission('ROLE_ADMIN'); // Only admins see which org

    // ... rest of fields
}
```

**Setlist CRUD Controller** (`src/Controller/Admin/SetlistCrudController.php`):
```php
public function configureFields(string $pageName): iterable
{
    yield IdField::new('id')->onlyOnIndex();
    yield TextField::new('name');
    // ... other fields

    yield AssociationField::new('organization')
        ->hideOnForm() // Auto-set by listener
        ->setPermission('ROLE_ADMIN');

    // ... rest of fields
}
```

**Person CRUD Controller** (`src/Controller/Admin/PersonCrudController.php`):
```php
public function configureFields(string $pageName): iterable
{
    yield IdField::new('id')->onlyOnIndex();
    yield TextField::new('name');
    yield ChoiceField::new('type')
        ->setChoices([
            'Composer' => 'composer',
            'Arranger' => 'arranger',
            'Both' => 'both',
        ]);

    yield AssociationField::new('organization')
        ->hideOnForm()
        ->setPermission('ROLE_ADMIN');

    // ... rest of fields
}
```

**Acceptance Criteria**:
- Organization field hidden on all forms
- Organization auto-set by entity listener
- Organization visible in list/detail views (admin only)
- Users cannot manually change organization
- All queries filtered by organization automatically

**Deliverables**:
- Updated CRUD controllers
- Multi-tenancy enforced at query level

---

### Story 5.4: Test Multi-Tenancy Isolation

**Description**: Comprehensive testing to ensure complete data isolation between organizations.

**Tasks**:
- [ ] Create test scenario with two organizations
- [ ] Create test users for each organization
- [ ] Create test data for each organization
- [ ] Verify User A cannot see User B's data
- [ ] Verify no data leakage via direct URLs
- [ ] Test with all entity types

**Technical Details**:

**Test Scenarios**:

1. **User A (St. Mary's Choir)**:
   - Login as conductor@stmarys.org
   - Create sheets, setlists, persons
   - Verify only sees St. Mary's data

2. **User B (City Jazz Band)**:
   - Login as admin@cityjazz.org
   - Create sheets, setlists, persons
   - Verify only sees City Jazz data

3. **Cross-Organization Access Attempt**:
   - User A logged in
   - Try to access User B's entity via direct URL
   - Should get 404 or Access Denied

**Manual Test Script**:
```bash
# 1. Login as conductor@stmarys.org
# 2. Go to Sheets list
# 3. Note the IDs visible (e.g., ID 1, 2, 3)
# 4. Create a new sheet - should auto-assign to St. Mary's

# 5. Logout
# 6. Login as admin@cityjazz.org
# 7. Go to Sheets list
# 8. Should NOT see sheets 1, 2, 3
# 9. Create a new sheet - should auto-assign to City Jazz

# 10. Try to access /admin?crudAction=detail&entityId=1 (St. Mary's sheet)
# 11. Should get 404 or Access Denied

# 12. Verify Person, Setlist entities have same behavior
```

**Automated Test** (`tests/Functional/MultiTenancyTest.php`):
```php
<?php

namespace App\Tests\Functional;

use App\Entity\Member;
use App\Entity\Sheet;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MultiTenancyTest extends WebTestCase
{
    public function testUserCanOnlySeeOwnOrganizationData(): void
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine')->getManager();

        // Get users from different organizations
        $userChoir = $entityManager->getRepository(Member::class)
            ->findOneBy(['email' => 'conductor@stmarys.org']);
        $userBand = $entityManager->getRepository(Member::class)
            ->findOneBy(['email' => 'admin@cityjazz.org']);

        // Login as choir user
        $client->loginUser($userChoir);

        // Get sheets - should only see choir sheets
        $choirSheets = $entityManager->getRepository(Sheet::class)->findAll();
        foreach ($choirSheets as $sheet) {
            $this->assertEquals($userChoir->getOrganization(), $sheet->getOrganization());
        }

        // Try to access a band sheet (should fail)
        $bandSheet = $entityManager->getRepository(Sheet::class)
            ->createQueryBuilder('s')
            ->where('s.organization = :org')
            ->setParameter('org', $userBand->getOrganization())
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($bandSheet) {
            $client->request('GET', '/admin?crudAction=detail&entityId=' . $bandSheet->getId());
            $this->assertResponseStatusCodeSame(404);
        }
    }
}
```

**Acceptance Criteria**:
- Users only see data from their organization
- Creating entities auto-assigns to user's organization
- Direct URL access to other org's data blocked
- No SQL queries return cross-organization data
- All entity types properly isolated
- Automated test passes

**Deliverables**:
- Test scenarios documented
- Manual test results
- Automated test (optional)

---

### Story 5.5: Add Organization Switcher for Admins (Optional)

**Description**: Allow super-admins to switch between organizations for demo/support purposes.

**Tasks**:
- [ ] Create organization switcher in admin header
- [ ] Store selected organization in session
- [ ] Update query extension to use session org
- [ ] Only visible to ROLE_SUPER_ADMIN
- [ ] Add visual indicator of current organization

**Technical Details**:

**Organization Switcher Controller** (`src/Controller/Admin/OrganizationSwitcherController.php`):
```php
<?php

namespace App\Controller\Admin;

use App\Entity\Organization;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class OrganizationSwitcherController extends AbstractController
{
    #[Route('/switch-organization/{id}', name: 'admin_switch_organization')]
    public function switchOrganization(
        Organization $organization,
        Request $request
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $request->getSession()->set('impersonate_organization', $organization->getId());

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('admin'));
    }

    #[Route('/clear-organization-switch', name: 'admin_clear_organization_switch')]
    public function clearSwitch(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $request->getSession()->remove('impersonate_organization');

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('admin'));
    }
}
```

**Updated Query Extension**:
```php
public function applyToCollection(
    QueryBuilder $queryBuilder,
    string $entityClass
): void {
    $user = $this->security->getUser();

    if (!$user instanceof Member) {
        return;
    }

    // Check for organization override (super admin feature)
    $organization = $user->getOrganization();

    if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
        $session = $this->requestStack->getCurrentRequest()?->getSession();
        $overrideOrgId = $session?->get('impersonate_organization');

        if ($overrideOrgId) {
            $organization = $this->entityManager
                ->getRepository(Organization::class)
                ->find($overrideOrgId);
        }
    }

    // ... rest of filter logic using $organization
}
```

**Dashboard Header Template** (`templates/admin/dashboard.html.twig`):
```twig
{% extends '@EasyAdmin/page/content.html.twig' %}

{% block content_header_wrapper %}
    {{ parent() }}

    {% if is_granted('ROLE_SUPER_ADMIN') %}
        <div class="alert alert-warning">
            <strong>Organization Switcher:</strong>
            Current: {{ app.session.get('impersonate_organization') ?? 'Your Organization' }}
            <div class="btn-group">
                {% for org in organizations %}
                    <a href="{{ path('admin_switch_organization', {id: org.id}) }}"
                       class="btn btn-sm btn-outline-primary">
                        {{ org.name }}
                    </a>
                {% endfor %}
                <a href="{{ path('admin_clear_organization_switch') }}"
                   class="btn btn-sm btn-outline-danger">
                    Clear
                </a>
            </div>
        </div>
    {% endif %}
{% endblock %}
```

**Acceptance Criteria**:
- Organization switcher visible only to ROLE_SUPER_ADMIN
- Can switch between organizations
- Selected organization persists in session
- Query extension respects switched organization
- Clear switch button returns to own organization
- Visual indicator shows current organization

**Deliverables**:
- `src/Controller/Admin/OrganizationSwitcherController.php`
- Updated query extension
- Organization switcher UI in dashboard

**Note**: This story is optional and primarily useful for demo purposes during the talk.

---

## Epic Acceptance Criteria

- [ ] Query extension automatically filters by organization
- [ ] Entity listener auto-sets organization on new entities
- [ ] Organization field hidden from forms
- [ ] Users only see their organization's data
- [ ] No cross-organization data leakage
- [ ] Direct URL access to other org's entities blocked
- [ ] Multi-tenancy tested with multiple users
- [ ] Works seamlessly with security layer (Epic 4)
- [ ] Optional: Super admin can switch organizations

---

## Testing Checklist

Comprehensive multi-tenancy testing:

```bash
# Test Data Isolation
- [ ] Login as conductor@stmarys.org
- [ ] Create sheet "Test Sheet Choir"
- [ ] See only choir sheets in list
- [ ] Logout

- [ ] Login as admin@cityjazz.org
- [ ] Create sheet "Test Sheet Band"
- [ ] See only band sheets in list
- [ ] Should NOT see "Test Sheet Choir"

# Test Auto-Assignment
- [ ] Login as librarian@stmarys.org
- [ ] Create new sheet
- [ ] Verify organization auto-set to St. Mary's Choir
- [ ] Organization field not visible in form

# Test Access Control
- [ ] Login as conductor@stmarys.org
- [ ] Note ID of a choir sheet (e.g., ID 5)
- [ ] Logout

- [ ] Login as admin@cityjazz.org
- [ ] Try to access /admin?entityId=5 directly
- [ ] Should get 404 or Access Denied

# Test All Entities
- [ ] Repeat above tests for Sheet, Setlist, Person
- [ ] Verify isolation works for all entity types

# Test with Different Roles
- [ ] Test with ROLE_MEMBER (read-only)
- [ ] Test with ROLE_LIBRARIAN
- [ ] Test with ROLE_CONDUCTOR
- [ ] Test with ROLE_ADMIN
- [ ] All should see only their org's data
```

---

## Deliverables

- [ ] `src/Doctrine/Extension/OrganizationExtension.php`
- [ ] `src/EventListener/OrganizationListener.php`
- [ ] Updated CRUD controllers (hide organization field)
- [ ] Service registrations
- [ ] Test results documenting isolation
- [ ] Optional: Organization switcher for super admins
- [ ] Documentation on multi-tenancy architecture

---

## Notes

- Multi-tenancy is enforced at the query level, making it very secure
- Entity listener ensures organization is always set automatically
- Combines with voters from Epic 4 for defense-in-depth security
- Query extension works seamlessly with EasyAdmin's query system
- Organization switcher is useful for demos and support, but not required

---

## Next Epic

**Epic 6**: File & Image Handling
