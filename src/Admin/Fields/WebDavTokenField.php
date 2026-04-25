<?php

namespace App\Admin\Fields;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Symfony\Contracts\Translation\TranslatableInterface;

class WebDavTokenField implements FieldInterface
{
    use FieldTrait;

    public static function new(string $propertyName, string|TranslatableInterface|bool|null $label = null): self
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setTemplatePath('admin/fields/webdav_token.html.twig')
        ;
    }
}
