# Multi-Tenancy: Architecture Analysis

> Captured for future reference. Not implemented in the current talk demo.

---

## Scope decision

Multi-tenancy was deferred from the talk demo (Epic 5). The app is intentionally
single-tenant for the demo. This document records the design decisions and open
questions so the feature can be picked up later.

---

## Confirmed design decisions

- Every piece of data (Sheet, Setlist, Person, PersonType, …) is **org-scoped** —
  there is no shared/global data between organisations.
- `Member` remains the **authentication holder** (user identity: email, password,
  name). It is NOT org-scoped itself.
- Role is **per-organisation**, not global. A user can be Admin in org A and
  Member in org B.

---

## Required features

### Registration & onboarding
- Self-registration form with **email confirmation** (token-based, expires).
- On first registration: **auto-create an Organisation** and make the registrant
  its Admin.
- **Password reset** ("forgot my password") — mandatory, was omitted in initial
  scoping.

### Invite system
- Admin (and optionally Librarian) can generate an **invite link** for their org.
- Invite links must have a **TTL** (e.g. 7 days) and be **single-use** (or
  explicitly reusable — decision pending).
- The **role to grant** to the invitee is set at invite-creation time by the Admin.
- Following an invite link:
  - If not registered → registration form, then linked to the inviting org (no
    new org created).
  - If already registered → just linked to the org (no registration step).

### Multi-org session
- On login: if the user belongs to **multiple orgs**, show a picker modal.
  If only one org, select it automatically.
- Store the chosen org in a **cookie** (persists across browser close).
- Provide an **org switcher** in the nav bar for mid-session switching.
- Guard: if the cookie references an org the user has been removed from, fall
  back gracefully (redirect to picker).

### Org context
- A service/provider exposes the **active Organisation** to the rest of the app
  (voters, query extensions, controllers).
- All voters must be updated to check org membership + role, not just the global
  Symfony role.

---

## Architectural refactor required

### Entity split: User + OrgMembership

The current `Member` entity conflates identity and membership. It must be split:

```
User           id, email, password, name
OrgMembership  user_id → User, org_id → Organisation, role (MemberRole enum)
Organisation   id, name, slug, created_at
```

This is a **breaking change** to the existing codebase:
- All existing voters need updating (check OrgMembership, not Member.role).
- All CRUD controllers need the active-org filter.
- All tests need updating (fixture structure changes).
- Migrations required.

### Data isolation

All entity tables (Sheet, Setlist, Person, PersonType, …) gain an
`organisation_id FK NOT NULL` column. EasyAdmin query extensions filter
automatically by the active org.

---

## Open questions

| Question | Status |
|---|---|
| Can the last Admin of an org leave / be demoted? | Must be guarded — no. |
| Single-use invite links or reusable? | Pending |
| Who can generate invite links — Admin only or Librarian too? | Pending |
| What happens to `createdBy` data when a user leaves an org? | Pending |
| Organisation deletion — is it supported? What happens to its data? | Pending |
| Organisation profile (logo, description, timezone)? | Pending |

---

## Effort estimate

Full implementation is approximately **3–5× the effort of Epics 3+4 combined**,
covering: registration flows, email infrastructure, invite system, entity
refactor, data migration, security layer updates, and test suite rewrite.
