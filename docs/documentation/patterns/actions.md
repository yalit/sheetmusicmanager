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

---

## Variant: action with form + Symfony Messenger

Use this when the action needs user input before executing (e.g. pick a target entity from a
dropdown, confirm options). The DTO becomes a Symfony Message; the handler becomes a
`MessageHandler`; a standard form type binds the two together.

### Directory layout

```
src/Message/
    AddSheetsToSetlist.php            ← Message DTO (#[AsMessage])
    Factory/
        AddSheetsToSetListFactory.php ← hydrates Message from raw request IDs

src/MessageHandler/
    AddSheetsToSetlistHandler.php     ← #[AsMessageHandler], __invoke()

src/Form/
    AddSheetsToSetlistType.php        ← AbstractType, data_class = Message
    DataTransformer/
        SheetToStringDataTransformer.php  ← HiddenType ↔ entity[]
```

---

### Message DTO — `src/Message/Xxx.php`

Plain class with public properties and Validator constraints. `#[AsMessage]` registers it
with Messenger. No methods, no logic.

```php
#[AsMessage]
class AddSheetsToSetlist
{
    #[NotNull]
    public Setlist $setlist;

    /** @var Sheet[] */
    public array $sheets = [];
}
```

---

### Message factory — `src/Message/Factory/XxxFactory.php`

Hydrates the Message from raw IDs coming from the HTTP request (batch action POST sends
`batchActionEntityIds[]`). Resolves entities via repository; skips missing IDs silently.

```php
readonly class AddSheetsToSetListFactory
{
    public function __construct(private SheetRepository $sheetRepository) {}

    /** @param string[] $sheetIDs */
    public function create(array $sheetIDs): AddSheetsToSetlist
    {
        $message = new AddSheetsToSetlist();
        foreach ($sheetIDs as $id) {
            $sheet = $this->sheetRepository->find($id);
            if ($sheet) {
                $message->sheets[] = $sheet;
            }
        }
        return $message;
    }
}
```

---

### Form type — `src/Form/XxxType.php`

Standard `AbstractType` with `data_class` set to the Message. Hidden fields that carry
entity collections need a `DataTransformer` to bridge between the serialised string (what
the hidden input stores) and the hydrated entity array (what the Message expects).

```php
class AddSheetsToSetlistType extends AbstractType
{
    public function __construct(private readonly SheetToStringDataTransformer $transformer) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('setlist', EntityType::class, [
                'class'        => Setlist::class,
                'choice_label' => 'title',
                'placeholder'  => '-- Choisir --',
                'required'     => true,
            ])
            ->add('sheets', HiddenType::class);

        $builder->get('sheets')->addModelTransformer($this->transformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => AddSheetsToSetlist::class]);
    }
}
```

---

### DataTransformer — `src/Form/DataTransformer/XxxDataTransformer.php`

Converts between the model value (entity array) and the view value (JSON string in a
hidden input). Implement `DataTransformerInterface<ModelType, ViewType>`.

```php
/** @implements DataTransformerInterface<Sheet[], string> */
readonly class SheetToStringDataTransformer implements DataTransformerInterface
{
    public function __construct(private SheetRepository $sheetRepository) {}

    /** @param Sheet[] $value */
    public function transform(mixed $value): string
    {
        return json_encode(array_map(fn(Sheet $s) => $s->getId(), $value)) ?: '{}';
    }

    /** @return Sheet[] */
    public function reverseTransform(mixed $value): array
    {
        $ids = json_decode($value, true);
        return array_filter(array_map(fn($id) => $this->sheetRepository->find($id), $ids));
    }
}
```

---

### Message handler — `src/MessageHandler/XxxHandler.php`

`#[AsMessageHandler]` wires it automatically. `__invoke()` receives the hydrated Message;
no HTTP or EA dependencies.

```php
#[AsMessageHandler]
final readonly class AddSheetsToSetlistHandler
{
    public function __construct(private SetlistRepository $setlistRepository) {}

    public function __invoke(AddSheetsToSetlist $message): void
    {
        $setlist = $message->setlist;
        $maxPosition = count($setlist->getItems());
        foreach ($message->sheets as $sheet) {
            $item = new SetListItem();
            $item->setSheet($sheet)->setPosition(++$maxPosition);
            $setlist->addItem($item);
        }
        $this->setlistRepository->save($setlist, flush: true);
    }
}
```

---

### Controller — `src/Controller/Action/XxxController.php`

Handles both the initial batch POST (renders the form) and the form submission (dispatches
the message). One route, one method; no branching on `isPost()` — the form's `isSubmitted()`
+ `isValid()` is the only gate.

```php
final class AddToSetlistController extends AbstractController
{
    public function __construct(
        private readonly AddSheetsToSetListFactory $factory,
        private readonly MessageBusInterface $messageBus,
    ) {}

    #[AdminRoute('/sheet/add-to-setlist', name: 'add_to_setlist', options: ['methods' => ['POST']])]
    public function addToSetlistRequest(Request $request): Response
    {
        // First POST: batchActionEntityIds[] from EA batch action
        $sheetIds = $request->request->all('batchActionEntityIds') ?: [];

        $message = $this->factory->create($sheetIds);
        $form = $this->createForm(AddSheetsToSetlistType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->messageBus->dispatch($message);
            return $this->redirectToRoute('admin_setlist_edit', ['entityId' => $message->setlist->getId()]);
        }

        return $this->render('admin/action/add_to_setlist.html.twig', [
            'form'        => $form,
            'sheet_count' => count($sheetIds),
        ]);
    }
}
```

Key points:
- The factory hydrates the Message on the **first** POST (batch action). `batchActionEntityIds`
  is only present in that first request; on re-POST the hidden field carries the JSON.
- `handleRequest` on the first POST finds the form not yet submitted (no `_token`), so it
  renders the form template with the pre-populated `sheets` hidden field.
- On the second POST (form submit), `isSubmitted() && isValid()` passes → dispatch → redirect.

---

### Checklist for a form + Messenger action

- [ ] `src/Admin/Action/XxxAction.php` — EA config, `linkToRoute()`
- [ ] `src/Message/Xxx.php` — Message DTO, `#[AsMessage]`, public props + constraints
- [ ] `src/Message/Factory/XxxFactory.php` — hydrates Message from raw request IDs
- [ ] `src/Form/XxxType.php` — `AbstractType`, `data_class` = Message
- [ ] `src/Form/DataTransformer/XxxDataTransformer.php` — if any field needs entity↔string bridging
- [ ] `src/MessageHandler/XxxHandler.php` — `#[AsMessageHandler]`, business logic
- [ ] `src/Controller/Action/XxxController.php` — factory → form → dispatch → redirect
- [ ] `templates/admin/action/xxx.html.twig` — renders the intermediate form
- [ ] Register action in the relevant CRUD controller's `configureActions()`
