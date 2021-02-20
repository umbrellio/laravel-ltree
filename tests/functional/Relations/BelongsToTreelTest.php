<?php

declare(strict_types=1);

namespace Umbrellio\LTree\tests\functional\Relations;

use Generator;
use Umbrellio\LTree\Exceptions\InvalidTraitInjectionClass;
use Umbrellio\LTree\tests\_data\Models\CategorySomeStub;
use Umbrellio\LTree\tests\_data\Models\ProductStub;
use Umbrellio\LTree\tests\LTreeBaseTestCase;

class BelongsToTreelTest extends LTreeBaseTestCase
{
    public function provideBelongsParentsTree(): Generator
    {
        yield 'two_levels' => [
            'path' => '11.12',
            'count' => 2,
            'expected1' => 11,
            'expected2' => 12,
            'expected3' => null,
        ];
        yield 'three_levels' => [
            'path' => '1.2.5',
            'count' => 3,
            'expected1' => 1,
            'expected2' => 2,
            'expected3' => 5,
        ];
    }

    public function provideBelongsDescendantsTree(): Generator
    {
        yield 'with_descendants' => [
            'path' => '1.3',
            'count' => 6,
            'expected1' => 3,
            'expected2' => 6,
            'expected3' => 10,
            'expected4' => 8,
            'expected5' => 9,
            'expected6' => 7,
        ];
    }

    /**
     * @test
     * @dataProvider provideBelongsParentsTree
     */
    public function getBelongsToParentsTree($path, $count, $level1, $level2, $level3)
    {
        $product = $this->createProduct([]);
        $product
            ->category()
            ->associate($this->findNodeByPath($path));
        $product->save();

        $item = ProductStub::query()->first();
        $this->assertFalse(array_key_exists('category_parents_tree', $item->toArray()));
        $this->assertSame($level1, optional($item->categoryParentsTree->get(0))->getKey());
        $this->assertSame($level2, optional($item->categoryParentsTree->get(1))->getKey());
        $this->assertSame($level3, optional($item->categoryParentsTree->get(2))->getKey());

        $itemWith = ProductStub::with('categoryParentsTree')->first();
        $this->assertTrue(array_key_exists('category_parents_tree', $itemWith->toArray()));
        $this->assertSame($count, $itemWith->categoryParentsTree->count());
        $this->assertSame($level1, optional($itemWith->categoryParentsTree->get(0))->getKey());
        $this->assertSame($level2, optional($itemWith->categoryParentsTree->get(1))->getKey());
        $this->assertSame($level3, optional($itemWith->categoryParentsTree->get(2))->getKey());
    }

    /**
     * @test
     * @dataProvider provideBelongsDescendantsTree
     */
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

    /**
     * @test
     */
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
        $childSome->parentParentsTree();
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
