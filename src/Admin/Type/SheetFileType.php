<?php

namespace App\Admin\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<FileType>
 */
final class SheetFileType extends AbstractType
{
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);
        $view->vars['existing_files'] = $options['existing_files'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'multiple' => true,
            'existing_files' => [],
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
