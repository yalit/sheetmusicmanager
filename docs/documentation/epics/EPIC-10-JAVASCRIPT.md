# Epic 10: JavaScript Integration

**Branch**: `epic/10-dnd-reorder`
**Status**: Done
**Dependencies**: Epic 3 (Basic Admin), Epic 1 (Stimulus setup)

---

## Goal

Implement JavaScript enhancements using both embedded JavaScript and Stimulus controllers to demonstrate different integration approaches.

---

## Stories

### Story 10.1: Implement Duration Auto-Format (Embedded JS)

**Description**: Add inline JavaScript to automatically format duration field input.

**Tasks**:
- [ ] Create custom field template with embedded JS
- [ ] Convert decimal to minutes:seconds format
- [ ] Convert integer to minutes:seconds format
- [ ] Trigger on blur event
- [ ] Test with various inputs

**Technical Details**:

**Duration Field Template** (`templates/admin/field/duration_field.html.twig`):
```twig
{% extends '@EasyAdmin/crud/form_theme.html.twig' %}

{% block _sheet_duration_widget %}
    {{ form_widget(form) }}

    <script>
        (function() {
            const input = document.querySelector('#{{ form.vars.id }}');

            if (!input || input.dataset.durationFormatted) {
                return;
            }

            input.dataset.durationFormatted = 'true';

            input.addEventListener('blur', function() {
                let value = this.value.trim();

                if (!value) {
                    return;
                }

                // Convert decimal format (e.g., "3.5" or "3.75") to "minutes:seconds"
                if (value.match(/^\d+\.\d+$/)) {
                    const parts = value.split('.');
                    const minutes = parseInt(parts[0]);
                    const decimal = parseFloat('0.' + parts[1]);
                    const seconds = Math.round(decimal * 60);
                    this.value = `${minutes}:${seconds.toString().padStart(2, '0')}`;
                }

                // Convert integer format (e.g., "3") to "minutes:00"
                else if (value.match(/^\d+$/)) {
                    this.value = `${value}:00`;
                }

                // Already in correct format (e.g., "3:45") - do nothing
                else if (!value.match(/^\d+:\d{2}$/)) {
                    // Invalid format - show warning (optional)
                    this.classList.add('is-invalid');
                    setTimeout(() => this.classList.remove('is-invalid'), 2000);
                }
            });

            // Show format hint
            const hint = document.createElement('small');
            hint.className = 'form-text text-muted';
            hint.textContent = 'Enter as 3:45 or 3.75 (will auto-format)';
            input.parentElement.appendChild(hint);
        })();
    </script>
{% endblock %}
```

**Sheet CRUD Controller**:
```php
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

public function configureCrud(Crud $crud): Crud
{
    return $crud
        ->addFormTheme('admin/field/duration_field.html.twig')
        // ... other configuration
        ;
}

public function configureFields(string $pageName): iterable
{
    // ... other fields

    yield TextField::new('duration')
        ->setColumns(6)
        ->setHelp('Duration will auto-format (e.g., 3.5 → 3:30)')
        ->setFormTypeOption('attr', [
            'placeholder' => 'e.g., 3:45 or 3.5',
        ]);

    // ... other fields
}
```

**Acceptance Criteria**:
- Decimal input (3.5) converts to 3:30
- Integer input (3) converts to 3:00
- Correct format (3:45) unchanged
- Triggers on blur event
- Works for multiple forms on same page
- Visual feedback for invalid format

**Deliverables**:
- `templates/admin/field/duration_field.html.twig`
- Updated SheetCrudController

---

### Story 10.2: Create Sortable Stimulus Controller

**Description**: Create Stimulus controller for drag-and-drop functionality.

**Tasks**:
- [ ] Create sortable_controller.js
- [ ] Integrate SortableJS library
- [ ] Handle drag events
- [ ] Send AJAX request to update positions
- [ ] Show visual feedback
- [ ] Handle errors

**Technical Details**:

**Sortable Controller** (`assets/controllers/sortable_controller.js`):
```javascript
import { Controller } from '@hotwired/stimulus';
import Sortable from 'sortablejs';

/**
 * Stimulus controller for drag-and-drop sortable lists
 *
 * Usage:
 * <div data-controller="sortable"
 *      data-sortable-url-value="/admin/setlist/1/reorder"
 *      data-sortable-handle-value=".drag-handle">
 *   <div data-id="1" class="sortable-item">
 *     <span class="drag-handle">⋮⋮</span>
 *     Item 1
 *   </div>
 * </div>
 */
export default class extends Controller {
    static values = {
        url: String,
        handle: String,
        animation: { type: Number, default: 150 }
    };

    connect() {
        this.initSortable();
    }

    disconnect() {
        if (this.sortable) {
            this.sortable.destroy();
        }
    }

    initSortable() {
        const options = {
            animation: this.animationValue,
            handle: this.handleValue || null,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            onEnd: this.handleSort.bind(this)
        };

        this.sortable = new Sortable(this.element, options);
    }

    async handleSort(event) {
        // Get all items in new order
        const items = Array.from(this.element.querySelectorAll('[data-id]'));
        const positions = items.map((item, index) => ({
            id: parseInt(item.dataset.id),
            position: index + 1
        }));

        try {
            // Show loading state
            this.showLoading();

            // Send AJAX request to update positions
            const response = await fetch(this.urlValue, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ positions })
            });

            if (!response.ok) {
                throw new Error('Failed to update order');
            }

            const data = await response.json();

            // Show success message
            this.showSuccess(data.message || 'Order updated successfully');

            // Update position numbers in UI
            this.updatePositionNumbers(items);

        } catch (error) {
            console.error('Error updating order:', error);
            this.showError('Failed to update order. Please refresh the page.');

            // Revert to original order (if we stored it)
            // For now, suggest refresh
        } finally {
            this.hideLoading();
        }
    }

    updatePositionNumbers(items) {
        items.forEach((item, index) => {
            const positionElement = item.querySelector('.position-number');
            if (positionElement) {
                positionElement.textContent = index + 1;
            }
        });
    }

    showLoading() {
        this.element.classList.add('sortable-loading');
    }

    hideLoading() {
        this.element.classList.remove('sortable-loading');
    }

    showSuccess(message) {
        this.showToast(message, 'success');
    }

    showError(message) {
        this.showToast(message, 'danger');
    }

    showToast(message, type = 'info') {
        // Simple toast notification
        const toast = document.createElement('div');
        toast.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        toast.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(toast);

        // Auto-dismiss after 3 seconds
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }
}
```

**CSS for Sortable** (`assets/styles/admin.css`):
```css
/* Sortable drag states */
.sortable-ghost {
    opacity: 0.4;
    background: #f0f0f0;
}

.sortable-chosen {
    cursor: grabbing !important;
}

.sortable-drag {
    cursor: grabbing !important;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}

.sortable-loading {
    opacity: 0.6;
    pointer-events: none;
}

/* Drag handle */
.drag-handle {
    cursor: grab;
    color: #999;
    padding: 0 8px;
    user-select: none;
}

.drag-handle:hover {
    color: #666;
}

.drag-handle:active {
    cursor: grabbing;
}

/* Sortable item */
.sortable-item {
    display: flex;
    align-items: center;
    padding: 12px;
    margin-bottom: 8px;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    transition: all 0.2s;
}

.sortable-item:hover {
    border-color: #999;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
```

**Acceptance Criteria**:
- Stimulus controller created
- SortableJS integrated
- Drag-and-drop works smoothly
- AJAX request sent on reorder
- Visual feedback during drag
- Success/error messages shown

**Deliverables**:
- `assets/controllers/sortable_controller.js`
- `assets/styles/admin.css`

---

### Story 10.3: Implement Drag-and-Drop for Setlist Items

**Description**: Apply Sortable controller to SetlistItem management.

**Tasks**:
- [ ] Create custom template for setlist items list
- [ ] Add Stimulus controller attributes
- [ ] Add drag handles
- [ ] Style sortable items
- [ ] Test reordering

**Technical Details**:

**Setlist Items Template** (`templates/admin/setlist_items.html.twig`):
```twig
{% extends '@EasyAdmin/page/content.html.twig' %}

{% block main %}
    <div class="content-panel">
        <div class="content-panel-header d-flex justify-content-between align-items-center">
            <h2>
                <i class="fa fa-list"></i>
                Setlist: {{ setlist.name }}
            </h2>
            <a href="{{ path('admin', {'crudAction': 'edit', 'crudControllerFqcn': 'App\\Controller\\Admin\\SetlistCrudController', 'entityId': setlist.id}) }}"
               class="btn btn-primary">
                <i class="fa fa-edit"></i> Edit Setlist
            </a>
        </div>

        <div class="content-panel-body">
            {% if setlist.items|length > 0 %}
                <div data-controller="sortable"
                     data-sortable-url-value="{{ path('admin_setlist_reorder', {id: setlist.id}) }}"
                     data-sortable-handle-value=".drag-handle"
                     class="sortable-container">

                    {% for item in setlist.items %}
                        <div class="sortable-item" data-id="{{ item.id }}">
                            <span class="drag-handle">
                                <i class="fa fa-grip-vertical"></i>
                            </span>

                            <span class="position-number badge bg-primary me-3">
                                {{ item.position }}
                            </span>

                            <div class="item-content flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        {% if item.name %}
                                            <strong class="text-primary">{{ item.name }}</strong>
                                            <br>
                                        {% endif %}
                                        <span class="sheet-title">{{ item.sheet.title }}</span>

                                        {% if item.sheet.composer %}
                                            <br>
                                            <small class="text-muted">
                                                by {{ item.sheet.composer.name }}
                                            </small>
                                        {% endif %}

                                        {% if item.notes %}
                                            <br>
                                            <small class="text-info">
                                                <i class="fa fa-info-circle"></i> {{ item.notes }}
                                            </small>
                                        {% endif %}
                                    </div>

                                    <div class="item-actions">
                                        {% if item.sheet.pdfFileName %}
                                            <a href="{{ vich_uploader_asset(item.sheet, 'pdfFile') }}"
                                               target="_blank"
                                               class="btn btn-sm btn-outline-primary"
                                               title="View PDF">
                                                <i class="fa fa-file-pdf"></i>
                                            </a>
                                        {% endif %}
                                    </div>
                                </div>
                            </div>
                        </div>
                    {% endfor %}
                </div>

                <div class="alert alert-info mt-3">
                    <i class="fa fa-info-circle"></i>
                    <strong>Tip:</strong> Drag and drop items to reorder them.
                </div>
            {% else %}
                <div class="alert alert-warning">
                    This setlist has no items yet. Add sheets to this setlist to get started.
                </div>
            {% endif %}
        </div>
    </div>
{% endblock %}
```

**Acceptance Criteria**:
- Setlist items display in list
- Drag handles visible
- Can reorder by dragging
- Position numbers update
- Visual feedback during drag
- Changes persist to database

**Deliverables**:
- `templates/admin/setlist_items.html.twig`

---

### Story 10.4: Create Backend Endpoint for Reordering

**Description**: Implement server-side endpoint to handle position updates.

**Tasks**:
- [ ] Create reorder action in controller
- [ ] Receive positions JSON
- [ ] Update SetlistItem positions
- [ ] Validate organization access
- [ ] Return success response

**Technical Details**:

**Reorder Endpoint** (`src/Controller/Admin/SetlistItemReorderController.php`):

```php
<?php

namespace App\Controller\Admin;

use App\Entity\Setlist\Setlist;use App\Entity\Setlist\SetlistItem;use Doctrine\ORM\EntityManagerInterface;use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;use Symfony\Component\HttpFoundation\JsonResponse;use Symfony\Component\HttpFoundation\Request;use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/setlist')]
class SetlistItemReorderController extends AbstractController
{
    #[Route('/{id}/reorder', name: 'admin_setlist_reorder', methods: ['POST'])]
    public function reorder(
        Setlist $setlist,
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        // Security: Check organization access
        if ($setlist->getOrganization() !== $this->getUser()->getOrganization()) {
            return new JsonResponse(
                ['error' => 'Access denied'],
                JsonResponse::HTTP_FORBIDDEN
            );
        }

        // Security: Check if user can edit setlists
        if (!$this->isGranted('ROLE_CONDUCTOR')) {
            return new JsonResponse(
                ['error' => 'Insufficient permissions'],
                JsonResponse::HTTP_FORBIDDEN
            );
        }

        // Get positions from request
        $data = json_decode($request->getContent(), true);

        if (!isset($data['positions']) || !is_array($data['positions'])) {
            return new JsonResponse(
                ['error' => 'Invalid data format'],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        // Update positions
        foreach ($data['positions'] as $positionData) {
            $item = $entityManager->find(SetlistItem::class, $positionData['id']);

            if (!$item || $item->getSetlist() !== $setlist) {
                continue;
            }

            $item->setPosition($positionData['position']);
        }

        $entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Order updated successfully'
        ]);
    }
}
```

**Acceptance Criteria**:
- Endpoint receives position updates
- Updates database correctly
- Validates organization access
- Returns appropriate responses
- Handles errors gracefully

**Deliverables**:
- `src/Controller/Admin/SetlistItemReorderController.php`

---

### Story 10.5: Add Toast Notifications

**Description**: Implement toast notification system for user feedback.

**Tasks**:
- [ ] Create toast component (or use Bootstrap)
- [ ] Show success messages
- [ ] Show error messages
- [ ] Auto-dismiss after timeout
- [ ] Style notifications

**Technical Details**:

Already implemented in Sortable Controller (Story 10.2).

**Alternative: Bootstrap Toast Template** (`templates/admin/includes/toast.html.twig`):
```twig
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999">
    <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <strong class="me-auto" id="toastTitle">Notification</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body" id="toastBody">
            Message here
        </div>
    </div>
</div>

<script>
    window.showToast = function(message, type = 'info', title = 'Notification') {
        const toastElement = document.getElementById('liveToast');
        const toastTitle = document.getElementById('toastTitle');
        const toastBody = document.getElementById('toastBody');

        toastTitle.textContent = title;
        toastBody.textContent = message;

        // Set background color based on type
        const colors = {
            'success': 'bg-success text-white',
            'error': 'bg-danger text-white',
            'warning': 'bg-warning',
            'info': 'bg-info text-white'
        };

        toastElement.className = 'toast ' + (colors[type] || colors.info);

        const toast = new bootstrap.Toast(toastElement);
        toast.show();
    };
</script>
```

**Acceptance Criteria**:
- Toast notifications display
- Auto-dismiss after 3 seconds
- Different colors for success/error/info
- Can be dismissed manually
- Multiple toasts stack properly

**Deliverables**:
- Toast implementation

---

### Story 10.6: Test JavaScript Features Across Browsers

**Description**: Comprehensive browser compatibility testing.

**Tasks**:
- [ ] Test in Chrome
- [ ] Test in Firefox
- [ ] Test in Safari
- [ ] Test in Edge
- [ ] Test on mobile browsers
- [ ] Fix any compatibility issues

**Acceptance Criteria**:
- Duration formatting works in all browsers
- Drag-and-drop works in all browsers
- AJAX requests work in all browsers
- Toast notifications work in all browsers
- No console errors

**Deliverables**:
- Browser compatibility report
- Fixed issues

---

## Epic Acceptance Criteria

- [ ] Duration field auto-formats input
- [ ] Sortable Stimulus controller created
- [ ] Drag-and-drop works for setlist items
- [ ] Backend endpoint handles reordering
- [ ] Toast notifications show feedback
- [ ] All features tested in multiple browsers
- [ ] No JavaScript errors
- [ ] Professional user experience

---

## Testing Checklist

```bash
# Duration Formatting
- [ ] Enter "3.5" → becomes "3:30"
- [ ] Enter "3" → becomes "3:00"
- [ ] Enter "3:45" → stays "3:45"
- [ ] Enter invalid format → shows feedback

# Drag-and-Drop
- [ ] Can grab and drag items
- [ ] Items reorder visually
- [ ] Position numbers update
- [ ] Changes save to database
- [ ] Refresh shows new order
- [ ] Works with multiple users

# Notifications
- [ ] Success toast shows on successful reorder
- [ ] Error toast shows on failed reorder
- [ ] Toasts auto-dismiss after 3 seconds
- [ ] Can dismiss manually

# Browser Compatibility
- [ ] Works in Chrome
- [ ] Works in Firefox
- [ ] Works in Safari
- [ ] Works in Edge
- [ ] Works on mobile
```

---

## Deliverables

- [ ] `templates/admin/field/duration_field.html.twig`
- [ ] `assets/controllers/sortable_controller.js`
- [ ] `assets/styles/admin.css`
- [ ] `templates/admin/setlist_items.html.twig`
- [ ] `src/Controller/Admin/SetlistItemReorderController.php`
- [ ] Toast notification system
- [ ] Browser compatibility testing results
- [ ] Working JavaScript integrations

---

## Notes

- Embedded JavaScript good for simple, field-specific enhancements
- Stimulus controllers better for complex, reusable interactions
- Both approaches demonstrate EasyAdmin's flexibility
- Drag-and-drop is an impressive feature for live demos
- Browser testing is crucial before talk

---

## Next Epic

**Epic 11**: Advanced Features
