---
layout: default
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
      <div class="px-4 py-3 w-full font-bold text-lg text-center">AdminRoute</div>
      <div class="text-xs mt-2 opacity-60 font-mono">#[AdminRoute]</div>
    </div>
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

```php {2|1,3-6,9-13|7}
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
