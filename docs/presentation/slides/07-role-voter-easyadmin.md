---
layout: default
---

# Role/Voter → EasyAdmin


<v-clicks>

- One role per user — a PHP enum stored in the DB
- `getRoles()` returns `['ROLE_LIBRARIAN']` — Symfony handles hierarchy
- One voter per entity — `supports()` + `voteOnAttribute()`
- EasyAdmin wires in via `setPermission()` on each action

</v-clicks>

<div class="mt-3" v-click="1">

````md magic-move {at:2}
```php
// Single role per user — no array, no flags
enum MemberRole: string {
    case Member      = 'member';
    case Contributor = 'contributor';
    case Librarian   = 'librarian';
    case Admin       = 'admin';
}
```

```php
// Member.php
public function getRoles(): array
{
    return ['ROLE_' . strtoupper($this->role->value)];
    // ROLE_MEMBER -> member
    // ROLE_CONTRIBUTOR...
}

// security.yaml
    role_hierarchy:
        ROLE_CONTRIBUTOR: ROLE_MEMBER
        ROLE_LIBRARIAN: [ROLE_MEMBER, ROLE_CONTRIBUTOR]
        ROLE_ADMIN: [ROLE_MEMBER, ROLE_CONTRIBUTOR, ROLE_LIBRARIAN]
```

```php
// SheetVoter — one voter, all Sheet actions
protected function voteOnAttribute(
    string $attribute, ...
): bool {
    return match ($attribute) {
        self::INDEX => $this->security->isGranted('ROLE_MEMBER'),
        self::EDIT => ...
        self::NEW, self::DELETE =>  ...
        default => false,
    };
}
```

```php
// SheetCrudController — wire voter into EA actions
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
````
</div>

<!--
The enum keeps role assignment simple — one column, no JSON array. getRoles() translates it to the string Symfony expects. The voter concentrates all Sheet authorization in one place — no scattered isGranted() calls. setPermission() on EasyAdmin actions tells EA to call isGranted(SheetVoter::EDIT, $entity) before showing the Edit button — if it returns false, the button simply doesn't appear.
-->
