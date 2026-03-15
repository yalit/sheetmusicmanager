<?php

namespace App\Tests\Admin\Type;

use App\Admin\Type\TableCollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Test\TypeTestCase;

final class TableCollectionTypeTest extends TypeTestCase
{
    public function testAllowSortDefaultsToFalse(): void
    {
        $view = $this->factory->create(TableCollectionType::class, null, [
            'entry_type' => TextType::class,
        ])->createView();

        static::assertFalse($view->vars['allow_sort']);
    }

    public function testAllowSortTrueIsPassedToView(): void
    {
        $view = $this->factory->create(TableCollectionType::class, null, [
            'entry_type' => TextType::class,
            'allow_sort' => true,
        ])->createView();

        static::assertTrue($view->vars['allow_sort']);
    }

    public function testDataTableCollectionAttrIsAlwaysSet(): void
    {
        $view = $this->factory->create(TableCollectionType::class, null, [
            'entry_type' => TextType::class,
        ])->createView();

        static::assertSame('true', $view->vars['attr']['data-table-collection']);
    }
}
