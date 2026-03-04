# EasyAdmin Custom Actions

## Architecture

Custom actions follow a 5-layer separation — each layer has a single responsibility and is independently testable.

```
src/Admin/Action/
    XxxAction.php          ← EA action definition (config only)
    Xxx.php                ← DTO / command
    XxxHandler.php         ← business logic (CQRS handler)

src/Controller/Action/
    XxxController.php      ← dedicated Symfony controller

src/Entity/Factory/
    XxxFactory.php         ← entity construction / cloning
```

---

## Layer by layer

### 1. Action definition — `src/Admin/Action/XxxAction.php`

Pure EA configuration. Returns an `Action` instance configured with `linkToRoute()` pointing
at the dedicated controller's route. Never contains business logic.

```php
final class DuplicateSetlistAction
{
    public static function new(): Action
    {
        return Action::new('duplicate', 'Dupliquer')
            ->setIcon('fa fa-copy')
            ->renderAsLink()
            ->linkToRoute('admin_duplicate_setlist', fn(Setlist $s) => ['id' => $s->getId()]);
    }
}
```

Register in the CRUD controller's `configureActions()`:

```php
return $actions
    ->add(Crud::PAGE_INDEX, DuplicateSetlistAction::new())
    // ...
```

Use `linkToRoute()` (not `linkToCrudAction()`) so the business logic lives outside the CRUD
controller. The route name must match the `#[AdminRoute]` name on the dedicated controller.

---

### 2. DTO / command — `src/Admin/Action/Xxx.php`

A plain value object carrying the data needed to execute the operation. No logic.

```php
final class DuplicateSetlist
{
    public function __construct(public readonly Setlist $setlist) {}
}
```

---

### 3. Handler — `src/Admin/Action/XxxHandler.php`

Executes the business logic. Depends on repositories and factories; never on EA or HTTP.
`__invoke()` makes it directly callable, compatible with Symfony Messenger if needed later.

```php
final readonly class DuplicateSetlistHandler
{
    public function __construct(private SetlistRepository $setlistRepository) {}

    public function __invoke(DuplicateSetlist $command): Setlist
    {
        $copy = SetlistFactory::clone($command->setlist);
        $this->setlistRepository->save($copy, flush: true);
        return $copy;
    }
}
```

---

### 4. Dedicated controller — `src/Controller/Action/XxxController.php`

A standard Symfony `AbstractController` (not a CRUD controller). Receives the entity via
route parameter (Symfony's ParamConverter), calls the handler, then redirects.

```php
final class DuplicateSetlistController extends AbstractController
{
    #[AdminRoute('/setlist/{id}/duplicate', name: 'duplicate_setlist')]
    public function duplicate(Setlist $setlist, DuplicateSetlistHandler $handler): Response
    {
        $copy = $handler(new DuplicateSetlist($setlist));
        $this->addFlash('success', sprintf('Setlist "%s" dupliquée.', $setlist->getTitle()));
        return $this->redirectToRoute('admin_setlist_edit', ['entityId' => $copy->getId()]);
    }
}
```

`#[AdminRoute]` registers the route under EA's admin prefix and firewall automatically.
The route name becomes `admin_duplicate_setlist` (EA prefixes with `admin_`).

---

### 5. Entity factories — `src/Entity/Factory/XxxFactory.php`

Static factories encapsulate entity construction and cloning. Keeps that logic out of
handlers, controllers, and fixtures.

```php
class SetlistFactory
{
    public static function clone(Setlist $setlist): Setlist
    {
        $copy = new Setlist();
        $copy->setTitle($setlist->getTitle());
        $copy->setNotes($setlist->getNotes());
        $copy->setDate(new \DateTime());
        foreach ($setlist->getItems() as $item) {
            $copy->addItem(SetlistItemFactory::clone($item));
        }
        return $copy;
    }
}
```

---

## Repository convention

`BaseRepository` provides `save(object $entity, bool $flush = false)` and
`delete(object $entity, bool $flush = false)`. Always use these instead of calling
`$em->persist()` + `$em->flush()` directly in handlers or controllers.

---

## Redirect targets

After a custom action, redirect using `redirectToRoute()` with EA's internal route names:

| Target | Route name pattern |
|---|---|
| Index page | `admin_{entity}_index` |
| Edit page | `admin_{entity}_edit` + `?entityId=X` |
| Detail page | `admin_{entity}_detail` + `?entityId=X` |

Entity name is the lowercase FQCN last segment (e.g. `setlist`, `sheet`).

---

## Checklist for a new action

- [ ] `src/Admin/Action/XxxAction.php` — EA config, `linkToRoute()`
- [ ] `src/Admin/Action/Xxx.php` — DTO
- [ ] `src/Admin/Action/XxxHandler.php` — business logic, `__invoke()`
- [ ] `src/Controller/Action/XxxController.php` — `#[AdminRoute]`, calls handler, redirects
- [ ] `src/Entity/Factory/XxxFactory.php` — if entity construction is non-trivial
- [ ] Register action in the relevant CRUD controller's `configureActions()`
