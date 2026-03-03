# Epic 8: Custom Actions (LIVE CODING)

**Branch**: `epic/08-actions`
**Status**: ⏳ Pending
**Dependencies**: Epic 3 (Basic Admin)

**Git Tag After Completion**: `step-3-custom-actions` 🔴 **LIVE CODING SAFETY NET**

---

## Goal

Implement custom EasyAdmin actions working with the actual entities:

- `Sheet` — `id`, `title`, `tags`, `refs`, `files`, `notes`, `credit` (OneToMany → CreditedPerson)
- `Setlist` — `id`, `title`, `date`, `notes`, `item` (OneToMany → SetListItem)
- `SetListItem` — `setlist`, `sheet`, `position`, `name`, `notes`

No status field, no organisation/multi-tenancy, no VichUploader.

The epic covers three actions:
- **"Duplicate Setlist"** — pre-built, single-entity action
- **"Generate Setlist PDF"** — pre-built, using [GotenbergBundle](https://github.com/sensiolabs/GotenbergBundle)
- **"Add to Setlist"** — live-coded, batch action

---

## Stories

### Story 8.1: Implement "Duplicate Setlist" Action (Pre-built) ⏳

**Description**: Single-entity action on a Setlist that clones it with all its items.
Demonstrates `linkToCrudAction()`, redirect to edit, and cloning a OneToMany relationship.

**In `SetlistCrudController`**:

```php
// configureActions()
$duplicate = Action::new('duplicate', 'Duplicate')
    ->linkToCrudAction('duplicate')
    ->setIcon('fa fa-copy')
    ->addCssClass('btn btn-secondary')
    ->displayAsLink();

return $actions
    ->add(Crud::PAGE_INDEX, $duplicate)
    ->add(Crud::PAGE_DETAIL, $duplicate);

// action method
public function duplicate(AdminContext $context, EntityManagerInterface $em): Response
{
    /** @var Setlist $original */
    $original = $context->getEntity()->getInstance();

    $copy = new Setlist();
    $copy->setTitle($original->getTitle() . ' (copie)');
    $copy->setDate(null);
    $copy->setNotes($original->getNotes());
    $em->persist($copy);

    foreach ($original->getItem() as $item) {
        $newItem = new SetListItem();
        $newItem->setSetlist($copy);
        $newItem->setSheet($item->getSheet());
        $newItem->setPosition($item->getPosition());
        $newItem->setName($item->getName() ?? '');
        $newItem->setNotes($item->getNotes() ?? '');
        $em->persist($newItem);
    }

    $em->flush();

    $this->addFlash('success', sprintf('Setlist "%s" dupliquée.', $original->getTitle()));

    return $this->redirect(
        $this->container->get(AdminUrlGenerator::class)
            ->setController(SetlistCrudController::class)
            ->setAction(Action::EDIT)
            ->setEntityId($copy->getId())
            ->generateUrl()
    );
}
```

**Acceptance Criteria**:
- Action visible on index and detail pages
- Cloned setlist has all items with correct positions
- Date cleared, title gets " (copie)" suffix
- Redirects to edit page of the new setlist

**Deliverables**:
- Updated `SetlistCrudController`

---

### Story 8.2: Implement "Generate Setlist PDF" Action (Pre-built) ⏳

**Description**: Single-entity action on a Setlist that generates a PDF of its programme
using [GotenbergBundle](https://github.com/sensiolabs/GotenbergBundle) — a Symfony-native
integration for the Gotenberg headless-Chrome PDF API.

#### Setup

**Install**:
```bash
composer require sensiolabs/gotenberg-bundle
```

Gotenberg itself runs as a sidecar service. Add to `compose.yaml`:
```yaml
gotenberg:
    image: gotenberg/gotenberg:8
    ports:
        - "3000:3000"
```

**`config/packages/sensiolabs_gotenberg.yaml`**:
```yaml
sensiolabs_gotenberg:
    http_client: 'gotenberg.client'
    default_options:
        pdf:
            html:
                paper_standard_size: 'A4'
                margin_top: 1.5
                margin_bottom: 1.5
                margin_left: 1.5
                margin_right: 1.5
```

**`config/packages/framework.yaml`** (add scoped client):
```yaml
framework:
    http_client:
        scoped_clients:
            gotenberg.client:
                base_uri: '%env(GOTENBERG_DSN)%'
```

Add to `.env`:
```
GOTENBERG_DSN=http://localhost:3000
```

#### Action

**In `SetlistCrudController`** — inject `GotenbergPdfInterface` in the constructor, then:

```php
use Sensiolabs\GotenbergBundle\GotenbergPdfInterface;

// configureActions()
$generatePdf = Action::new('generatePdf', 'Export PDF')
    ->linkToCrudAction('generatePdf')
    ->setIcon('fa fa-file-pdf')
    ->addCssClass('btn btn-secondary')
    ->displayAsLink();

return $actions
    ->add(Crud::PAGE_INDEX, $generatePdf)
    ->add(Crud::PAGE_DETAIL, $generatePdf);

// action method
public function generatePdf(AdminContext $context): Response
{
    /** @var Setlist $setlist */
    $setlist = $context->getEntity()->getInstance();

    return $this->gotenberg->html()
        ->content('admin/pdf/setlist.html.twig', ['setlist' => $setlist])
        ->fileName(sprintf('setlist-%s', $setlist->getId()))
        ->generate()
        ->stream();
}
```

#### PDF Template

**`templates/admin/pdf/setlist.html.twig`** — must be a full HTML document:
```twig
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ setlist.title }}</title>
    <style>
        body { font-family: sans-serif; font-size: 14px; }
        h1 { font-size: 22px; margin-bottom: 4px; }
        .meta { color: #666; margin-bottom: 24px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 6px 8px; border-bottom: 1px solid #ddd; vertical-align: top; }
        .pos { width: 32px; color: #999; }
    </style>
</head>
<body>
    <h1>{{ setlist.title }}</h1>
    <p class="meta">
        {% if setlist.date %}{{ setlist.date|date('d/m/Y') }}{% endif %}
    </p>
    <table>
        {% for item in setlist.item %}
        <tr>
            <td class="pos">{{ loop.index }}</td>
            <td>{{ item.sheet.title }}</td>
            <td>{{ item.name }}</td>
        </tr>
        {% endfor %}
    </table>
</body>
</html>
```

**Acceptance Criteria**:
- Gotenberg service running via Docker
- Action visible on index and detail pages
- Clicking it downloads a PDF named `setlist-{id}.pdf`
- PDF lists all setlist items in order
- No crash when setlist has no items

**Deliverables**:
- `compose.yaml` updated with Gotenberg service
- `config/packages/sensiolabs_gotenberg.yaml`
- `templates/admin/pdf/setlist.html.twig`
- Updated `SetlistCrudController`

---

### Story 8.3: Prepare Template for "Add to Setlist" Batch Action (Live Coding) ⏳

**Description**: Skeleton file and intermediate Twig template for the live-coded batch action.

The flow is two-step because `BatchActionDto` (carrying the selected IDs) is only available
on the first request. The IDs must be forwarded as a hidden field on the intermediate form.

**Template** (`src/Action/AddToSetlistAction.TEMPLATE.php`):
```php
<?php

// TEMPLATE for Live Coding — implement in SheetCrudController

/**
 * Steps:
 * 1. In configureActions(): declare the batch action with Action::new()->linkToCrudAction()
 *    and register it with ->addBatchAction()
 *
 * 2. Action method signature:
 *    public function addToSetlist(BatchActionDto $batchActionDto, Request $request, ...): Response
 *
 * 3. If $request->isMethod('POST'): process — load Setlist, create SetListItems, flush, redirect
 *    Otherwise: render the selection page (setlist dropdown + hidden entity IDs)
 *
 * 4. Position: MAX(position) of existing items + 1 for each new item
 *
 * 5. Template: templates/admin/action/add_to_setlist.html.twig
 */
```

**Intermediate template** (`templates/admin/action/add_to_setlist.html.twig`):
```twig
{% extends '@EasyAdmin/layout.html.twig' %}
{% block content %}
<div class="container py-4" style="max-width: 480px">
    <h2>Ajouter à une setlist</h2>
    <p>{{ sheet_count }} fiche(s) sélectionnée(s).</p>
    <form method="POST">
        <div class="mb-3">
            <label for="setlist_id" class="form-label">Setlist</label>
            <select name="setlist_id" id="setlist_id" class="form-select" required>
                <option value="">-- Choisir --</option>
                {% for setlist in setlists %}
                    <option value="{{ setlist.id }}">{{ setlist.title }}</option>
                {% endfor %}
            </select>
        </div>
        <input type="hidden" name="entity_ids" value="{{ entity_ids|join(',') }}">
        <div class="d-flex gap-2">
            <a href="{{ referrer_url }}" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">Ajouter</button>
        </div>
    </form>
</div>
{% endblock %}
```

**Acceptance Criteria**:
- Files in place with clear comments
- App still runs normally (template file not autoloaded)

**Deliverables**:
- `src/Action/AddToSetlistAction.TEMPLATE.php`
- `templates/admin/action/add_to_setlist.html.twig`

---

### Story 8.4: Implement "Add to Setlist" Batch Action — Complete Version (Safety Net) ⏳

**Description**: Full working implementation in `SheetCrudController`.

```php
// configureActions()
$addToSetlist = Action::new('addToSetlist', 'Ajouter à une setlist')
    ->linkToCrudAction('addToSetlist')
    ->addCssClass('btn btn-primary');

return $actions
    ->addBatchAction($addToSetlist)
    // ... existing permissions ...
    ;

// action method
public function addToSetlist(
    BatchActionDto $batchActionDto,
    Request $request,
    EntityManagerInterface $em,
    SetlistRepository $setlistRepository,
): Response {
    if ($request->isMethod('POST')) {
        $setlist = $em->find(Setlist::class, $request->request->get('setlist_id'));
        if (!$setlist) {
            $this->addFlash('danger', 'Setlist introuvable.');
            return $this->redirect($batchActionDto->getReferrerUrl());
        }

        $maxPosition = (int) $em->createQuery(
            'SELECT COALESCE(MAX(i.position), 0) FROM App\Entity\SetListItem i WHERE i.setlist = :s'
        )->setParameter('s', $setlist)->getSingleScalarResult();

        $ids = array_filter(explode(',', $request->request->get('entity_ids', '')));
        $added = 0;
        foreach ($ids as $id) {
            $sheet = $em->find(Sheet::class, (int) $id);
            if (!$sheet) continue;
            $item = new SetListItem();
            $item->setSetlist($setlist);
            $item->setSheet($sheet);
            $item->setPosition(++$maxPosition);
            $item->setName('');
            $item->setNotes('');
            $em->persist($item);
            $added++;
        }
        $em->flush();

        $this->addFlash('success', sprintf('%d fiche(s) ajoutée(s) à "%s".', $added, $setlist->getTitle()));
        return $this->redirect($batchActionDto->getReferrerUrl());
    }

    return $this->render('admin/action/add_to_setlist.html.twig', [
        'setlists'     => $setlistRepository->findBy([], ['title' => 'ASC']),
        'sheet_count'  => count($batchActionDto->getEntityIds()),
        'entity_ids'   => $batchActionDto->getEntityIds(),
        'referrer_url' => $batchActionDto->getReferrerUrl(),
    ]);
}
```

**Acceptance Criteria**:
- Batch action button appears on Sheet index
- Intermediate page shows the setlist dropdown
- Items created with sequential positions after existing ones
- Flash message confirms how many sheets were added
- All existing tests still pass

**Deliverables**:
- Updated `SheetCrudController`

---

### Story 8.5: Document Custom Action Pattern ⏳

**Deliverables**:
- `docs/patterns/actions.md`

---

## Epic Acceptance Criteria

- [ ] "Duplicate Setlist" action working on index and detail
- [ ] "Generate Setlist PDF" action working end-to-end (requires Gotenberg Docker service)
- [ ] "Add to Setlist" template and Twig skeleton in place
- [ ] "Add to Setlist" complete version working end-to-end
- [ ] Action pattern documented
- [ ] Live coding rehearsed 5+ times, under 4 minutes
- [ ] Safety net branch tagged as `step-3-custom-actions`
- [ ] All existing tests still pass

---

## Live Coding Rehearsal Checklist

```
Rehearsal 1: [ ] Time: ____ mins
Rehearsal 2: [ ] Time: ____ mins
Rehearsal 3: [ ] Time: ____ mins
Rehearsal 4: [ ] Time: ____ mins
Rehearsal 5: [ ] Time: ____ mins
```

---

## Next Epic

**Epic 9**: Custom Fields & Form Extensions
