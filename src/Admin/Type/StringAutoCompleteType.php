<?php

namespace App\Admin\Type;

use App\DataTransformer\ArrayToStringTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<TextType>
 */
final class StringAutoCompleteType extends AbstractType
{
    public function getParent(): string
    {
        return TextType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        if (!array_key_exists('separator', $options)) {
            $options['separator'] = ArrayToStringTransformer::DEFAULT_SEPARATOR;
        }

        if ($options['multiple']) {
            $builder->addModelTransformer(new ArrayToStringTransformer($options['separator']));
        }
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);
        $choices = $options['choices'];
        if (!is_callable($choices)) {
            $choices = fn() => $choices;
        }

        $view->vars = array_merge($view->vars, [
            'choices' => $choices(),
            'separator' => $options['separator'],
            'create' => $options['create'] ? 'create' : '',
            'multiple' => $options['multiple'] ? 'multiple' : '',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "choices" => fn() => [],
            'separator' => ArrayToStringTransformer::DEFAULT_SEPARATOR,
            'create' => true,
            'multiple' => true,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'app_autocomplete';
    }

}

