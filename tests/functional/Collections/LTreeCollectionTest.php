<?php

declare(strict_types=1);

namespace Umbrellio\LTree\tests\functional\Resources;

use Generator;
use Umbrellio\LTree\Exceptions\LTreeUndefinedNodeException;
use Umbrellio\LTree\Interfaces\LTreeModelInterface;
use Umbrellio\LTree\tests\_data\Models\CategoryStub;
use Umbrellio\LTree\tests\LTreeBaseTestCase;

class LTreeCollectionTest extends LTreeBaseTestCase
{
    /**
     * @test
     */
    public function collectionCanBeConvertedIntoTree()
    {
        $tree = $this
            ->getCategories()
            ->toTree();
        $this->assertSame(2, $tree->getChildren()->count());
        $this->assertSame(3, $tree->getChildren()[0]->getChildren()->count());
        $this->assertSame(1, $tree->getChildren()->find(11)->getChildren()->count());
        $this->assertSame($tree, $tree->getChildren()[0]->getParent());
    }

    /**
     * @test
     * @dataProvider provideNoConstencyTree
     */
    public function loadMissingNodes(array $ids, array $expected): void
    {
        $this->assertSame(
            CategoryStub::query()
                ->whereKey($ids)
                ->get()
                ->toTree()
                ->toCollection()
                ->sortBy(function (LTreeModelInterface $item) {
                    return $item->getKey();
                })
                ->pluck('id')
                ->toArray(),
            $expected
        );
    }

    public function provideNoConstency(): Generator
    {
        yield 'non_consistent_without_loading' => [
            'ids' => [7, 3, 12],
            'expected' => [3, 7, 12],
            'loadMissing' => false,
        ];
    }

    /**
     * @test
     * @dataProvider provideNoConstency
     */
    public function withoutLoadMissingNodes(array $ids, array $expected): void
    {
        $this->expectException(LTreeUndefinedNodeException::class);
        $this->assertSame(
            CategoryStub::query()
                ->whereKey($ids)
                ->get()
                ->toTree(true, false)
                ->toCollection()
                ->sortBy(function (LTreeModelInterface $item) {
                    return $item->getKey();
                })
                ->pluck('id')
                ->toArray(),
            $expected
        );
    }

    public function provideNoConstencyTree(): Generator
    {
        yield 'non_consistent_with_loading' => [
            'ids' => [7, 3, 12],
            'expected' => [1, 3, 7, 11, 12],
        ];
        yield 'consistent' => [
            'items' => [1, 3, 7],
            'expected' => [1, 3, 7],
        ];
    }
}
