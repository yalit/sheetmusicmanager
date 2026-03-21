<?php

namespace App\Admin\Fields;

use App\Admin\Type\StringAutoCompleteType;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Symfony\Contracts\Translation\TranslatableInterface;


/**
 * It's a field that allows to:
 * - define a list of values
 */
class ChoiceAutoCompleteStringField implements FieldInterface
{
    use FieldTrait;

    public static function new(string $propertyName, string|TranslatableInterface|bool|null $label = null): self
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(StringAutoCompleteType::class)
            ->setTemplatePath("admin/fields/multi_choice_autocomplete.html.twig")
            ->setCustomOption('multiple', true);
            ;
    }

    public function setChoices(callable $choices): self
    {
        $this->setFormTypeOption('choices', $choices);
        return $this;
    }

    public function allowCreate(bool $create = true): self
    {
        $this->setFormTypeOption('create', $create);
        return $this;
    }

    public function allowMutiple(bool $multiple = true): self
    {
        $this->setFormTypeOption('multiple', $multiple);
        $this->setCustomOption('multiple', $multiple);
        return $this;
    }
}

