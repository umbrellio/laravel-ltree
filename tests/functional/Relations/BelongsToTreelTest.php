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
    public function provideBelongsTree(): Generator
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

    /**
     * @test
     * @dataProvider provideBelongsTree
     */
    public function getBelongsToTree($path, $count, $level1, $level2, $level3)
    {
        $product = $this->createProduct([]);
        $product->category()
            ->associate($this->findNodeByPath($path));
        $product->save();

        $item = ProductStub::query()->first();
        $this->assertFalse(array_key_exists('category_tree', $item->toArray()));
        $this->assertSame($level1, optional($item->categoryTree->get(0))->getKey());
        $this->assertSame($level2, optional($item->categoryTree->get(1))->getKey());
        $this->assertSame($level3, optional($item->categoryTree->get(2))->getKey());

        $itemWith = ProductStub::with('categoryTree')->first();
        $this->assertTrue(array_key_exists('category_tree', $itemWith->toArray()));
        $this->assertSame($count, $itemWith->categoryTree->count());
        $this->assertSame($level1, optional($itemWith->categoryTree->get(0))->getKey());
        $this->assertSame($level2, optional($itemWith->categoryTree->get(1))->getKey());
        $this->assertSame($level3, optional($itemWith->categoryTree->get(2))->getKey());
    }

    /**
     * @test
     */
    public function missingLtreeModel(): void
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
        $childSome->parentTree();
    }

    private function getCategorySome(array $data = []): CategorySomeStub
    {
        return new CategorySomeStub($data);
    }
}
