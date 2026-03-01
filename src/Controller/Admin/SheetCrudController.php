<?php

namespace App\Controller\Admin;

use App\Admin\Fields\ChoiceAutoCompleteStringField;
use App\Admin\Fields\PDFField;
use App\Entity\Sheet;
use App\Repository\SheetRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @extends AbstractCrudController<Sheet>
 */
class SheetCrudController extends AbstractCrudController
{
    private string $uploadDir = "public/uploads/sheets";

    public function __construct(
        private readonly SheetRepository $sheetRepository,
        private readonly RequestStack $requestStack,
        private readonly Filesystem   $filesystem,
        private readonly string       $projectDir,
    )
    {
    }

    public static function getEntityFqcn(): string
    {
        return Sheet::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield FormField::addColumn(8);
        yield FormField::addPanel("General");
        yield TextField::new('title', 'Titre')->setColumns(8);

        yield PDFField::new('files', 'Fichier PDF')
            ->setUploadDir($this->uploadDir)
            ->setRequired($pageName === Crud::PAGE_NEW)
            ;

        yield FormField::addColumn(4);
        yield FormField::addPanel("Details");
        yield ChoiceAutoCompleteStringField::new('refs')
            ->setChoices([$this->sheetRepository, 'getAllRefs'])
        ;
        yield ChoiceAutoCompleteStringField::new('tags')
            ->setChoices([$this->sheetRepository, 'getAllTags'])
        ;

        yield FormField::addPanel("Details");
        yield TextareaField::new('notes')
            ->setNumOfRows(5)
            ->hideOnIndex();
    }

    /**
     * @param Sheet $entityInstance
     */
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $kept = $request?->request->get('app_pdf_field_kept', '[]');
        $removed = $request?->request->get('app_pdf_field_removed', '[]');
        if (!is_string($kept) || !is_string($removed)) {
            return;
        }

        $oldFiles = $entityInstance->getFiles();

        /** @var string[] $keptFiles */
        $keptFiles = json_decode($kept, true) ?: [];
        /** @var string[] $removedFiles */
        $removedFiles = json_decode($removed, true) ?: [];
        $newFiles = $entityInstance->getFiles();

        foreach ($removedFiles as $filename) {
            $fullPath = $this->projectDir . DIRECTORY_SEPARATOR . $this->uploadDir . DIRECTORY_SEPARATOR . $filename;
            $this->filesystem->remove($fullPath);
        }

        $entityInstance->setFiles(array_merge($keptFiles, $newFiles));

        parent::updateEntity($entityManager, $entityInstance);
    }
}
