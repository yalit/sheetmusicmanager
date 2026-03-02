# Epic 4: Authentication & Security Layer

**Branch**: `epic/04-security`
**Status**: ⏳ Pending
**Estimated Effort**: 3-4 hours
**Dependencies**: Epic 3 (Basic Admin)

---

## Goal

Implement comprehensive authentication, authorization, and role-based access control (RBAC) for the Sheet Music Manager application.

---

## Stories

### Story 4.1: Configure Security Bundle

**Description**: Set up Symfony security configuration with password hashing and user provider.

**Tasks**:
- [x] Configure `security.yaml`
- [x] Set password hasher to `auto` (bcrypt/argon2)
- [x] Configure user provider using Member entity (property: email)
- [ ] Configure firewall for /admin routes (form_login, logout, remember_me missing)
- [ ] Set up access control rules (currently commented out)
- [ ] Define role hierarchy

**Technical Details**:

**Configuration** (`config/packages/security.yaml`):
```yaml
security:
    password_hashers:
        Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface: 'auto'
        App\Entity\Member:
            algorithm: auto

    providers:
        app_user_provider:
            entity:
                class: App\Entity\Member
                property: email

    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false

        main:
            lazy: true
            provider: app_user_provider
            form_login:
                login_path: admin_login
                check_path: admin_login
                enable_csrf: true
                default_target_path: admin
            logout:
                path: admin_logout
                target: admin_login
            remember_me:
                secret: '%kernel.secret%'
                lifetime: 604800
                path: /admin

    access_control:
        - { path: ^/admin/login, roles: PUBLIC_ACCESS }
        - { path: ^/admin, roles: ROLE_MEMBER }

    role_hierarchy:
        ROLE_LIBRARIAN: ROLE_MEMBER
        ROLE_CONDUCTOR: [ROLE_MEMBER, ROLE_LIBRARIAN]
        ROLE_ADMIN: [ROLE_MEMBER, ROLE_LIBRARIAN, ROLE_CONDUCTOR]
```

**Acceptance Criteria**:
- Security configuration complete
- Password hashing configured
- User provider set to Member entity
- Firewall configured for /admin
- Role hierarchy defined

**Deliverables**:
- `config/packages/security.yaml`

---

### Story 4.2: Create Login System

**Description**: Implement login and logout functionality with remember me feature.

**Tasks**:
- [x] Create login controller (`src/Controller/SecurityController.php`)
- [x] Create login form template (extends `@EasyAdmin/page/login.html.twig`)
- [x] Configure authentication (form_login in security.yaml)
- [x] Add remember me checkbox
- [x] Create logout route
- [x] Style login page (EasyAdmin theme)

**Technical Details**:

**Login Controller** (`src/Controller/SecurityController.php`):
```php
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/admin/login', name: 'admin_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('admin');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/admin/logout', name: 'admin_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
```

**Login Template** (`templates/security/login.html.twig`):
```twig
{% extends 'base.html.twig' %}

{% block title %}Log in - Sheet Music Manager{% endblock %}

{% block body %}
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Sheet Music Manager</h4>
                </div>
                <div class="card-body">
                    {% if error %}
                        <div class="alert alert-danger">{{ error.messageKey|trans(error.messageData, 'security') }}</div>
                    {% endif %}

                    <form method="post">
                        <div class="mb-3">
                            <label for="username" class="form-label">Email</label>
                            <input type="email"
                                   class="form-control"
                                   id="username"
                                   name="_username"
                                   value="{{ last_username }}"
                                   required
                                   autofocus>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password"
                                   class="form-control"
                                   id="password"
                                   name="_password"
                                   required>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox"
                                   class="form-check-input"
                                   id="remember_me"
                                   name="_remember_me">
                            <label class="form-check-label" for="remember_me">
                                Remember me
                            </label>
                        </div>

                        <input type="hidden" name="_csrf_token" value="{{ csrf_token('authenticate') }}">

                        <button type="submit" class="btn btn-primary w-100">Sign in</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
{% endblock %}
```

**Acceptance Criteria**:
- [x] Login form displays correctly (EasyAdmin theme)
- [x] Users can log in with email/password
- [x] Remember me functionality works
- [x] Logout works
- [x] CSRF protection enabled
- [x] Redirects to dashboard after login

**Deliverables**:
- [x] `src/Controller/SecurityController.php`
- [x] `templates/security/login.html.twig`

---

### Story 4.3: Configure Role Hierarchy

**Description**: Define role hierarchy and permissions structure.

**Tasks**:
- [ ] Define ROLE_MEMBER (base role)
- [ ] Define ROLE_LIBRARIAN (can manage sheets)
- [ ] Define ROLE_CONDUCTOR (can manage sheets + setlists)
- [ ] Define ROLE_ADMIN (full access)
- [ ] Document role permissions

**Technical Details**:

**Role Hierarchy** (already in security.yaml):
```yaml
role_hierarchy:
    ROLE_LIBRARIAN: ROLE_MEMBER
    ROLE_CONDUCTOR: [ROLE_MEMBER, ROLE_LIBRARIAN]
    ROLE_ADMIN: [ROLE_MEMBER, ROLE_LIBRARIAN, ROLE_CONDUCTOR]
```

**Permission Matrix**:

| Role | View All | Manage Sheets | Manage Setlists | Manage Members | Manage Organizations |
|------|----------|---------------|-----------------|----------------|---------------------|
| ROLE_MEMBER | ✅ | ❌ | ❌ | ❌ | ❌ |
| ROLE_LIBRARIAN | ✅ | ✅ | ❌ | ❌ | ❌ |
| ROLE_CONDUCTOR | ✅ | ✅ | ✅ | ❌ | ❌ |
| ROLE_ADMIN | ✅ | ✅ | ✅ | ✅ | ✅ |

**Acceptance Criteria**:
- Role hierarchy defined in security.yaml
- Roles inherit permissions from lower roles
- Permission matrix documented
- Clear understanding of each role's capabilities

**Deliverables**:
- Documented role hierarchy
- Permission matrix

---

### Story 4.4: Implement EasyAdmin Permission System

**Description**: Configure permissions on CRUD actions based on roles.

**Tasks**:
- [ ] Configure permissions on Sheet CRUD actions
- [ ] Configure permissions on Setlist CRUD actions
- [ ] Configure permissions on Member CRUD actions
- [ ] Configure permissions on Organization CRUD actions
- [ ] Test each role's access

**Technical Details**:

**Sheet CRUD Controller** (`src/Controller/Admin/SheetCrudController.php`):
```php
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;

public function configureActions(Actions $actions): Actions
{
    return $actions
        ->setPermission(Action::INDEX, 'ROLE_MEMBER')
        ->setPermission(Action::DETAIL, 'ROLE_MEMBER')
        ->setPermission(Action::NEW, 'ROLE_LIBRARIAN')
        ->setPermission(Action::EDIT, 'ROLE_LIBRARIAN')
        ->setPermission(Action::DELETE, 'ROLE_ADMIN');
}
```

**Setlist CRUD Controller** (`src/Controller/Admin/SetlistCrudController.php`):
```php
public function configureActions(Actions $actions): Actions
{
    return $actions
        ->setPermission(Action::INDEX, 'ROLE_MEMBER')
        ->setPermission(Action::DETAIL, 'ROLE_MEMBER')
        ->setPermission(Action::NEW, 'ROLE_CONDUCTOR')
        ->setPermission(Action::EDIT, 'ROLE_CONDUCTOR')
        ->setPermission(Action::DELETE, 'ROLE_ADMIN');
}
```

**Member CRUD Controller** (`src/Controller/Admin/MemberCrudController.php`):
```php
public function configureActions(Actions $actions): Actions
{
    return $actions
        ->setPermission(Action::INDEX, 'ROLE_ADMIN')
        ->setPermission(Action::DETAIL, 'ROLE_ADMIN')
        ->setPermission(Action::NEW, 'ROLE_ADMIN')
        ->setPermission(Action::EDIT, 'ROLE_ADMIN')
        ->setPermission(Action::DELETE, 'ROLE_ADMIN');
}
```

**Organization CRUD Controller** (`src/Controller/Admin/OrganizationCrudController.php`):
```php
public function configureActions(Actions $actions): Actions
{
    return $actions
        ->setPermission(Action::INDEX, 'ROLE_ADMIN')
        ->setPermission(Action::DETAIL, 'ROLE_ADMIN')
        ->setPermission(Action::NEW, 'ROLE_ADMIN')
        ->setPermission(Action::EDIT, 'ROLE_ADMIN')
        ->setPermission(Action::DELETE, 'ROLE_ADMIN');
}
```

**Acceptance Criteria**:
- Permissions configured on all CRUD actions
- ROLE_MEMBER can only view
- ROLE_LIBRARIAN can manage sheets
- ROLE_CONDUCTOR can manage sheets and setlists
- ROLE_ADMIN has full access
- Unauthorized actions show 403 error

**Deliverables**:
- Updated CRUD controllers with permission configuration

---

### Story 4.5: Create Security Voters

**Description**: Implement voters for organization-scoped access control.

**Tasks**:
- [ ] Create SheetVoter for organization-based access
- [ ] Create SetlistVoter for organization-based + status access
- [ ] Implement voting logic
- [ ] Test voter logic with different users

**Technical Details**:

**Sheet Voter** (`src/Security/Voter/SheetVoter.php`):
```php
<?php

namespace App\Security\Voter;

use App\Entity\Sheet;
use App\Entity\Member;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class SheetVoter extends Voter
{
    public const VIEW = 'VIEW';
    public const EDIT = 'EDIT';
    public const DELETE = 'DELETE';

    public function __construct(private Security $security)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])
            && $subject instanceof Sheet;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof Member) {
            return false;
        }

        /** @var Sheet $sheet */
        $sheet = $subject;

        // Users can only access sheets from their organization
        if ($sheet->getOrganization() !== $user->getOrganization()) {
            return false;
        }

        return match($attribute) {
            self::VIEW => $this->canView($sheet, $user),
            self::EDIT => $this->canEdit($sheet, $user),
            self::DELETE => $this->canDelete($sheet, $user),
            default => false,
        };
    }

    private function canView(Sheet $sheet, Member $user): bool
    {
        // All members can view sheets from their organization
        return $this->security->isGranted('ROLE_MEMBER');
    }

    private function canEdit(Sheet $sheet, Member $user): bool
    {
        // Librarians and above can edit
        return $this->security->isGranted('ROLE_LIBRARIAN');
    }

    private function canDelete(Sheet $sheet, Member $user): bool
    {
        // Only admins can delete
        return $this->security->isGranted('ROLE_ADMIN');
    }
}
```

**Setlist Voter** (`src/Security/Voter/SetlistVoter.php`):
```php
<?php

namespace App\Security\Voter;

use App\Entity\Setlist;
use App\Entity\Member;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class SetlistVoter extends Voter
{
    public const VIEW = 'VIEW';
    public const EDIT = 'EDIT';
    public const DELETE = 'DELETE';
    public const FINALIZE = 'FINALIZE';
    public const MARK_PERFORMED = 'MARK_PERFORMED';

    public function __construct(private Security $security)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE, self::FINALIZE, self::MARK_PERFORMED])
            && $subject instanceof Setlist;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof Member) {
            return false;
        }

        /** @var Setlist $setlist */
        $setlist = $subject;

        // Users can only access setlists from their organization
        if ($setlist->getOrganization() !== $user->getOrganization()) {
            return false;
        }

        return match($attribute) {
            self::VIEW => $this->canView($setlist, $user),
            self::EDIT => $this->canEdit($setlist, $user),
            self::DELETE => $this->canDelete($setlist, $user),
            self::FINALIZE => $this->canFinalize($setlist, $user),
            self::MARK_PERFORMED => $this->canMarkPerformed($setlist, $user),
            default => false,
        };
    }

    private function canView(Setlist $setlist, Member $user): bool
    {
        return $this->security->isGranted('ROLE_MEMBER');
    }

    private function canEdit(Setlist $setlist, Member $user): bool
    {
        // Can only edit if draft status and has ROLE_CONDUCTOR
        return $setlist->getStatus() === 'draft'
            && $this->security->isGranted('ROLE_CONDUCTOR');
    }

    private function canDelete(Setlist $setlist, Member $user): bool
    {
        // Can only delete drafts and must be admin
        return $setlist->getStatus() === 'draft'
            && $this->security->isGranted('ROLE_ADMIN');
    }

    private function canFinalize(Setlist $setlist, Member $user): bool
    {
        return $setlist->getStatus() === 'draft'
            && $this->security->isGranted('ROLE_CONDUCTOR');
    }

    private function canMarkPerformed(Setlist $setlist, Member $user): bool
    {
        return $setlist->getStatus() === 'finalized'
            && $this->security->isGranted('ROLE_CONDUCTOR');
    }
}
```

**Acceptance Criteria**:
- Voters created for Sheet and Setlist
- Organization scoping enforced in voters
- Status-based access for setlists implemented
- Voters tested with different user scenarios

**Deliverables**:
- `src/Security/Voter/SheetVoter.php`
- `src/Security/Voter/SetlistVoter.php`

---

### Story 4.6: Protect Custom Actions with Roles

**Description**: Add role checks to custom actions and display actions conditionally.

**Tasks**:
- [ ] Add permission checks to custom actions
- [ ] Configure displayIf conditions based on roles
- [ ] Test action visibility with different roles
- [ ] Ensure unauthorized access returns 403

**Technical Details**:

**Example Custom Action with Role Check**:
```php
// In CRUD controller
public function configureActions(Actions $actions): Actions
{
    $exportAction = Action::new('export', 'Export')
        ->linkToCrudAction('export')
        ->setIcon('fa fa-download')
        ->displayIf(fn () => $this->isGranted('ROLE_LIBRARIAN'));

    $archiveAction = Action::new('archive', 'Archive')
        ->linkToCrudAction('archive')
        ->setIcon('fa fa-archive')
        ->displayIf(fn () => $this->isGranted('ROLE_ADMIN'));

    return $actions
        ->add(Crud::PAGE_INDEX, $exportAction)
        ->add(Crud::PAGE_DETAIL, $archiveAction);
}

// Action method with authorization check
public function archive(AdminContext $context): Response
{
    $this->denyAccessUnlessGranted('ROLE_ADMIN');

    $sheet = $context->getEntity()->getInstance();
    $sheet->setStatus('archived');
    $this->entityManager->flush();

    return $this->redirect($context->getReferrer());
}
```

**Acceptance Criteria**:
- Custom actions protected with role checks
- Actions display only for authorized roles
- Unauthorized direct access blocked
- Clear error messages for unauthorized attempts

**Deliverables**:
- Protected custom actions
- Conditional action display

---

### Story 4.7: Create Test Users

**Description**: Create test users for each role for demonstration purposes.

**Tasks**:
- [ ] Create fixtures for test users
- [ ] One user per role type
- [ ] Users belong to different organizations
- [ ] Document test user credentials
- [ ] Seed users via fixtures

**Technical Details**:

**Fixtures** (`src/DataFixtures/MemberFixtures.php`):
```php
<?php

namespace App\DataFixtures;

use App\Entity\Member;
use App\Entity\Organization;
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
        $choirOrg = $this->getReference('org-choir');
        $bandOrg = $this->getReference('org-band');

        // ROLE_MEMBER - St. Mary's Choir
        $member = new Member();
        $member->setName('John Member');
        $member->setEmail('member@stmarys.org');
        $member->setPassword($this->passwordHasher->hashPassword($member, 'password123'));
        $member->setRoles(['ROLE_MEMBER']);
        $member->setOrganization($choirOrg);
        $manager->persist($member);

        // ROLE_LIBRARIAN - St. Mary's Choir
        $librarian = new Member();
        $librarian->setName('Sarah Librarian');
        $librarian->setEmail('librarian@stmarys.org');
        $librarian->setPassword($this->passwordHasher->hashPassword($librarian, 'password123'));
        $librarian->setRoles(['ROLE_LIBRARIAN']);
        $librarian->setOrganization($choirOrg);
        $manager->persist($librarian);

        // ROLE_CONDUCTOR - St. Mary's Choir
        $conductor = new Member();
        $conductor->setName('Michael Conductor');
        $conductor->setEmail('conductor@stmarys.org');
        $conductor->setPassword($this->passwordHasher->hashPassword($conductor, 'password123'));
        $conductor->setRoles(['ROLE_CONDUCTOR']);
        $conductor->setOrganization($choirOrg);
        $manager->persist($conductor);

        // ROLE_ADMIN - City Jazz Band
        $admin = new Member();
        $admin->setName('Admin User');
        $admin->setEmail('admin@cityjazz.org');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'password123'));
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setOrganization($bandOrg);
        $manager->persist($admin);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            OrganizationFixtures::class,
        ];
    }
}
```

**Test User Credentials Document** (`docs/TEST_USERS.md`):
```markdown
# Test Users

## St. Mary's Choir (Organization ID: 1)

### Read-Only Member
- Email: member@stmarys.org
- Password: password123
- Role: ROLE_MEMBER
- Can: View all entities
- Cannot: Create/edit/delete anything

### Librarian
- Email: librarian@stmarys.org
- Password: password123
- Role: ROLE_LIBRARIAN
- Can: View all, manage sheets
- Cannot: Manage setlists, members, organizations

### Conductor
- Email: conductor@stmarys.org
- Password: password123
- Role: ROLE_CONDUCTOR
- Can: View all, manage sheets and setlists
- Cannot: Manage members, organizations

## City Jazz Band (Organization ID: 2)

### Administrator
- Email: admin@cityjazz.org
- Password: password123
- Role: ROLE_ADMIN
- Can: Everything
```

**Acceptance Criteria**:
- Four test users created
- One user per role type
- Two different organizations represented
- Users can log in successfully
- Each user has appropriate access level
- Credentials documented

**Deliverables**:
- `src/DataFixtures/MemberFixtures.php`
- `docs/TEST_USERS.md`

---

## Epic Acceptance Criteria

- [ ] Security bundle configured
- [ ] Login/logout working
- [ ] Remember me functionality working
- [ ] Role hierarchy defined
- [ ] Permissions configured on all CRUD actions
- [ ] Voters enforce organization-scoped access
- [ ] Custom actions protected by roles
- [ ] Test users created for all roles
- [ ] All roles tested and working correctly
- [ ] Users only see their organization's data
- [ ] Unauthorized access properly blocked

---

## Testing Checklist

Manual testing with each test user:

```bash
# Start server
symfony server:start

# Test ROLE_MEMBER (member@stmarys.org / password123)
- [ ] Can log in
- [ ] Can view sheets, setlists
- [ ] Cannot create/edit sheets
- [ ] Cannot create/edit setlists
- [ ] Cannot access members/organizations

# Test ROLE_LIBRARIAN (librarian@stmarys.org / password123)
- [ ] Can log in
- [ ] Can view sheets, setlists
- [ ] Can create/edit/delete sheets
- [ ] Cannot create/edit setlists
- [ ] Cannot access members/organizations

# Test ROLE_CONDUCTOR (conductor@stmarys.org / password123)
- [ ] Can log in
- [ ] Can view all entities
- [ ] Can manage sheets
- [ ] Can manage setlists
- [ ] Cannot access members/organizations

# Test ROLE_ADMIN (admin@cityjazz.org / password123)
- [ ] Can log in
- [ ] Can access all entities
- [ ] Can manage everything
- [ ] Only sees City Jazz Band data (multi-tenancy)

# Test Multi-tenancy
- [ ] Member from choir org cannot see band data
- [ ] Admin from band org cannot see choir data
```

---

## Deliverables

- [ ] `config/packages/security.yaml`
- [ ] `src/Controller/SecurityController.php`
- [ ] `templates/security/login.html.twig`
- [ ] `src/Security/Voter/SheetVoter.php`
- [ ] `src/Security/Voter/SetlistVoter.php`
- [ ] `src/DataFixtures/MemberFixtures.php`
- [ ] `docs/TEST_USERS.md`
- [ ] Updated CRUD controllers with permissions
- [ ] Working authentication and authorization system

---

## Notes

- Role hierarchy allows for clean permission inheritance
- Voters provide fine-grained access control beyond simple role checks
- Test users enable effective demonstration of security features during talk
- Organization scoping in voters complements multi-tenancy implementation (Epic 5)

---

## Next Epic

**Epic 5**: Multi-Tenancy Implementation
