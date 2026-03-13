<?php

namespace App\Admin\Fields;

use App\Admin\Type\SheetFileType;
use App\Entity\ValueObject\StoredFile;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;


/**
 * So it's a field that allows to:
 * - in new form, present a file upload field that accepts only PDF files (there should be a setting to allow one or multiple)
 *      - when file is selected, there should be the name of the file (or files) that appears below the input field
 * - in edit forms, the list of existing files for that field should be listed below the input field with a delete capability
 * - after submitting, the file should be uploaded to the server in a specific location (settable via a function on the field)
 * - in list or detail pages, it should display the list of files with a link to the actual file
 */
class PDFField implements FieldInterface
{
    use FieldTrait;

    public static function new(string $propertyName, ?string $label = null): PDFField
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(SheetFileType::class)
            ->setFormTypeOption('attr', ['accept' => 'application/pdf'])
            ->setTemplatePath("admin/fields/pdf.html.twig")
            ;
    }

    /**
     * @param StoredFile[] $data
     */
    public function setExistingFiles(array $data): self
    {
        $this->setFormTypeOption('existing_files', $data);
        return $this;
    }
}
