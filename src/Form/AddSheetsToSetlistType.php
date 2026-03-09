<?php

namespace App\Form;

use App\Entity\Setlist;
use App\Form\DataTransformer\SheetToStringDataTransformer;
use App\Message\AddSheetsToSetlist;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @template-extends AbstractType<AddSheetsToSetlist>
 */
class AddSheetsToSetlistType extends AbstractType
{
    public function __construct(private readonly SheetToStringDataTransformer $sheetToStringDataTransformer)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('setlist', EntityType::class, [
                'class'        => Setlist::class,
                'choice_label' => 'title',
                'label'        => 'Setlist',
                'placeholder'  => '-- Choisir --',
                'required'     => true,
                'attr'         => ['data-ea-widget' => 'ea-autocomplete'],
            ])
            ->add('sheets', HiddenType::class)
        ;

        $builder->get('sheets')->addModelTransformer($this->sheetToStringDataTransformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AddSheetsToSetlist::class,
        ]);
    }
}
