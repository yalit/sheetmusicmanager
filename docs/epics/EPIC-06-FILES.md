# Epic 6: File & Image Handling

**Branch**: `epic/06-files`
**Status**: ⏳ Pending
**Estimated Effort**: 2-3 hours
**Dependencies**: Epic 3 (Basic Admin)

---

## Goal

Implement comprehensive file upload, storage, and management for PDFs and images in the Sheet Music Manager application.

---

## Stories

### Story 6.1: Choose and Configure File Upload Strategy

**Description**: Select and implement file upload approach (VichUploaderBundle or custom).

**Tasks**:
- [ ] Evaluate VichUploaderBundle vs custom implementation
- [ ] Install chosen approach
- [ ] Configure upload directories
- [ ] Set up file naming strategy
- [ ] Configure MIME type validation

**Technical Details**:

**Option A: VichUploaderBundle** (Recommended):
```bash
composer require vich/uploader-bundle
```

**Configuration** (`config/packages/vich_uploader.yaml`):
```yaml
vich_uploader:
    db_driver: orm

    mappings:
        sheet_pdfs:
            uri_prefix: /uploads/sheets
            upload_destination: '%kernel.project_dir%/public/uploads/sheets'
            namer: Vich\UploaderBundle\Naming\SmartUniqueNamer
            inject_on_load: false
            delete_on_update: true
            delete_on_remove: true

        sheet_covers:
            uri_prefix: /uploads/covers
            upload_destination: '%kernel.project_dir%/public/uploads/covers'
            namer: Vich\UploaderBundle\Naming\SmartUniqueNamer
            inject_on_load: false
            delete_on_update: true
            delete_on_remove: true

        organization_logos:
            uri_prefix: /uploads/logos
            upload_destination: '%kernel.project_dir%/public/uploads/logos'
            namer: Vich\UploaderBundle\Naming\SmartUniqueNamer
            inject_on_load: false
            delete_on_update: true
            delete_on_remove: true
```

**Option B: Custom Upload Handler** (`src/Service/FileUploader.php`):
```php
<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    public function __construct(
        private string $targetDirectory,
        private SluggerInterface $slugger
    ) {}

    public function upload(UploadedFile $file): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        try {
            $file->move($this->targetDirectory, $fileName);
        } catch (FileException $e) {
            throw new \RuntimeException('Failed to upload file: ' . $e->getMessage());
        }

        return $fileName;
    }

    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }
}
```

**Acceptance Criteria**:
- File upload strategy chosen and implemented
- Configuration files created
- Upload directories configured
- File naming strategy implemented
- Ready for entity integration

**Deliverables**:
- VichUploaderBundle installed OR custom FileUploader service
- Configuration files
- Upload directories structure

---

### Story 6.2: Implement Sheet PDF Upload

**Description**: Add PDF file upload functionality to Sheet entity.

**Tasks**:
- [ ] Update Sheet entity with file upload annotations
- [ ] Create upload directory
- [ ] Configure validation (PDF only, max 10MB)
- [ ] Update CRUD controller for PDF upload
- [ ] Add download link in admin
- [ ] Test PDF upload and download

**Technical Details**:

**Sheet Entity Updates** (`src/Entity/Sheet.php`):
```php
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @Vich\Uploadable
 */
#[ORM\Entity]
class Sheet
{
    // ... other properties

    /**
     * @Vich\UploadableField(mapping="sheet_pdfs", fileNameProperty="pdfFileName", size="pdfFileSize")
     */
    #[Assert\File(
        maxSize: '10M',
        mimeTypes: ['application/pdf'],
        mimeTypesMessage: 'Please upload a valid PDF document'
    )]
    private ?File $pdfFile = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $pdfFileName = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $pdfFileSize = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    public function setPdfFile(?File $file = null): void
    {
        $this->pdfFile = $file;

        if (null !== $file) {
            // Force Doctrine to trigger update
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getPdfFile(): ?File
    {
        return $this->pdfFile;
    }

    public function setPdfFileName(?string $pdfFileName): void
    {
        $this->pdfFileName = $pdfFileName;
    }

    public function getPdfFileName(): ?string
    {
        return $this->pdfFileName;
    }

    public function setPdfFileSize(?int $pdfFileSize): void
    {
        $this->pdfFileSize = $pdfFileSize;
    }

    public function getPdfFileSize(): ?int
    {
        return $this->pdfFileSize;
    }
}
```

**Sheet CRUD Controller** (`src/Controller/Admin/SheetCrudController.php`):
```php
use Vich\UploaderBundle\Form\Type\VichFileType;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;

public function configureFields(string $pageName): iterable
{
    // ... other fields

    // PDF upload on forms
    yield Field::new('pdfFile')
        ->setFormType(VichFileType::class)
        ->setFormTypeOptions([
            'allow_delete' => true,
            'download_uri' => true,
            'download_label' => 'Download current PDF',
        ])
        ->setLabel('PDF File')
        ->onlyOnForms();

    // PDF info on index/detail
    yield Field::new('pdfFileName')
        ->setLabel('PDF')
        ->setTemplatePath('admin/field/pdf_link.html.twig')
        ->hideOnForm();

    // ... other fields
}
```

**PDF Link Template** (`templates/admin/field/pdf_link.html.twig`):
```twig
{% if value %}
    <a href="{{ vich_uploader_asset(entity.instance, 'pdfFile') }}"
       target="_blank"
       class="btn btn-sm btn-primary">
        <i class="fa fa-file-pdf"></i> Download PDF
    </a>
    <small class="text-muted">({{ (entity.instance.pdfFileSize / 1024 / 1024)|number_format(2) }} MB)</small>
{% else %}
    <span class="text-muted">No PDF uploaded</span>
{% endif %}
```

**Acceptance Criteria**:
- PDF files can be uploaded
- Only PDF files accepted
- File size limited to 10MB
- Files stored in correct directory
- Download link works
- Old files deleted when new uploaded
- File size displayed

**Deliverables**:
- Updated Sheet entity
- Updated SheetCrudController
- PDF link template
- Working PDF upload/download

---

### Story 6.3: Implement Sheet Cover Image Upload

**Description**: Add cover image upload functionality to Sheet entity.

**Tasks**:
- [ ] Update Sheet entity for cover image upload
- [ ] Configure validation (JPG/PNG, max 2MB)
- [ ] Update CRUD controller for image upload
- [ ] Generate thumbnail for list view
- [ ] Add image preview in detail view
- [ ] Test image upload and display

**Technical Details**:

**Sheet Entity Updates** (`src/Entity/Sheet.php`):
```php
/**
 * @Vich\UploadableField(mapping="sheet_covers", fileNameProperty="coverImageName", size="coverImageSize")
 */
#[Assert\Image(
    maxSize: '2M',
    mimeTypes: ['image/jpeg', 'image/png', 'image/jpg'],
    mimeTypesMessage: 'Please upload a valid image (JPG or PNG)'
)]
private ?File $coverImageFile = null;

#[ORM\Column(type: 'string', length: 255, nullable: true)]
private ?string $coverImageName = null;

#[ORM\Column(type: 'integer', nullable: true)]
private ?int $coverImageSize = null;

public function setCoverImageFile(?File $file = null): void
{
    $this->coverImageFile = $file;

    if (null !== $file) {
        $this->updatedAt = new \DateTimeImmutable();
    }
}

public function getCoverImageFile(): ?File
{
    return $this->coverImageFile;
}

public function setCoverImageName(?string $coverImageName): void
{
    $this->coverImageName = $coverImageName;
}

public function getCoverImageName(): ?string
{
    return $this->coverImageName;
}
```

**Sheet CRUD Controller**:
```php
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;

public function configureFields(string $pageName): iterable
{
    // ... other fields

    // Cover image upload on forms
    yield Field::new('coverImageFile')
        ->setFormType(VichFileType::class)
        ->setFormTypeOptions([
            'allow_delete' => true,
            'download_uri' => false,
            'imagine_pattern' => 'thumbnail',
        ])
        ->setLabel('Cover Image')
        ->onlyOnForms();

    // Cover image display
    yield ImageField::new('coverImageName')
        ->setBasePath('/uploads/covers')
        ->setLabel('Cover')
        ->setTemplatePath('admin/field/cover_image.html.twig')
        ->hideOnForm();

    // ... other fields
}
```

**Cover Image Template** (`templates/admin/field/cover_image.html.twig`):
```twig
{% if value %}
    <img src="{{ vich_uploader_asset(entity.instance, 'coverImageFile') }}"
         alt="{{ entity.instance.title }}"
         style="max-width: 80px; max-height: 80px; border-radius: 4px;">
{% else %}
    <span class="text-muted">No cover</span>
{% endif %}
```

**Acceptance Criteria**:
- Cover images can be uploaded
- Only JPG/PNG accepted
- File size limited to 2MB
- Images displayed as thumbnails in list view
- Full size preview in detail view
- Old images deleted when new uploaded

**Deliverables**:
- Updated Sheet entity with cover image
- Updated SheetCrudController
- Cover image template
- Working image upload/display

---

### Story 6.4: Implement Organization Logo Upload

**Description**: Add logo upload functionality to Organization entity.

**Tasks**:
- [ ] Update Organization entity for logo upload
- [ ] Configure validation (JPG/PNG, max 1MB)
- [ ] Update CRUD controller
- [ ] Display logo in admin dashboard header
- [ ] Test logo upload and display

**Technical Details**:

**Organization Entity Updates** (`src/Entity/Organization.php`):
```php
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @Vich\Uploadable
 */
#[ORM\Entity]
class Organization
{
    // ... other properties

    /**
     * @Vich\UploadableField(mapping="organization_logos", fileNameProperty="logoName")
     */
    #[Assert\Image(
        maxSize: '1M',
        mimeTypes: ['image/jpeg', 'image/png', 'image/jpg'],
        mimeTypesMessage: 'Please upload a valid logo image'
    )]
    private ?File $logoFile = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $logoName = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    public function setLogoFile(?File $file = null): void
    {
        $this->logoFile = $file;

        if (null !== $file) {
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getLogoFile(): ?File
    {
        return $this->logoFile;
    }

    public function setLogoName(?string $logoName): void
    {
        $this->logoName = $logoName;
    }

    public function getLogoName(): ?string
    {
        return $this->logoName;
    }
}
```

**Organization CRUD Controller**:
```php
public function configureFields(string $pageName): iterable
{
    yield IdField::new('id')->onlyOnIndex();
    yield TextField::new('name');
    yield TextField::new('type');

    // Logo upload
    yield Field::new('logoFile')
        ->setFormType(VichFileType::class)
        ->setLabel('Logo')
        ->onlyOnForms();

    yield ImageField::new('logoName')
        ->setBasePath('/uploads/logos')
        ->setLabel('Logo')
        ->hideOnForm();

    yield DateTimeField::new('createdAt')->hideOnForm();
    yield DateTimeField::new('updatedAt')->hideOnForm();
}
```

**Dashboard Header with Logo** (`templates/admin/dashboard.html.twig`):
```twig
{% extends '@EasyAdmin/page/content.html.twig' %}

{% block content_header_wrapper %}
    <div class="d-flex align-items-center mb-3">
        {% if app.user and app.user.organization and app.user.organization.logoName %}
            <img src="{{ vich_uploader_asset(app.user.organization, 'logoFile') }}"
                 alt="{{ app.user.organization.name }}"
                 style="max-height: 50px; margin-right: 15px;">
        {% endif %}
        <div>
            <h1>{{ app.user.organization.name }}</h1>
            <small class="text-muted">{{ app.user.organization.type|title }}</small>
        </div>
    </div>

    {{ parent() }}
{% endblock %}
```

**Acceptance Criteria**:
- Organization logos can be uploaded
- Only JPG/PNG accepted
- File size limited to 1MB
- Logo displays in dashboard header
- Logo displays in organization list
- Old logos deleted when new uploaded

**Deliverables**:
- Updated Organization entity
- Updated OrganizationCrudController
- Enhanced dashboard template with logo
- Working logo upload/display

---

### Story 6.5: Create Custom Field Templates for File Display

**Description**: Create reusable custom templates for file display in admin.

**Tasks**:
- [ ] Create PDF download template
- [ ] Create image preview template
- [ ] Create file info template (size, type, date)
- [ ] Style templates consistently
- [ ] Test templates with various file types

**Technical Details**:

**PDF Download Template** (`templates/admin/field/pdf_download.html.twig`):
```twig
{% if value %}
    <div class="file-info">
        <a href="{{ vich_uploader_asset(entity.instance, 'pdfFile') }}"
           target="_blank"
           class="btn btn-sm btn-primary me-2">
            <i class="fa fa-download"></i> Download
        </a>
        <a href="{{ vich_uploader_asset(entity.instance, 'pdfFile') }}"
           target="_blank"
           class="btn btn-sm btn-outline-primary">
            <i class="fa fa-eye"></i> Preview
        </a>
        <div class="text-muted small mt-1">
            Size: {{ (entity.instance.pdfFileSize / 1024 / 1024)|number_format(2) }} MB
            {% if entity.instance.updatedAt %}
                | Uploaded: {{ entity.instance.updatedAt|date('Y-m-d H:i') }}
            {% endif %}
        </div>
    </div>
{% else %}
    <span class="badge bg-secondary">No PDF</span>
{% endif %}
```

**Image Preview Template** (`templates/admin/field/image_preview.html.twig`):
```twig
{% if value %}
    <div class="image-preview">
        <a href="{{ vich_uploader_asset(entity.instance, field.formTypeOption('mapping')) }}"
           target="_blank"
           data-bs-toggle="modal"
           data-bs-target="#imageModal{{ entity.instance.id }}">
            <img src="{{ vich_uploader_asset(entity.instance, field.formTypeOption('mapping')) }}"
                 alt="{{ entity.instance.title ?? 'Image' }}"
                 style="max-width: 150px; max-height: 150px; border-radius: 4px; cursor: pointer;">
        </a>
    </div>

    {# Modal for full-size preview #}
    <div class="modal fade" id="imageModal{{ entity.instance.id }}" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ entity.instance.title ?? 'Image Preview' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="{{ vich_uploader_asset(entity.instance, field.formTypeOption('mapping')) }}"
                         alt="{{ entity.instance.title ?? 'Image' }}"
                         style="max-width: 100%;">
                </div>
            </div>
        </div>
    </div>
{% else %}
    <span class="badge bg-secondary">No Image</span>
{% endif %}
```

**File Info Widget Template** (`templates/admin/field/file_info.html.twig`):
```twig
{% if value %}
    <div class="file-info-widget">
        <span class="badge bg-success">
            <i class="fa fa-check-circle"></i> Uploaded
        </span>
        <div class="small text-muted mt-1">
            <strong>{{ value }}</strong><br>
            {% if entity.instance[field.property ~ 'Size'] is defined %}
                Size: {{ (entity.instance[field.property ~ 'Size'] / 1024)|number_format(0) }} KB
            {% endif %}
        </div>
    </div>
{% else %}
    <span class="badge bg-warning">Not uploaded</span>
{% endif %}
```

**Acceptance Criteria**:
- Custom templates created for different file types
- PDF template shows download/preview links
- Image template shows thumbnail with modal preview
- File info template shows size and metadata
- Templates styled consistently with EasyAdmin
- Reusable across different entities

**Deliverables**:
- `templates/admin/field/pdf_download.html.twig`
- `templates/admin/field/image_preview.html.twig`
- `templates/admin/field/file_info.html.twig`

---

### Story 6.6: Add File Validation and Error Handling

**Description**: Implement comprehensive validation and user-friendly error messages.

**Tasks**:
- [ ] Add file type validation
- [ ] Add file size validation
- [ ] Create custom error messages
- [ ] Handle upload errors gracefully
- [ ] Test with invalid files

**Technical Details**:

**Enhanced Validation** (`src/Entity/Sheet.php`):
```php
use Symfony\Component\Validator\Constraints as Assert;

#[Assert\File(
    maxSize: '10M',
    maxSizeMessage: 'The PDF file is too large ({{ size }} {{ suffix }}). Maximum allowed size is {{ limit }} {{ suffix }}.',
    mimeTypes: ['application/pdf'],
    mimeTypesMessage: 'Please upload a valid PDF document. Uploaded file type: {{ type }}',
    uploadErrorMessage: 'There was an error uploading the file. Please try again.'
)]
private ?File $pdfFile = null;

#[Assert\Image(
    maxSize: '2M',
    maxSizeMessage: 'The image is too large ({{ size }} {{ suffix }}). Maximum allowed size is {{ limit }} {{ suffix }}.',
    mimeTypes: ['image/jpeg', 'image/png', 'image/jpg'],
    mimeTypesMessage: 'Please upload a valid image (JPG or PNG). Uploaded file type: {{ type }}',
    maxWidth: 3000,
    maxHeight: 3000,
    maxWidthMessage: 'Image width is too large ({{ width }}px). Maximum allowed width is {{ max_width }}px.',
    maxHeightMessage: 'Image height is too large ({{ height }}px). Maximum allowed height is {{ max_height }}px.'
)]
private ?File $coverImageFile = null;
```

**Custom Validation Constraint** (`src/Validator/SafeFileValidator.php`):
```php
<?php

namespace App\Validator;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class SafeFileValidator extends ConstraintValidator
{
    private array $dangerousExtensions = ['php', 'exe', 'sh', 'bat', 'cmd'];

    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof File) {
            return;
        }

        $extension = strtolower($value->guessExtension() ?? '');

        if (in_array($extension, $this->dangerousExtensions)) {
            $this->context->buildViolation('This file type is not allowed for security reasons.')
                ->addViolation();
        }
    }
}
```

**Error Display in Form** (automatic with Symfony):
```twig
{# Errors are automatically displayed above fields #}
{# Additional custom error styling in form theme #}
```

**Acceptance Criteria**:
- File type validation prevents wrong formats
- File size validation prevents oversized uploads
- Clear, user-friendly error messages
- Dangerous file types blocked
- Errors displayed prominently in forms
- Failed uploads don't delete existing files

**Deliverables**:
- Enhanced validation constraints
- Custom error messages
- Optional custom validator
- Tested with various invalid files

---

### Story 6.7: Implement File Deletion Handling

**Description**: Handle file deletion when entities are deleted or files are replaced.

**Tasks**:
- [ ] Configure automatic file deletion on entity delete
- [ ] Configure automatic file deletion on file replace
- [ ] Test file cleanup
- [ ] Handle orphaned files
- [ ] Create cleanup command for orphaned files

**Technical Details**:

**VichUploader Configuration** (already in Story 6.1):
```yaml
vich_uploader:
    mappings:
        sheet_pdfs:
            delete_on_update: true  # Delete old file when new uploaded
            delete_on_remove: true  # Delete file when entity deleted
```

**Manual Cleanup in Controller** (if needed):
```php
use Doctrine\ORM\EntityManagerInterface;

public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
{
    // VichUploader handles file deletion automatically
    // But you can add custom logic here if needed

    parent::deleteEntity($entityManager, $entityInstance);
}
```

**Cleanup Command for Orphaned Files** (`src/Command/CleanupOrphanedFilesCommand.php`):
```php
<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Doctrine\ORM\EntityManagerInterface;

#[AsCommand(
    name: 'app:cleanup-orphaned-files',
    description: 'Remove orphaned uploaded files'
)]
class CleanupOrphanedFilesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private string $projectDir
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $uploadDirs = [
            'public/uploads/sheets',
            'public/uploads/covers',
            'public/uploads/logos',
        ];

        $deletedCount = 0;

        foreach ($uploadDirs as $dir) {
            $fullPath = $this->projectDir . '/' . $dir;

            if (!is_dir($fullPath)) {
                continue;
            }

            $finder = new Finder();
            $finder->files()->in($fullPath);

            foreach ($finder as $file) {
                $fileName = $file->getFilename();

                // Check if file is referenced in database
                // This is simplified - implement actual check based on your needs
                $isOrphaned = $this->isFileOrphaned($fileName, $dir);

                if ($isOrphaned) {
                    unlink($file->getRealPath());
                    $deletedCount++;
                    $output->writeln("Deleted orphaned file: $fileName");
                }
            }
        }

        $output->writeln("Cleanup complete. Deleted $deletedCount orphaned files.");

        return Command::SUCCESS;
    }

    private function isFileOrphaned(string $fileName, string $directory): bool
    {
        // Implement logic to check if file exists in database
        // Return true if orphaned, false if still referenced
        return false; // Placeholder
    }
}
```

**Acceptance Criteria**:
- Files automatically deleted when entity deleted
- Old files deleted when new uploaded
- No orphaned files accumulate
- Cleanup command available for maintenance
- File system remains clean

**Deliverables**:
- Configured automatic file deletion
- Cleanup command
- Tested file deletion scenarios

---

## Epic Acceptance Criteria

- [ ] VichUploaderBundle (or custom solution) installed and configured
- [ ] PDF upload working for Sheet entity
- [ ] Cover image upload working for Sheet entity
- [ ] Logo upload working for Organization entity
- [ ] File validation working (type, size)
- [ ] Custom templates for file display
- [ ] Download links working
- [ ] Image previews working
- [ ] Automatic file deletion on entity delete
- [ ] Automatic file deletion on file replace
- [ ] User-friendly error messages
- [ ] All file operations tested

---

## Testing Checklist

```bash
# PDF Upload Tests
- [ ] Upload valid PDF (< 10MB)
- [ ] Try to upload PDF > 10MB (should fail)
- [ ] Try to upload non-PDF file (should fail)
- [ ] Download uploaded PDF
- [ ] Replace PDF with new one (old should delete)
- [ ] Delete entity (PDF should delete)

# Cover Image Tests
- [ ] Upload valid JPG image
- [ ] Upload valid PNG image
- [ ] Try to upload image > 2MB (should fail)
- [ ] Try to upload non-image file (should fail)
- [ ] View thumbnail in list
- [ ] View full size in detail
- [ ] Replace image (old should delete)
- [ ] Delete entity (image should delete)

# Logo Upload Tests
- [ ] Upload organization logo
- [ ] See logo in dashboard header
- [ ] Replace logo (old should delete)
- [ ] Delete organization (logo should delete)

# Error Handling
- [ ] Invalid file type shows clear error
- [ ] Oversized file shows clear error
- [ ] Upload error handled gracefully
- [ ] Error messages are user-friendly
```

---

## Deliverables

- [ ] VichUploaderBundle installed and configured
- [ ] Updated Sheet entity (PDF + cover image)
- [ ] Updated Organization entity (logo)
- [ ] Updated CRUD controllers
- [ ] Custom field templates for file display
- [ ] File validation and error handling
- [ ] Automatic file deletion
- [ ] Optional cleanup command
- [ ] `/public/uploads/` directory structure
- [ ] Working file upload/download/preview system

---

## Notes

- VichUploaderBundle is recommended for its robust feature set and EasyAdmin integration
- Always validate file uploads on both client and server side
- Use unique file names to prevent conflicts
- Automatic deletion prevents storage bloat
- Consider implementing image optimization (resize, compress) for production

---

## Next Epic

**Epic 7**: Custom Filters (LIVE CODING)
