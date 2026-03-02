<?php

namespace App\Admin\Type;

use App\Entity\SetListItem;
use App\Entity\Sheet;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SetListItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('position', IntegerType::class, [
                'label' => 'Position',
            ])
            ->add('name', TextType::class, [
                'label' => 'Name',
                'required' => false,
            ])
            ->add('sheet', EntityType::class, [
                'class' => Sheet::class,
                'label' => 'Sheet',
                'choice_label' => 'title',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SetListItem::class,
        ]);
    }
}
