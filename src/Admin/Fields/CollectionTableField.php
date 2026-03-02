<?php

namespace App\Admin\Fields;

use App\Admin\Type\TableCollectionType;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;

/**
 * Factory that returns a CollectionField configured for table rendering.
 *
 * Returning CollectionField (not a new FieldInterface class) ensures
 * EasyAdmin's CollectionConfigurator processes it, giving useEntryCrudForm()
 * support for free.
 */
class CollectionTableField
{
    public static function new(string $propertyName, ?string $label = null): CollectionField
    {
        return CollectionField::new($propertyName, $label)
            ->setFormType(TableCollectionType::class)
        ;
    }
}
