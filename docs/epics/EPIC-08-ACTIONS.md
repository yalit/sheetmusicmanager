# Epic 8: Custom Actions (LIVE CODING)

**Branch**: `epic/08-actions`
**Status**: ⏳ Pending
**Estimated Effort**: 3-4 hours (build) + 2-3 hours (rehearsal)
**Dependencies**: Epic 3 (Basic Admin), Epic 7 (Filters)

**Git Tag After Completion**: `step-3-custom-actions` 🔴 **LIVE CODING SAFETY NET**

---

## Goal

Implement custom actions for EasyAdmin, including both pre-built actions for demonstration and an action that will be live-coded during the talk.

---

## Stories

### Story 8.1: Prepare "Add to Setlist" Action for Live Coding

**Description**: Create template and cheat sheet for the batch action that will be live-coded.

**Tasks**:
- [ ] Create action template with comments
- [ ] Prepare step-by-step implementation guide
- [ ] Create modal form for setlist selection
- [ ] Document talking points
- [ ] Rehearse implementation 5+ times

**Technical Details**:

**Action Template** (`src/Action/AddToSetlistAction.TEMPLATE.php`):
```php
<?php

namespace App\Controller\Admin;

// TODO: Add use statements

/**
 * TEMPLATE for Live Coding
 *
 * This batch action allows selecting multiple sheets and adding them to a setlist.
 *
 * Steps:
 * 1. Create batch action in CRUD controller
 * 2. Implement action method
 * 3. Show modal with setlist selection
 * 4. Process selected sheets
 * 5. Create SetlistItems
 * 6. Return success message
 */

// TODO: Implement in SheetCrudController
```

**Live Coding Cheat Sheet** (`docs/LIVE_CODING_ACTION.md`):
```markdown
# Live Coding: Custom Batch Action

## Time Target: 3-4 minutes

## Steps:

### 1. Create batch action in configureActions() (1 minute)
```php
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;

public function configureActions(Actions $actions): Actions
{
    $addToSetlist = Action::new('addToSetlist', 'Add to Setlist')
        ->linkToCrudAction('addToSetlist')
        ->addCssClass('btn btn-primary')
        ->setIcon('fa fa-plus');

    return $actions
        ->addBatchAction($addToSetlist);
}
```

### 2. Create action method (2 minutes)
```php
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

public function addToSetlist(BatchActionDto $batchActionDto): Response
{
    $entityManager = $this->container->get('doctrine')->getManager();

    // Get selected sheet IDs
    $entityIds = $batchActionDto->getEntityIds();

    // For demo: show confirmation and redirect
    // In real implementation: show modal, get setlist choice, create items

    foreach ($entityIds as $id) {
        $sheet = $entityManager->find(Sheet::class, $id);
        // Create SetlistItem logic here
    }

    $this->addFlash('success', sprintf('Added %d sheets to setlist', count($entityIds)));

    return $this->redirect($batchActionDto->getReferrerUrl());
}
```

### 3. Test (30 seconds)
- Select multiple sheets
- Click "Add to Setlist" button
- See confirmation message

## Talking Points:
- "Batch actions work on multiple selected items"
- "Perfect for bulk operations"
- "Can show modals, process data, or export"
- "Action methods have full Symfony power"

## Backup Plan:
```bash
git stash
git checkout step-3-custom-actions
php bin/console cache:clear
```
```

**Acceptance Criteria**:
- Template prepared with clear steps
- Cheat sheet ready
- Talking points documented
- Rehearsed multiple times
- Under 4 minutes consistently

**Deliverables**:
- Action template
- Live coding cheat sheet
- Rehearsal notes

---

### Story 8.2: Implement "Add to Setlist" Batch Action (Complete Version)

**Description**: Create complete working version for safety net branch.

**Tasks**:
- [ ] Implement full action with modal
- [ ] Create setlist selection form
- [ ] Handle form submission
- [ ] Create SetlistItems with proper positions
- [ ] Add success/error messages
- [ ] Test thoroughly

**Technical Details**:

**Complete Action** (in `src/Controller/Admin/SheetCrudController.php`):
```php
use App\Entity\Setlist;
use App\Entity\SetlistItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

public function configureActions(Actions $actions): Actions
{
    $addToSetlist = Action::new('addToSetlist', 'Add to Setlist')
        ->linkToCrudAction('addToSetlist')
        ->addCssClass('btn btn-primary')
        ->setIcon('fa fa-plus')
        ->setCssClass('btn btn-primary');

    return $actions
        ->addBatchAction($addToSetlist)
        // ... other actions
        ;
}

public function addToSetlist(
    BatchActionDto $batchActionDto,
    Request $request,
    EntityManagerInterface $entityManager
): Response {
    // Get selected sheet IDs
    $entityIds = $batchActionDto->getEntityIds();

    // Get all available setlists for current user
    $setlists = $entityManager->getRepository(Setlist::class)->findBy(
        ['organization' => $this->getUser()->getOrganization()],
        ['name' => 'ASC']
    );

    // Handle form submission
    if ($request->isMethod('POST')) {
        $setlistId = $request->request->get('setlist_id');
        $setlist = $entityManager->find(Setlist::class, $setlistId);

        if (!$setlist) {
            $this->addFlash('danger', 'Setlist not found');
            return $this->redirect($batchActionDto->getReferrerUrl());
        }

        // Check if user can modify this setlist
        if ($setlist->getOrganization() !== $this->getUser()->getOrganization()) {
            $this->addFlash('danger', 'You cannot modify this setlist');
            return $this->redirect($batchActionDto->getReferrerUrl());
        }

        // Get current maximum position
        $maxPosition = $entityManager->createQuery(
            'SELECT MAX(si.position) FROM App\Entity\SetlistItem si WHERE si.setlist = :setlist'
        )->setParameter('setlist', $setlist)
         ->getSingleScalarResult() ?? 0;

        $position = $maxPosition + 1;
        $addedCount = 0;

        foreach ($entityIds as $id) {
            $sheet = $entityManager->find(Sheet::class, $id);

            if ($sheet && $sheet->getOrganization() === $this->getUser()->getOrganization()) {
                $item = new SetlistItem();
                $item->setSetlist($setlist);
                $item->setSheet($sheet);
                $item->setPosition($position++);

                $entityManager->persist($item);
                $addedCount++;
            }
        }

        $entityManager->flush();

        $this->addFlash('success', sprintf(
            'Added %d sheet(s) to setlist "%s"',
            $addedCount,
            $setlist->getName()
        ));

        return $this->redirect($batchActionDto->getReferrerUrl());
    }

    // Show modal with setlist selection
    return $this->render('admin/action/add_to_setlist_modal.html.twig', [
        'setlists' => $setlists,
        'sheet_count' => count($entityIds),
        'entity_ids' => $entityIds,
        'referrer_url' => $batchActionDto->getReferrerUrl(),
    ]);
}
```

**Modal Template** (`templates/admin/action/add_to_setlist_modal.html.twig`):
```twig
{% extends '@EasyAdmin/layout.html.twig' %}

{% block body %}
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Add to Setlist</h4>
                </div>
                <div class="card-body">
                    <p>Select a setlist to add <strong>{{ sheet_count }}</strong> sheet(s) to:</p>

                    <form method="POST">
                        <div class="mb-3">
                            <label for="setlist_id" class="form-label">Setlist</label>
                            <select name="setlist_id" id="setlist_id" class="form-select" required>
                                <option value="">-- Select Setlist --</option>
                                {% for setlist in setlists %}
                                    <option value="{{ setlist.id }}">
                                        {{ setlist.name }}
                                        ({{ setlist.status|title }})
                                        {% if setlist.eventDate %}
                                            - {{ setlist.eventDate|date('Y-m-d') }}
                                        {% endif %}
                                    </option>
                                {% endfor %}
                            </select>
                        </div>

                        <input type="hidden" name="entity_ids" value="{{ entity_ids|join(',') }}">

                        <div class="d-flex justify-content-between">
                            <a href="{{ referrer_url }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Add to Setlist</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
{% endblock %}
```

**Acceptance Criteria**:
- Complete action implementation
- Modal displays with setlist choices
- Sheets added to selected setlist
- Positions calculated correctly
- Success message displayed
- Works with multi-tenancy

**Deliverables**:
- Complete batch action
- Modal template
- Tested and working

---

### Story 8.3: Implement "Archive Sheets" Batch Action (Pre-built)

**Description**: Create batch action to archive multiple sheets at once.

**Tasks**:
- [ ] Create archive batch action
- [ ] Add confirmation
- [ ] Update sheet status to 'archived'
- [ ] Add success message
- [ ] Test with multiple sheets

**Technical Details**:

**Archive Action** (in `SheetCrudController.php`):
```php
public function configureActions(Actions $actions): Actions
{
    $archive = Action::new('archive', 'Archive')
        ->linkToCrudAction('batchArchive')
        ->addCssClass('btn btn-warning')
        ->setIcon('fa fa-archive');

    return $actions
        ->addBatchAction($archive)
        ->addBatchAction($addToSetlist);
}

public function batchArchive(
    BatchActionDto $batchActionDto,
    EntityManagerInterface $entityManager
): Response {
    $entityIds = $batchActionDto->getEntityIds();
    $count = 0;

    foreach ($entityIds as $id) {
        $sheet = $entityManager->find(Sheet::class, $id);

        if ($sheet && $sheet->getOrganization() === $this->getUser()->getOrganization()) {
            $sheet->setStatus('archived');
            $count++;
        }
    }

    $entityManager->flush();

    $this->addFlash('success', sprintf('Archived %d sheet(s)', $count));

    return $this->redirect($batchActionDto->getReferrerUrl());
}
```

**Acceptance Criteria**:
- Batch archive action works
- Multiple sheets archived at once
- Only affects user's organization sheets
- Success message shows count

**Deliverables**:
- Archive batch action

---

### Story 8.4: Implement "Generate Setlist PDF" Action (Pre-built)

**Description**: Create single entity action to generate PDF of a setlist.

**Tasks**:
- [ ] Create PDF generation action
- [ ] Use TCPDF or Dompdf library
- [ ] Include all sheets in setlist
- [ ] Format nicely with headers
- [ ] Download as PDF file
- [ ] Only show for finalized setlists

**Technical Details**:

**Install PDF Library**:
```bash
composer require tecnickcom/tcpdf
# OR
composer require dompdf/dompdf
```

**PDF Action** (in `SetlistCrudController.php`):
```php
use TCPDF;

public function configureActions(Actions $actions): Actions
{
    $generatePdf = Action::new('generatePdf', 'Generate PDF')
        ->linkToCrudAction('generatePdf')
        ->setIcon('fa fa-file-pdf')
        ->displayIf(static function (Setlist $setlist) {
            return $setlist->getStatus() === 'finalized';
        });

    return $actions
        ->add(Crud::PAGE_DETAIL, $generatePdf)
        ->add(Crud::PAGE_INDEX, $generatePdf);
}

public function generatePdf(AdminContext $context): Response
{
    /** @var Setlist $setlist */
    $setlist = $context->getEntity()->getInstance();

    // Check organization access
    if ($setlist->getOrganization() !== $this->getUser()->getOrganization()) {
        throw $this->createAccessDeniedException();
    }

    // Create PDF
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');

    $pdf->SetCreator('Sheet Music Manager');
    $pdf->SetAuthor($this->getUser()->getName());
    $pdf->SetTitle($setlist->getName());

    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    $pdf->AddPage();

    // Title
    $pdf->SetFont('helvetica', 'B', 20);
    $pdf->Cell(0, 10, $setlist->getName(), 0, 1, 'C');

    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 10, $setlist->getOccasion() ?? '', 0, 1, 'C');

    if ($setlist->getEventDate()) {
        $pdf->Cell(0, 10, $setlist->getEventDate()->format('F j, Y'), 0, 1, 'C');
    }

    $pdf->Ln(10);

    // Setlist items
    $pdf->SetFont('helvetica', '', 11);

    foreach ($setlist->getItems() as $item) {
        $sheet = $item->getSheet();

        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(10, 8, $item->getPosition() . '.', 0, 0);

        if ($item->getName()) {
            $pdf->Cell(50, 8, $item->getName(), 0, 0);
        }

        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(0, 8, $sheet->getTitle(), 0, 1);

        if ($sheet->getComposer()) {
            $pdf->Cell(10, 6, '', 0, 0);
            $pdf->SetFont('helvetica', 'I', 9);
            $pdf->Cell(0, 6, 'by ' . $sheet->getComposer()->getName(), 0, 1);
        }

        if ($item->getNotes()) {
            $pdf->Cell(10, 6, '', 0, 0);
            $pdf->SetFont('helvetica', '', 9);
            $pdf->SetTextColor(100, 100, 100);
            $pdf->MultiCell(0, 6, $item->getNotes(), 0, 'L');
            $pdf->SetTextColor(0, 0, 0);
        }

        $pdf->Ln(2);
    }

    // Output PDF
    return new Response(
        $pdf->Output('', 'S'),
        Response::HTTP_OK,
        [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => sprintf(
                'attachment; filename="setlist-%s.pdf"',
                $setlist->getId()
            ),
        ]
    );
}
```

**Acceptance Criteria**:
- PDF generation works
- PDF includes all setlist items
- Formatted nicely
- Downloads as PDF file
- Only available for finalized setlists

**Deliverables**:
- PDF generation action
- Working PDF download

---

### Story 8.5: Implement "Mark as Performed" Action (Pre-built)

**Description**: Create action to change setlist status from finalized to performed.

**Tasks**:
- [ ] Create status change action
- [ ] Add confirmation
- [ ] Update status to 'performed'
- [ ] Record performed date
- [ ] Show success message
- [ ] Only show for finalized setlists

**Technical Details**:

**Mark Performed Action** (in `SetlistCrudController.php`):
```php
public function configureActions(Actions $actions): Actions
{
    $markPerformed = Action::new('markPerformed', 'Mark as Performed')
        ->linkToCrudAction('markPerformed')
        ->setIcon('fa fa-check-circle')
        ->setCssClass('btn btn-success')
        ->displayIf(static function (Setlist $setlist) {
            return $setlist->getStatus() === 'finalized';
        });

    return $actions
        ->add(Crud::PAGE_DETAIL, $markPerformed)
        ->add(Crud::PAGE_INDEX, $markPerformed)
        ->add(Crud::PAGE_DETAIL, $generatePdf);
}

public function markPerformed(
    AdminContext $context,
    EntityManagerInterface $entityManager
): Response {
    /** @var Setlist $setlist */
    $setlist = $context->getEntity()->getInstance();

    // Check access
    $this->denyAccessUnlessGranted('MARK_PERFORMED', $setlist);

    $setlist->setStatus('performed');

    $entityManager->flush();

    $this->addFlash('success', sprintf(
        'Setlist "%s" marked as performed',
        $setlist->getName()
    ));

    return $this->redirect($context->getReferrer());
}
```

**Acceptance Criteria**:
- Action only visible for finalized setlists
- Status changes to performed
- Success message displayed
- Voter check passes

**Deliverables**:
- Mark performed action

---

### Story 8.6: Implement "Duplicate Setlist" Action (Pre-built)

**Description**: Create action to clone a setlist with all its items.

**Tasks**:
- [ ] Create duplicate action
- [ ] Clone setlist entity
- [ ] Clone all setlist items
- [ ] Adjust name (add "Copy")
- [ ] Set status to 'draft'
- [ ] Redirect to edit page

**Technical Details**:

**Duplicate Action** (in `SetlistCrudController.php`):
```php
public function configureActions(Actions $actions): Actions
{
    $duplicate = Action::new('duplicate', 'Duplicate')
        ->linkToCrudAction('duplicate')
        ->setIcon('fa fa-copy')
        ->setCssClass('btn btn-info');

    return $actions
        ->add(Crud::PAGE_DETAIL, $duplicate)
        ->add(Crud::PAGE_INDEX, $duplicate);
}

public function duplicate(
    AdminContext $context,
    EntityManagerInterface $entityManager
): Response {
    /** @var Setlist $original */
    $original = $context->getEntity()->getInstance();

    // Check access
    if ($original->getOrganization() !== $this->getUser()->getOrganization()) {
        throw $this->createAccessDeniedException();
    }

    // Clone setlist
    $copy = new Setlist();
    $copy->setName($original->getName() . ' (Copy)');
    $copy->setOccasion($original->getOccasion());
    $copy->setStatus('draft');
    $copy->setEventDate(null); // Don't copy event date
    $copy->setNotes($original->getNotes());
    $copy->setOrganization($original->getOrganization());

    $entityManager->persist($copy);

    // Clone all items
    foreach ($original->getItems() as $originalItem) {
        $copyItem = new SetlistItem();
        $copyItem->setSetlist($copy);
        $copyItem->setSheet($originalItem->getSheet());
        $copyItem->setPosition($originalItem->getPosition());
        $copyItem->setName($originalItem->getName());
        $copyItem->setNotes($originalItem->getNotes());

        $entityManager->persist($copyItem);
    }

    $entityManager->flush();

    $this->addFlash('success', sprintf(
        'Duplicated setlist "%s" as "%s"',
        $original->getName(),
        $copy->getName()
    ));

    // Redirect to edit the copy
    return $this->redirect(
        $this->container->get(AdminUrlGenerator::class)
            ->setController(self::class)
            ->setAction(Action::EDIT)
            ->setEntityId($copy->getId())
            ->generateUrl()
    );
}
```

**Acceptance Criteria**:
- Duplicates setlist with all items
- Name appended with "(Copy)"
- Status set to draft
- Event date cleared
- Redirects to edit page

**Deliverables**:
- Duplicate action

---

### Story 8.7: Implement "Preview Sheet" Action (Pre-built)

**Description**: Create action to preview sheet PDF in new tab.

**Tasks**:
- [ ] Create preview action
- [ ] Open PDF in new tab
- [ ] Only show if PDF exists
- [ ] Test with different browsers

**Technical Details**:

**Preview Action** (in `SheetCrudController.php`):
```php
public function configureActions(Actions $actions): Actions
{
    $preview = Action::new('preview', 'Preview PDF')
        ->linkToRoute('sheet_preview', fn (Sheet $sheet) => [
            'id' => $sheet->getId()
        ])
        ->setIcon('fa fa-eye')
        ->setCssClass('btn btn-info')
        ->displayIf(static function (Sheet $sheet) {
            return $sheet->getPdfFileName() !== null;
        })
        ->setHtmlAttributes(['target' => '_blank']);

    return $actions
        ->add(Crud::PAGE_DETAIL, $preview)
        ->add(Crud::PAGE_INDEX, $preview);
}
```

**Preview Route** (`src/Controller/SheetPreviewController.php`):
```php
<?php

namespace App\Controller;

use App\Entity\Sheet;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Vich\UploaderBundle\Storage\StorageInterface;

class SheetPreviewController extends AbstractController
{
    public function __construct(private StorageInterface $storage)
    {
    }

    #[Route('/sheet/{id}/preview', name: 'sheet_preview')]
    public function preview(Sheet $sheet): Response
    {
        // Check access
        if ($sheet->getOrganization() !== $this->getUser()->getOrganization()) {
            throw $this->createAccessDeniedException();
        }

        if (!$sheet->getPdfFileName()) {
            throw $this->createNotFoundException('No PDF available');
        }

        $path = $this->storage->resolvePath($sheet, 'pdfFile');

        return new Response(
            file_get_contents($path),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $sheet->getTitle() . '.pdf"',
            ]
        );
    }
}
```

**Acceptance Criteria**:
- Preview opens in new tab
- Only shows if PDF exists
- Organization access checked
- Works in all browsers

**Deliverables**:
- Preview action
- Preview route/controller

---

## Epic Acceptance Criteria

- [ ] "Add to Setlist" action template prepared
- [ ] "Add to Setlist" action complete version ready
- [ ] "Archive Sheets" batch action working
- [ ] "Generate PDF" action working
- [ ] "Mark as Performed" action working
- [ ] "Duplicate Setlist" action working
- [ ] "Preview Sheet" action working
- [ ] All actions respect multi-tenancy
- [ ] Live coding rehearsed 5+ times
- [ ] Timing under 4 minutes for live coding
- [ ] Safety net branch tested

---

## Live Coding Rehearsal Checklist

```bash
# Rehearsal 1: [ ] Time: ____ mins
# Rehearsal 2: [ ] Time: ____ mins
# Rehearsal 3: [ ] Time: ____ mins
# Rehearsal 4: [ ] Time: ____ mins
# Rehearsal 5: [ ] Time: ____ mins

# Key metrics:
- [ ] Can implement action from memory
- [ ] Can explain while coding
- [ ] Can handle typos smoothly
- [ ] Can test successfully
- [ ] Stay under 4 minutes
```

---

## Testing Checklist

```bash
# Batch Actions
- [ ] Add to Setlist works with multiple sheets
- [ ] Archive works with multiple sheets
- [ ] Actions respect organization boundaries

# Single Entity Actions
- [ ] Generate PDF creates valid PDF
- [ ] Mark as Performed updates status
- [ ] Duplicate creates exact copy
- [ ] Preview opens PDF in new tab

# Conditional Display
- [ ] Actions show only when conditions met
- [ ] Generate PDF only for finalized setlists
- [ ] Mark Performed only for finalized setlists
- [ ] Preview only for sheets with PDF
```

---

## Deliverables

- [ ] `docs/LIVE_CODING_ACTION.md`
- [ ] Complete Add to Setlist action with modal
- [ ] Archive batch action
- [ ] Generate PDF action with TCPDF
- [ ] Mark as Performed action
- [ ] Duplicate Setlist action
- [ ] Preview Sheet action
- [ ] `templates/admin/action/add_to_setlist_modal.html.twig`
- [ ] `src/Controller/SheetPreviewController.php`
- [ ] Updated CRUD controllers
- [ ] Git tag: `step-3-custom-actions`

---

## Git Tagging

```bash
git add .
git commit -m "Epic 8: Custom actions complete (live coding safety net)"
git tag -a step-3-custom-actions -m "After live coding #2: Custom actions implemented"
git push origin epic/08-actions --tags
```

---

## Notes

- Custom actions are powerful demonstrations of EasyAdmin extensibility
- Batch actions are particularly impressive to audiences
- PDF generation shows real-world utility
- Modal interaction demonstrates UI flexibility
- Practice live coding until completely comfortable

---

## Next Epic

**Epic 9**: Custom Fields & Form Extensions
