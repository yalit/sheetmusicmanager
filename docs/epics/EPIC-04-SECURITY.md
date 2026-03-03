# Epic 4: Authentication & Security Layer

**Branch**: `epic/03-easyadmin`
**Status**: ✅ Implemented
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
- [x] Configure firewall for /admin routes (form_login, logout, remember_me)
- [x] Set up access control rules
- [x] Define role hierarchy

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
        ROLE_CONTRIBUTOR: ROLE_MEMBER
        ROLE_LIBRARIAN: [ROLE_MEMBER, ROLE_CONTRIBUTOR]
        ROLE_ADMIN: [ROLE_MEMBER, ROLE_CONTRIBUTOR, ROLE_LIBRARIAN]
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
- [x] Define ROLE_MEMBER (base role — read-only access)
- [x] Define ROLE_CONTRIBUTOR (can manage setlists)
- [x] Define ROLE_LIBRARIAN (can manage sheets and persons)
- [x] Define ROLE_ADMIN (full access including members)
- [x] Document role permissions

**Technical Details**:

**Role Hierarchy** (`config/packages/security.yaml`):
```yaml
role_hierarchy:
    ROLE_CONTRIBUTOR: ROLE_MEMBER
    ROLE_LIBRARIAN: [ROLE_MEMBER, ROLE_CONTRIBUTOR]
    ROLE_ADMIN: [ROLE_MEMBER, ROLE_CONTRIBUTOR, ROLE_LIBRARIAN]
```

**Permission Matrix** (per CRUD action):

| Entity | Action | ROLE_MEMBER | ROLE_CONTRIBUTOR | ROLE_LIBRARIAN | ROLE_ADMIN |
|--------|--------|:-----------:|:----------------:|:--------------:|:----------:|
| Sheet | INDEX | ✅ | ✅ | ✅ | ✅ |
| Sheet | DETAIL | ❌ | ❌ | ❌ | ❌ |
| Sheet | NEW | ❌ | ❌ | ✅ | ✅ |
| Sheet | EDIT | ❌ | ✅ | ✅ | ✅ |
| Sheet | DELETE | ❌ | ❌ | ✅ | ✅ |
| Setlist | INDEX | ✅ | ✅ | ✅ | ✅ |
| Setlist | DETAIL | ✅ | ✅ | ✅ | ✅ |
| Setlist | NEW | ✅ | ✅ | ✅ | ✅ |
| Setlist | EDIT | own only | ✅ | ✅ | ✅ |
| Setlist | DELETE | own only | ✅ | ✅ | ✅ |
| Person | INDEX | ✅ | ✅ | ✅ | ✅ |
| Person | DETAIL | ✅ | ✅ | ✅ | ✅ |
| Person | NEW | ❌ | ❌ | ✅ | ✅ |
| Person | EDIT | ❌ | ❌ | ✅ | ✅ |
| Person | DELETE | ❌ | ❌ | ✅ | ✅ |
| PersonType | INDEX | ✅ | ✅ | ✅ | ✅ |
| PersonType | DETAIL | ✅ | ✅ | ✅ | ✅ |
| PersonType | NEW | ❌ | ❌ | ✅ | ✅ |
| PersonType | EDIT | ❌ | ❌ | ✅ | ✅ |
| PersonType | DELETE | ❌ | ❌ | ✅ | ✅ |
| Member | INDEX | ❌ | ❌ | ❌ | ✅ |
| Member | DETAIL | ❌ | ❌ | ❌ | ✅ |
| Member | NEW | ❌ | ❌ | ❌ | ✅ |
| Member | EDIT | ❌ | ❌ | ❌ | ✅ |
| Member | DELETE | ❌ | ❌ | ❌ | ✅ |

Notes:
- Sheet DETAIL is disabled for all roles (no detail page for sheets).
- Setlist EDIT/DELETE "own only" means ROLE_MEMBER can manage setlists they created
  (matched via `createdBy` field against the authenticated user's email identifier).

**Acceptance Criteria**:
- Role hierarchy defined in security.yaml
- Roles inherit permissions from lower roles
- Permission matrix documented

**Deliverables**:
- Documented role hierarchy
- Permission matrix

---

### Story 4.4: Implement EasyAdmin Permission System

**Description**: Configure permissions on CRUD actions using Symfony Voters (one voter per entity).

**Tasks**:
- [x] Configure permissions on Sheet CRUD actions via SheetVoter
- [x] Configure permissions on Setlist CRUD actions via SetlistVoter
- [x] Configure permissions on Member CRUD actions via MemberVoter
- [x] Configure permissions on Person CRUD actions via PersonVoter
- [x] Configure permissions on PersonType CRUD actions via PersonTypeVoter
- [x] Test each role's access

**Technical Details**:

Permissions are wired through voter constants rather than raw role strings. Each controller delegates to its voter:

```php
// Example: SheetCrudController
public function configureActions(Actions $actions): Actions
{
    return $actions
        ->setPermission(Action::INDEX,  SheetVoter::INDEX)
        ->setPermission(Action::DETAIL, SheetVoter::DETAIL)
        ->setPermission(Action::NEW,    SheetVoter::NEW)
        ->setPermission(Action::EDIT,   SheetVoter::EDIT)
        ->setPermission(Action::DELETE, SheetVoter::DELETE);
}
```

**Acceptance Criteria**:
- [x] Permissions configured on all CRUD actions
- [x] ROLE_MEMBER can view sheets/setlists/persons/person types; can create and manage own setlists
- [x] ROLE_CONTRIBUTOR can additionally edit sheets and manage any setlist
- [x] ROLE_LIBRARIAN can additionally create/edit/delete sheets, persons, person types
- [x] ROLE_ADMIN has full access including members
- [x] Unauthorized actions show 403 error

**Deliverables**:
- [x] Updated CRUD controllers with voter-based permission configuration

---

### Story 4.5: Create Security Voters

**Description**: One voter per entity covering all five CRUD actions (INDEX, DETAIL, NEW, EDIT, DELETE).

**Tasks**:
- [x] Create SheetVoter
- [x] Create SetlistVoter (with ownership logic for EDIT/DELETE)
- [x] Create MemberVoter
- [x] Create PersonVoter
- [x] Create PersonTypeVoter
- [x] Wire voters into CRUD controllers via `setPermission()`
- [x] Test voter logic with DataProvider-based integration tests

**Technical Details**:

**`supports()` pattern**: EasyAdmin calls the voter with three different subject shapes:
- `null` when checking whether to display the NEW toolbar button
- `EntityClass::class` (string) when checking INDEX and NEW page access
- entity instance for DETAIL, EDIT, and DELETE

```php
protected function supports(string $attribute, mixed $subject): bool
{
    return ($attribute === self::NEW && $subject === null)
        || (in_array($attribute, [self::INDEX, self::NEW]) && $subject === Sheet::class)
        || (in_array($attribute, [self::DETAIL, self::EDIT, self::DELETE]) && $subject instanceof Sheet);
}
```

**SetlistVoter** uses ownership logic for EDIT and DELETE: any user with `ROLE_CONTRIBUTOR` may manage any setlist; a plain `ROLE_MEMBER` may only manage setlists where `createdBy` matches their identifier.

```php
private function voteOnOwned(mixed $subject, TokenInterface $token): bool
{
    if ($this->security->isGranted('ROLE_CONTRIBUTOR')) {
        return true;
    }
    $user = $token->getUser();
    if (!$user instanceof UserInterface || !$subject instanceof Setlist) {
        return false;
    }
    return $subject->getCreatedBy() === $user->getUserIdentifier();
}
```

**Acceptance Criteria**:
- [x] One voter per entity, covering all five actions
- [x] Voter constants used as permission strings in CRUD controllers
- [x] Setlist ownership enforced for EDIT/DELETE
- [x] Integration tests cover all role × action combinations

**Deliverables**:
- [x] `src/Security/Voter/SheetVoter.php`
- [x] `src/Security/Voter/SetlistVoter.php`
- [x] `src/Security/Voter/MemberVoter.php`
- [x] `src/Security/Voter/PersonVoter.php`
- [x] `src/Security/Voter/PersonTypeVoter.php`

---

### Story 4.6: Protect Custom Actions with Roles

**Description**: Add role checks to custom actions and display actions conditionally.

**Status**: ⏳ Pending — no custom actions exist yet. To be revisited when custom actions are introduced in later epics.

**Tasks**:
- [ ] Add permission checks to custom actions
- [ ] Configure `displayIf` conditions based on voter checks
- [ ] Test action visibility with different roles
- [ ] Ensure unauthorized direct access returns 403

---

### Story 4.7: Create Test Fixtures

**Description**: Create Doctrine fixtures for all entities to support automated testing and local development.

**Tasks**:
- [x] Create `MemberFixtures` — one member per role (`member/contributor/librarian/admin @sheetmusic.test`)
- [x] Create `PersonTypeFixtures` — Composer, Arranger, Conductor
- [x] Create `PersonFixtures` — Bach, Mozart, Brahms
- [x] Create `SheetFixtures` — Toccata and Fugue, Eine Kleine Nachtmusik
- [x] Create `SetlistFixtures` — one per role owner (Member/Contributor/Librarian Setlist), `createdBy` set via raw SQL
- [x] Wire fixture loading into `test-init` task (`Taskfile.yml`)

**Fixture credentials** (password: `password` for all):

| Email | Role |
|-------|------|
| `member@sheetmusic.test` | ROLE_MEMBER |
| `contributor@sheetmusic.test` | ROLE_CONTRIBUTOR |
| `librarian@sheetmusic.test` | ROLE_LIBRARIAN |
| `admin@sheetmusic.test` | ROLE_ADMIN |

**Acceptance Criteria**:
- [x] One fixture member per role
- [x] Fixtures loaded automatically by `task test-init`
- [x] Tests rely solely on fixture data (no test-created entities)

**Deliverables**:
- [x] `src/DataFixtures/MemberFixtures.php`
- [x] `src/DataFixtures/PersonTypeFixtures.php`
- [x] `src/DataFixtures/PersonFixtures.php`
- [x] `src/DataFixtures/SheetFixtures.php`
- [x] `src/DataFixtures/SetlistFixtures.php`

---

## Epic Acceptance Criteria

- [x] Security bundle configured
- [x] Login/logout working
- [x] Remember me functionality working
- [x] Role hierarchy defined (ROLE_CONTRIBUTOR, ROLE_LIBRARIAN, ROLE_ADMIN)
- [x] Permissions configured on all CRUD actions via voters
- [x] Voters enforce per-entity access control (including NEW button visibility)
- [x] Test fixtures created for all four roles and all entities
- [x] All roles tested with DataProvider-based integration tests (access + UI action visibility)
- [x] Unauthorized access returns 403
- [ ] Custom actions protected (pending Story 4.6)

---

## Remaining

### Story 4.6 — Custom action protection
No custom actions exist yet. Once custom CRUD actions are added in later epics, each one must:
- Use `->setPermission()` with a voter constant, or `->displayIf()` with a role check
- Have a corresponding `denyAccessUnlessGranted()` guard in the action method
- Be covered by a `testXxxDisplayOnIndex` test in the relevant test class

---

## Testing

Automated integration tests cover all role × action combinations for every CRUD controller.
Each test class uses PHPUnit `#[DataProvider]` and covers:
- **Access** (`testIndexAccess`, `testNewAccess`, `testEditAccess`, `testDetailAccess`, `testDeleteAccess`)
- **UI visibility** (`testNewDisplayOnIndex`, `testEditDisplayOnIndex`, `testDeleteAccess` on index row)

Test files:
- `tests/Admin/Controller/SheetCrudControllerTest.php`
- `tests/Admin/Controller/SetlistCrudControllerTest.php`
- `tests/Admin/Controller/MemberCrudControllerTest.php`
- `tests/Admin/Controller/PersonCrudControllerTest.php`
- `tests/Admin/Controller/PersonTypeCrudControllerTest.php`

Run tests after initialising the test database:
```bash
task test-init   # drops DB, runs migrations, loads fixtures
task tests
```

---

## Deliverables

- [x] `config/packages/security.yaml`
- [x] `src/Controller/SecurityController.php`
- [x] `templates/security/login.html.twig`
- [x] `src/Security/Voter/SheetVoter.php`
- [x] `src/Security/Voter/SetlistVoter.php`
- [x] `src/Security/Voter/MemberVoter.php`
- [x] `src/Security/Voter/PersonVoter.php`
- [x] `src/Security/Voter/PersonTypeVoter.php`
- [x] `src/DataFixtures/MemberFixtures.php`
- [x] `src/DataFixtures/PersonTypeFixtures.php`
- [x] `src/DataFixtures/PersonFixtures.php`
- [x] `src/DataFixtures/SheetFixtures.php`
- [x] `src/DataFixtures/SetlistFixtures.php`
- [x] Updated CRUD controllers with voter-based permission configuration
- [x] Working authentication and authorization system
- [ ] Custom action protection (Story 4.6)

---

## Next Epic

**Epic 5**: Multi-Tenancy Implementation
