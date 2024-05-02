<?php

declare(strict_types=1);

namespace Umbrellio\LTree\tests\functional\Resources;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Umbrellio\LTree\Exceptions\LTreeUndefinedNodeException;
use Umbrellio\LTree\Interfaces\LTreeModelInterface;
use Umbrellio\LTree\tests\_data\Models\CategoryStub;
use Umbrellio\LTree\tests\LTreeBaseTestCase;

class LTreeCollectionTest extends LTreeBaseTestCase
{
    #[Test]
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

    #[Test]
    #[DataProvider('provideNoConstencyTree')]
    public function loadMissingNodes(array $items, array $expected): void
    {
        $this->assertSame(
            CategoryStub::query()
                ->whereKey($items)
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

    #[Test]
    #[DataProvider('providePartialConstencyTree')]
    public function withoutLoadMissingForPartialTree(array $items, array $expected): void
    {
        $this->assertSame(
            CategoryStub::query()
                ->whereKey($items)
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

    public static function provideTreeWithoutLeaves(): Generator
    {
        yield 'without_leaves' => [
            'items' => [10, 7, 12],
            'expected' => [1, 3, 6, 11],
        ];
    }

    #[Test]
    #[DataProvider('provideTreeWithoutLeaves')]
    public function withoutLeaves(array $items, array $expected): void
    {
        $this->assertSame(
            CategoryStub::query()
                ->whereKey($items)
                ->get()
                ->withLeaves(false)
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

    public static function provideNoConstency(): Generator
    {
        yield 'non_consistent_without_loading' => [
            'items' => [1, 6, 8],
            'expected' => [1, 6, 8],
            'loadMissing' => false,
        ];
    }

    #[Test]
    #[DataProvider('provideNoConstency')]
    public function withoutLoadMissingNodes(array $items, array $expected, bool $loadMissing): void
    {
        $this->expectException(LTreeUndefinedNodeException::class);
        $this->assertSame(
            CategoryStub::query()
                ->whereKey($items)
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

    public static function provideNoConstencyTree(): Generator
    {
        yield 'non_consistent_with_loading' => [
            'items' => [7, 3, 12],
            'expected' => [1, 3, 7, 11, 12],
        ];
        yield 'consistent' => [
            'items' => [1, 3, 7],
            'expected' => [1, 3, 7],
        ];
    }
    public static function providePartialConstencyTree(): Generator
    {
        yield 'partial with single branch without single nodes' => [
            'items' => [3, 6, 7, 8, 9, 10],
            'expected' => [3, 6, 7, 8, 9, 10],
        ];
        yield 'partial with single branch without more nodes' => [
            'items' => [6, 8, 9, 10],
            'expected' => [6, 8, 9, 10],
        ];
        yield 'partial with more branches' => [
            'items' => [6, 8, 9, 10, 11, 12],
            'expected' => [6, 8, 9, 10, 11, 12],
        ];
    }
}
