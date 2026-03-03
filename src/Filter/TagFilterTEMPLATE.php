<?php

namespace App\Filter;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\ChoiceFilterType;

/**
 * TEMPLATE for Live Coding
 *
 * This filter lets the user pick a tag from the list of all known tags and
 * filters sheets whose tags column contains that value.
 *
 * Steps to implement:
 * 1. Create the class implementing FilterInterface + FilterTrait
 * 2. Inject SheetRepository via constructor (to populate choices)
 * 3. Implement static new() with setFilterFqcn / setProperty / setLabel
 * 4. Implement buildForm() with a ChoiceType fed by $repository->getAllTags()
 * 5. Implement apply() with a LIKE query on the tags column
 */
class TagFilterTEMPLATE implements FilterInterface
{
    use FilterTrait;

    public static function new(string $property, string $label, callable $tagChoices): self
    {
        return (new self())
            ->setProperty($property)
            ->setLabel($label)
            ->setFormType(ChoiceFilterType::class)
            ->setFormTypeOptions(['value_type_options.choices' => $tagChoices()]);;
    }

    public function allowMultiple(bool $multiple = true): self
    {
        $this->setFormTypeOption('value_type_options.multiple', $multiple);
        return $this;
    }

    public function apply(QueryBuilder $queryBuilder, FilterDataDto $filterDataDto, ?FieldDto $fieldDto, EntityDto $entityDto): void
    {
        $alias = $queryBuilder->getRootAliases()[0];
        /** @var string|string[] $value */
        $value = $filterDataDto->getValue();
        $comparison = $filterDataDto->getComparison();

        if ($value === "") {
            return;
        }

        if (!is_array($value)) {
            $queryBuilder->andWhere($this->getWhereQueryString($comparison, $alias, "tag"))
                ->setParameter("tag", "%{$value}%");
        } else {
            $whereQueries = [];
            $parameters = new ArrayCollection();
            foreach ($value as $k => $item) {
                $whereQueries[] = $this->getWhereQueryString($comparison, $alias, 'tag_' . $k);
                $parameters->add(new Parameter("tag_{$k}", "%{$item}%"));

            }
            $queryBuilder->andWhere(implode(' OR ', $whereQueries))
                ->setParameters($parameters);
        }

    }

    private function getWhereQueryString(string $comparison, string $alias, string $tagParameter): string
    {
        return "{$alias}.tags" . ($comparison === "IN" ? " " : " not ") . "like :" . $tagParameter;
    }
}
