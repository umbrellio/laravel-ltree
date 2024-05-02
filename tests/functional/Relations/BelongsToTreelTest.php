<?php

declare(strict_types=1);

namespace Umbrellio\LTree\tests\functional\Relations;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Umbrellio\LTree\Exceptions\InvalidTraitInjectionClass;
use Umbrellio\LTree\tests\_data\Models\CategorySomeStub;
use Umbrellio\LTree\tests\_data\Models\ProductStub;
use Umbrellio\LTree\tests\LTreeBaseTestCase;

class BelongsToTreelTest extends LTreeBaseTestCase
{
    public static function provideBelongsParentsTree(): Generator
    {
        yield 'two_levels' => [
            'path' => '11.12',
            'count' => 2,
            'level1' => 11,
            'level2' => 12,
            'level3' => null,
        ];
        yield 'three_levels' => [
            'path' => '1.2.5',
            'count' => 3,
            'level1' => 1,
            'level2' => 2,
            'level3' => 5,
        ];
    }

    public static function provideBelongsDescendantsTree(): Generator
    {
        yield 'with_descendants' => [
            'path' => '1.3',
            'count' => 6,
            'level1' => 3,
            'level2' => 6,
            'level3' => 10,
            'level4' => 8,
            'level5' => 9,
            'level6' => 7,
        ];
    }

    #[Test]
    #[DataProvider('provideBelongsParentsTree')]
    public function getBelongsToParentsTree($path, $count, $level1, $level2, $level3)
    {
        $product = $this->createProduct([]);
        $product
            ->category()
            ->associate($this->findNodeByPath($path));
        $product->save();

        $item = ProductStub::query()->first();
        $this->assertFalse(array_key_exists('category_ancestors_tree', $item->toArray()));
        $this->assertSame($level1, optional($item->categoryAncestorsTree->get(0))->getKey());
        $this->assertSame($level2, optional($item->categoryAncestorsTree->get(1))->getKey());
        $this->assertSame($level3, optional($item->categoryAncestorsTree->get(2))->getKey());

        $itemWith = ProductStub::with('categoryAncestorsTree')->first();
        $this->assertTrue(array_key_exists('category_ancestors_tree', $itemWith->toArray()));
        $this->assertSame($count, $itemWith->categoryAncestorsTree->count());
        $this->assertSame($level1, optional($itemWith->categoryAncestorsTree->get(0))->getKey());
        $this->assertSame($level2, optional($itemWith->categoryAncestorsTree->get(1))->getKey());
        $this->assertSame($level3, optional($itemWith->categoryAncestorsTree->get(2))->getKey());
    }

    #[Test]
    #[DataProvider('provideBelongsDescendantsTree')]
    public function getBelongsToDescendantsTree($path, $count, $level1, $level2, $level3, $level4, $level5, $level6)
    {
        $product = $this->createProduct([]);
        $product
            ->category()
            ->associate($this->findNodeByPath($path));
        $product->save();

        $item = ProductStub::query()->first();
        $this->assertFalse(array_key_exists('category_descendants_tree', $item->toArray()));
        $this->assertSame($level1, optional($item->categoryDescendantsTree->get(0))->getKey());
        $this->assertSame($level2, optional($item->categoryDescendantsTree->get(1))->getKey());
        $this->assertSame($level3, optional($item->categoryDescendantsTree->get(2))->getKey());
        $this->assertSame($level4, optional($item->categoryDescendantsTree->get(3))->getKey());
        $this->assertSame($level5, optional($item->categoryDescendantsTree->get(4))->getKey());
        $this->assertSame($level6, optional($item->categoryDescendantsTree->get(5))->getKey());

        $itemWith = ProductStub::with('categoryDescendantsTree')->first();

        $this->assertTrue(array_key_exists('category_descendants_tree', $itemWith->toArray()));
        $this->assertSame($count, $itemWith->categoryDescendantsTree->count());
        $this->assertSame($level1, optional($itemWith->categoryDescendantsTree->get(0))->getKey());
        $this->assertSame($level2, optional($itemWith->categoryDescendantsTree->get(1))->getKey());
        $this->assertSame($level3, optional($itemWith->categoryDescendantsTree->get(2))->getKey());
        $this->assertSame($level4, optional($itemWith->categoryDescendantsTree->get(3))->getKey());
        $this->assertSame($level5, optional($itemWith->categoryDescendantsTree->get(4))->getKey());
        $this->assertSame($level6, optional($itemWith->categoryDescendantsTree->get(5))->getKey());
    }

    #[Test]
    public function missingParentsLtreeModel(): void
    {
        $rootSome = $this->getCategorySome([
            'id' => 16,
            'path' => '16',
            'parent_id' => null,
        ]);
        $rootSome->save();

        $childSome = $this->getCategorySome([
            'id' => 17,
            'path' => '16.17',
            'parent_id' => $rootSome->getKey(),
        ]);
        $childSome->save();

        $this->expectException(InvalidTraitInjectionClass::class);
        $childSome->parentAncestorsTree();
    }

    public function missingDescendantsLtreeModel(): void
    {
        $rootSome = $this->getCategorySome([
            'id' => 16,
            'path' => '16',
            'parent_id' => null,
        ]);
        $rootSome->save();

        $childSome = $this->getCategorySome([
            'id' => 17,
            'path' => '16.17',
            'parent_id' => $rootSome->getKey(),
        ]);
        $childSome->save();

        $this->expectException(InvalidTraitInjectionClass::class);
        $rootSome->parentDescendantsTree();
    }

    private function getCategorySome(array $data = []): CategorySomeStub
    {
        return new CategorySomeStub($data);
    }
}
