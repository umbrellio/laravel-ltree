<?php

declare(strict_types=1);

namespace Umbrellio\LTree\tests\functional\Helpers;

use Generator;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Umbrellio\LTree\Collections\LTreeCollection;
use Umbrellio\LTree\Exceptions\LTreeReflectionException;
use Umbrellio\LTree\Exceptions\LTreeUndefinedNodeException;
use Umbrellio\LTree\Helpers\LTreeNode;
use Umbrellio\LTree\tests\LTreeBaseTestCase;

class LTreeNodeTest extends LTreeBaseTestCase
{
    private $hits;

    #[Test]
    public function nodeCantHaveUnknownParent()
    {
        $this->expectException(LTreeUndefinedNodeException::class);
        $this
            ->getCategoriesWithUnknownParent()
            ->toTree();
    }

    #[Test]
    public function nodeCantBeParentToItself()
    {
        $this->expectException(LTreeReflectionException::class);
        $this
            ->getCategoriesWithSelfParent()
            ->toTree();
    }

    #[Test]
    public function findSuccess(): void
    {
        $tree = $this
            ->getCategories()
            ->toTree();
        foreach (range(1, 12) as $id) {
            $node = $tree->findInTree($id);
            $this->assertNotNull($node);
            $model = $node->model;
            $this->assertSame($id, $model->id);
            $this->assertInstanceOf(Model::class, $model);
        }
        $this->assertNotNull($tree->getChildren()->find(11)->findInTree(12));
        $this->assertNotNull($tree->findInTree(1)->findInTree(2));
    }

    #[Test]
    #[DataProvider('provideUnknownNodes')]
    public function findFail($node): void
    {
        $tree = $this
            ->getCategories()
            ->toTree();
        $this->assertNull($tree->findInTree($node));
    }

    public static function provideUnknownNodes(): Generator
    {
        yield '-1' => [
            'node' => -1,
        ];
        yield '0' => [
            'node' => 0,
        ];
        yield '999' => [
            'node' => 999,
        ];
    }

    #[Test]
    public function countDescendants(): void
    {
        $tree = $this
            ->getCategories()
            ->toTree();
        $this->assertSame(12, $tree->countDescendants());
        $this->assertSame(9, $tree->findInTree(1)->countDescendants());
        $this->assertSame(1, $tree->findInTree(2)->countDescendants());
        $this->assertSame(5, $tree->findInTree(3)->countDescendants());
        $this->assertSame(3, $tree->findInTree(6)->countDescendants());
        $this->assertSame(1, $tree->findInTree(11)->countDescendants());
    }

    #[Test]
    public function each()
    {
        $tree = $this
            ->getCategories()
            ->toTree();
        $tree->sortTree([]);
        $collection = $tree->toCollection();
        $this->hits = 0;
        $tree->each(function ($item) use ($collection) {
            $key = $collection->search($item->getModel());
            $this->assertIsInt($key);
            $collection->pull($key);
            $this->hits++;
        });
        $this->assertSame(12, $this->hits);
        $this->assertCount(0, $collection);
    }

    #[Test]
    public function toTreeOnEmptyCollection(): void
    {
        $collection = new LTreeCollection();
        $this->assertInstanceOf(LTreeNode::class, $collection->toTree());
    }

    #[Test]
    public function toCollection(): void
    {
        $tree = $this
            ->getCategories()
            ->toTree();

        $this->assertSame('1', $tree->findInTree(1)->pathAsString());

        $collection = $tree->toCollection();
        $this->assertCount(12, $collection);
        for ($id = 1; $id <= 12; $id++) {
            $collection->find($id);
        }
    }

    #[Test]
    public function toTreeArray(): void
    {
        $formatter = static function ($item) {
            return [
                'my_id' => $item->id,
                'custom' => $item->id * 10,
            ];
        };
        $tree = $this
            ->getRandomCategories()
            ->toTree(false);
        $array = $tree->toTreeArray($formatter);
        $this->assertIsArray($array);
        $this->assertCount(2, $array);
        foreach ($array as $item) {
            $this->assertArrayHasKey('my_id', $item);
            $this->assertArrayHasKey('custom', $item);
            $this->assertArrayNotHasKey('id', $item);
        }
        $node = $array[0];
        $this->assertIsArray($node);
        $this->assertCount(3, $node);
        $this->assertArrayHasKey('my_id', $node);
        $this->assertArrayHasKey('custom', $node);
        $this->assertArrayNotHasKey('id', $node);
    }

    #[Test]
    public function nodePresenter()
    {
        $tree = $this
            ->getCategories()
            ->toTree();
        $node = $tree->findInTree(1);
        // node method
        $this->assertTrue(method_exists($node, 'getChildren'));
        $this->assertNotNull($node->getChildren());
        // model method
        $this->assertFalse(method_exists($node, 'getTable'));
        $this->assertNotNull($node->model->getTable());
    }

    #[Test]
    public function sortFail(): void
    {
        $tree = $this
            ->getRandomCategories()
            ->toTree();
        $this->expectException(InvalidArgumentException::class);
        $tree->sortTree(['name']);
    }

    #[Test]
    public function sort()
    {
        $tree = $this
            ->getRandomCategories()
            ->whereIn('id', [1, 2, 3, 4])
            ->toTree();
        $tree->sortTree([
            'name' => 'asc',
        ]);
        $sorted = $this->getSortedTree();
        foreach ($tree->getChildren() as $key => $node) {
            $this->assertSame($sorted[$key]['id'], $node->id);
            $this->assertCount(count($sorted[$key]['children']), $node->getChildren());
            foreach ($node->getChildren() as $childKey => $child) {
                $this->assertSame($child->id, $sorted[$key]['children'][$childKey]);
            }
        }
    }

    public function getSortedTree()
    {
        return [
            [
                'id' => 1,
                'children' => [3, 4, 2],
            ],
            [
                'id' => 4,
                'children' => [],
            ],
        ];
    }
}
