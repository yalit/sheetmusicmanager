---
theme: the-unnamed
layout: cover
background: '/images/bg_intro.jpg'
info: |
  ## Life is Easy... as an Admin
  Building admin panels with EasyAdmin 4 and Symfony 7.
---

# Life is Easy... as an Admin

Building admin panels with EasyAdmin 4

<!--
Welcome. Today we're going to build a real-world admin panel with EasyAdmin 4 and Symfony 7 — not a toy CRUD, but something with custom filters, custom actions, inline editing, drag-and-drop, and PDF export.
-->

---
transition: fade-out
layout: two-cols
---

# What we're building

A **sheet music manager** for choirs, bands, and orchestras.

<v-clicks>

- Manage sheets (PDFs, tags, refs, credits)
- Manage persons (composers, arrangers)
- Build setlists and reorder items
- Role-based access for 4 types of users
- PDF generation and export

</v-clicks>

::right::

<div class="mt-[110px]" v-click="1">

````md magic-move {at:2}
``` {*}
Sheet
  ├── title, refs, tags, notes
  ├── files (PDFs)
```

``` {1,4-6}
Sheet
  ├── title, refs, tags, notes
  ├── files (PDFs)
  └── credits → CreditedPerson
                  ├── Person
                  └── PersonType
```

``` {1,8-10}
Sheet
  ├── title, refs, tags, notes
  ├── files (PDFs)
  └── credits → CreditedPerson
                  ├── Person
                  └── PersonType

Setlist
  └── items → SetListItem
                  └── Sheet
```

``` {12-13|*}
Sheet
  ├── title, refs, tags, notes
  ├── files (PDFs)
  └── credits → CreditedPerson
                  ├── Person
                  └── PersonType

Setlist
  └── items → SetListItem
                  └── Sheet

Member (user)
  └── role: member > contributor > librarian > admin
```
````

</div>

<!--
The app is realistic enough to hit non-trivial EasyAdmin patterns — not just a blog with posts and comments.
-->

---
transition: slide-up
layout: center
class: text-center
---

# The starting point

A fresh Symfony 7 app with EasyAdmin 4 installed.

```bash
composer require easycorp/easyadmin-bundle
php bin/console make:admin:dashboard
```

That's it. Let's see what we get.

<!--
No configuration yet. Just the bundle installed and a dashboard controller. Let's look at what EasyAdmin gives us before we write a single custom line.
-->

---
layout: section
background: '/images/bg_basic_crud.jpg'
---

# 1. Basic CRUD

<!--
Before we touch any customization, let's understand the two building blocks EasyAdmin gives us: the dashboard and the CRUD controllers.
-->

---
layout: two-cols
transition: slide-up
---

# A CRUD controller

```bash
php bin/console make:admin:crud PersonCrudController
```

<v-clicks depth="1">

- One entity per controller, extends `AbstractCrudController`
- One method to configure the CRUD class : `configureCRUD()`:
  - labels, search fields ...
- One method to configure the fields: `configureFields()`
  - what renders where ...

</v-clicks>

::right::

<div class="mt-[98px]">
````md magic-move {at:1}
```php {1-2,21|3-6|8-14|15-20} 
class PersonCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string 
    { 
        return Person::class; 
    }

    public function configureCrud(Crud $crud)
    {
        return $crud
            ->setEntityLabelInSingular('Person')
            ->setEntityLabelInPlural('Persons')
            ->setSearchFields(['name']);
    }

    public function configureFields(string $pageName)
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('name', 'Name');
    }
}
```
````
</div>

<!--
This is the complete Person CRUD — nothing else needed. EA reads the Doctrine metadata and builds the list, the form, and the detail page. The $pageName argument to configureFields lets you vary what's shown per page: index, new, edit, or detail.
-->

---
layout: section
background: '/images/bg_security_roles.jpg'
---

# 2. Security & roles

<!--
Four user roles, per-action visibility, per-entity voters. Let's see how these connect.
-->

---
layout: default
---

# Role/Voter → EasyAdmin

- One role per user — a PHP enum stored in the DB

<v-clicks>

- `getRoles()` returns `['ROLE_LIBRARIAN']` — Symfony handles hierarchy
- One voter per entity — `supports()` + `voteOnAttribute()`
- EA wires in via `setPermission()` on each action

</v-clicks>

<div class="mt-3">
````md magic-move {at:1}
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

---
layout: section
background: '/images/bg_custom_filter.jpg'
---

# 3. Custom filter

<!--
EA ships text, numeric, boolean, entity filters. But our files column is a JSON array of value objects — none of those apply. We need a filter that answers one question: does this sheet have a PDF attached?
-->

---
layout: two-cols
---

# **HasPdfFilter** — boolean filter on a JSON column

- Sheet `files` are stored as a JSON array => no built-in "is empty" filter for that

<v-clicks>

- `FilterInterface` + `FilterTrait` — two lines of boilerplate, that's it
- `new()` factory — property, label, form type (`BooleanFilterType` = Yes/No)
- `apply()` — append a raw DQL `WHERE` clause to the QueryBuilder

</v-clicks>

::right::

<div class="mt-[140px]">
````md magic-move {at:1}
```php {1-3,6-7}
// SheetCrudController
public function configureFilters(Filters $filters): Filters
{
    return $filters
        ->add(TextFilter::new('title'))
        ->add(HasPdfFilter::new('files', 'Has PDF'));
}
```

```php
class HasPdfFilter implements FilterInterface
{
    use FilterTrait;
}
```

```php
class HasPdfFilter implements FilterInterface
{
    use FilterTrait;

    public static function new(
        string $propertyName, 
        string $label
    ): self {
        return (new self())
            ->setFilterFqcn(__CLASS__)
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(BooleanFilterType::class);
    }
}
```

```php
class HasPdfFilter implements FilterInterface
{
    ...
    
  public function apply(
      QueryBuilder $queryBuilder,
      FilterDataDto $filterDataDto,
      ?FieldDto $fieldDto,
      EntityDto $entityDto
  ): void {
      $alias = $queryBuilder->getRootAliases()[0];

      if ($filterDataDto->getValue() === true) {
          $queryBuilder->andWhere("$alias.files != '[]'");
      } elseif ($filterDataDto->getValue() === false) {
          $queryBuilder->andWhere("$alias.files = '[]'");
      }
  }
}
```
````
</div>

<!--
FilterTrait provides the fluent setters and the static factory plumbing — you just implement apply(). The form type drives what the user sees in the filter panel; BooleanFilterType renders a Yes/No select. apply() receives the chosen value as a typed PHP bool, not a string. The WHERE clause is raw DQL — you have full control, which is exactly what you need when the column is not a scalar.
Wired in SheetCrudController with a single line: ->add(HasPdfFilter::new('files', 'Has PDF'))
-->

---
layout: section
---

# 4. Custom actions

<!--
EasyAdmin lets you add buttons to any page — index, detail, or as batch actions over selected rows. The pattern behind every action is always the same chain of responsibility.
-->

---
layout: default
background: '/images/bg_actions.jpg'
---

# Custom actions — the pattern

Allows for complete separation of concern in the setup of the actions

<div class="flex items-start justify-center mt-3">
  <div  class="flex flex-col items-center w-36">
    <div v-click class="px-4 py-3 w-full font-bold text-lg text-center">Action</div>
    <div v-click class="text-xs mt-2 opacity-60 font-mono">.linkToRoute()</div>
  </div>

  <div v-click class="flex items-start">
    <div class="flex items-center h-12 px-2 opacity-40 text-xl">→</div>
    <div class="flex flex-col items-center w-36">
      <div class="px-4 py-3 w-full font-bold text-lg text-center">Controller</div>
      <div class="text-xs mt-2 opacity-60 font-mono">Standard controller</div>
    </div>
  </div>

  <div v-click class="flex items-start">
    <div class="flex items-center h-12 px-2 opacity-40 text-xl">→</div>
    <div class="flex flex-col items-center w-36">
      <div class="px-4 py-3 w-full font-bold text-lg text-center">AdminRoute</div>
      <div class="text-xs mt-2 opacity-60 font-mono">#[AdminRoute]</div>
    </div>
  </div>

  <div v-click class="flex items-start">
    <div class="flex items-center h-12 px-2 opacity-40 text-xl">→</div>
    <div class="flex flex-col items-center w-36">
      <div class="px-4 py-3 w-full font-bold text-lg text-center">DTO</div>
      <div class="text-xs mt-2 opacity-60 font-mono">new DTO(...)</div>
    </div>
  </div>

  <div v-click class="flex items-start">
    <div class="flex items-center h-12 px-2 opacity-40 text-xl">→</div>
    <div class="flex flex-col items-center w-36">
      <div class="px-4 py-3 w-full font-bold text-lg text-center">Messenger</div>
      <div class="text-xs mt-2 opacity-60 font-mono">#[AsMessageHandler]</div>
    </div>
  </div>
</div>

<div class="mt-3">
````md magic-move {at:1}
```php
class SetlistCrudController extends AbstractCrudController
{
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, DuplicateSetlistAction::new());
    }
}
```

```php {*|5,8-11}
final class DuplicateSetlistAction
{
    public static function new(): Action
    {
        return Action::new('duplicate', 'Dupliquer')
            ->setIcon('fa fa-copy')
            ->renderAsLink()
            ->linkToRoute(
                'admin_duplicate_setlist',
                fn(Setlist $s) => ['id' => $s->getId()]
            );
    }
}
```

```php {1,3-6,9-13|2|7}
final class DuplicateSetlistController extends AbstractController {
    #[AdminRoute('/setlist/{id}/duplicate', 'duplicate_setlist')]
    public function duplicate(
        Setlist $setlist,
        DuplicateSetlistHandler $handler
    ): Response {
        $new = $handler(new DuplicateSetlist($setlist));
        $this->addFlash('success', '...');
        return $this->redirectToRoute('admin_setlist_edit', [
            'entityId' => $new->getId(),
        ]);
    }
}
```

```php
#[AsMessageHandler]
final readonly class DuplicateSetlistHandler {
    public function __construct(
        private SetlistRepository $repo
    ) {}

    public function __invoke(DuplicateSetlist $command): Setlist
    {
        $new = SetlistFactory::clone($command->setlist);
        $this->repo->save($new, flush: true);
        return $new;
    }
}
```
````
</div>

<!--
The Action object is pure EA config — it knows how to generate the button and the URL. Behind the route everything is plain Symfony. The controller is intentionally thin: it builds the DTO and dispatches it. The handler has no knowledge of HTTP or EasyAdmin — it's a pure service, trivial to test. The double arrow before Messenger signals that this can run async.
-->
