<?php

namespace App\Admin\Type;

use App\DataTransformer\StringToFileTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<FileType>
 */
final class SheetFileType extends AbstractType
{
    public function __construct(
        private readonly string $rootDir
    )
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);
        /** @var string $uploadDir */
        $uploadDir = $options['upload_dir'] ?? '';
        $uploadDir = $this->rootDir . DIRECTORY_SEPARATOR . $uploadDir;

        $builder->addModelTransformer(new StringToFileTransformer($uploadDir));
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        $existingFiles = [];
        $normData = $form->getNormData();

        if (is_array($normData)) {
            /** @var string $uploadDir */
            $uploadDir = $options['upload_dir'] ?? '';
            $webPrefix = '/' . preg_replace('#^public/#', '', $uploadDir);

            foreach ($normData as $file) {
                if ($file instanceof File && !$file instanceof UploadedFile) {
                    $existingFiles[] = [
                        'name' => $file->getFilename(),
                        'size' => $this->formatFileSize($file->getSize()),
                        'web_path' => $webPrefix . '/' . $file->getFilename(),
                    ];
                }
            }
        }

        $view->vars['existing_files'] = $existingFiles;
    }

    private function formatFileSize(int|false $bytes): string
    {
        if ($bytes === false || $bytes < 1024) {
            return ($bytes ?: 0) . ' o';
        }
        if ($bytes < 1024 * 1024) {
            return round($bytes / 1024, 1) . ' Ko';
        }
        return round($bytes / (1024 * 1024), 1) . ' Mo';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'upload_dir' => 'public/uploads/files',
            'multiple' => true
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'app_pdf_field';
    }

    public function getParent(): string
    {
        return FileType::class;
    }
}
