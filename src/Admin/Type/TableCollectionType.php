<?php

namespace App\Admin\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A CollectionType wrapper that enables table-layout rendering.
 *
 * Sets 'data-table-collection' on the collection's attr so that the
 * collection_entry_row override in form.html.twig can detect it and
 * render entries as <tr> rows instead of EasyAdmin's accordion.
 * @extends AbstractType<CollectionType>
 */
class TableCollectionType extends AbstractType
{
    public function getParent(): string
    {
        return CollectionType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'table_collection';
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['attr']['data-table-collection'] = 'true';
        $view->vars['allow_sort'] = $options['allow_sort'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['allow_sort' => false]);
    }
}
