<?php

namespace App\Filter;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\BooleanFilterType;

class HasPdfFilter implements FilterInterface
{
    use FilterTrait;

    public static function new(string $propertyName, string $label): self
    {
        return (new self())
            ->setFilterFqcn(__CLASS__)
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(BooleanFilterType::class)
            ;
    }

    public function apply(
        QueryBuilder $queryBuilder,
        FilterDataDto $filterDataDto,
        ?FieldDto $fieldDto,
        EntityDto $entityDto
    ): void {
        $alias = $queryBuilder->getRootAliases()[0];

        if ($filterDataDto->getValue() === true) {
            $queryBuilder->andWhere("$alias.files IS NOT NULL AND $alias.files != ''");
        } elseif ($filterDataDto->getValue() === false) {
            $queryBuilder->andWhere("$alias.files IS NULL OR $alias.files = ''");
        }
    }
}
