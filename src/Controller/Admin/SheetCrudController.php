<?php

namespace App\Controller\Admin;

use App\Entity\Sheet;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @extends AbstractCrudController<Sheet>
 */
class SheetCrudController extends AbstractCrudController
{
    public function __construct(
        private SluggerInterface $slugger,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Sheet::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('title', 'Titre');
        yield AssociationField::new('organization', 'Organisation');
        yield TextField::new('genre');
        yield TextField::new('difficulty', 'Difficulté');
        yield TextField::new('duration', 'Durée');
        yield TextField::new('keySignature', 'Tonalité');
        yield Field::new('file', 'Fichier PDF')
            ->setFormType(FileType::class)
            ->setFormTypeOption('mapped', false)
            ->setFormTypeOption('required', false)
            ->setFormTypeOption('attr', ['accept' => 'application/pdf'])
            ->onlyOnForms();
        yield TextField::new('file', 'Fichier PDF')
            ->hideOnForm();
        yield TextareaField::new('notes')
            ->hideOnIndex();
        yield AssociationField::new('credit', 'Crédits')
            ->hideOnForm();
    }

    /**
     * @param Sheet $entityInstance
     */
    public function persistEntity(\Doctrine\ORM\EntityManagerInterface $entityManager, mixed $entityInstance): void
    {
        $this->handleFileUpload($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    /**
     * @param Sheet $entityInstance
     */
    public function updateEntity(\Doctrine\ORM\EntityManagerInterface $entityManager, mixed $entityInstance): void
    {
        $this->handleFileUpload($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }

    private function handleFileUpload(Sheet $sheet): void
    {
        $request = $this->getContext()?->getRequest();
        if ($request === null) {
            return;
        }

        $sheetFiles = $request->files->get('Sheet');
        if (!is_array($sheetFiles) || !isset($sheetFiles['file'])) {
            return;
        }

        $file = $sheetFiles['file'];
        if (!$file instanceof UploadedFile) {
            return;
        }

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
        $sheet->setFile($newFilename);
    }
}
