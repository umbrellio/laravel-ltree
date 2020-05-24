<?php

declare(strict_types=1);

namespace Umbrellio\LTree\tests\Helpers;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Umbrellio\LTree\Collections\LTreeCollection;
use Umbrellio\LTree\Exceptions\LTreeReflectionException;
use Umbrellio\LTree\Exceptions\LTreeUndefinedNodeException;
use Umbrellio\LTree\Helpers\LTreeNode;
use Umbrellio\LTree\Interfaces\LTreeModelInterface;
use Umbrellio\LTree\tests\TestCase;
use Umbrellio\LTree\Traits\LTreeModelTrait;

class LtreeTest extends TestCase
{
    private $hits;

    /**
     * @test
     */
    public function collectionCanBeConvertedIntoTree()
    {
        $tree = $this->getTree();
        $this->assertSame(5, $tree->getChildren()->count());
        $this->assertSame(3, $tree->getChildren()[0]->getChildren()->count());
        $this->assertSame(1, $tree->getChildren()->find(10)->getChildren()->count());
        $this->assertSame($tree, $tree->getChildren()[0]->getParent());
    }

    /**
     * @test
     */
    public function nodeCantHaveUnknownParent()
    {
        $this->expectException(LTreeUndefinedNodeException::class);
        $this->getTreeWithUnknownParent();
    }

    /**
     * @test
     */
    public function nodeCantBeParentToItself()
    {
        $this->expectException(LTreeReflectionException::class);
        $this->getTreeWithSelfParentNode();
    }

    /**
     * @test
     */
    public function findSuccess()
    {
        $tree = $this->getTree();
        foreach (range(1, 10) as $id) {
            $node = $tree->findInTree($id);
            $this->assertNotNull($node);
            $model = $node->model;
            $this->assertSame($id, $model->id);
            $this->assertInstanceOf(Model::class, $model);
        }
        $this->assertNotNull($tree->getChildren()->find(10)->findInTree(11));
        $this->assertNotNull($tree->findInTree(7)->findInTree(7));
    }

    /**
     * @test
     */
    public function findFail()
    {
        $tree = $this->getTree();
        foreach ($this->provideUnknownNodes() as $row) {
            list($id) = $row;
            $this->assertNull($tree->findInTree($id));
        }
    }

    /**
     * @test
     */
    public function countDescendants()
    {
        $tree = $this->getTree();
        $this->assertSame(11, $tree->countDescendants());
        $this->assertSame(5, $tree->findInTree(1)->countDescendants());
        $this->assertSame(2, $tree->findInTree(2)->countDescendants());
        $this->assertSame(0, $tree->findInTree(5)->countDescendants());
        $this->assertSame(0, $tree->findInTree(7)->countDescendants());
        $this->assertSame(1, $tree->findInTree(10)->countDescendants());
    }

    /**
     * @test
     */
    public function each()
    {
        $tree = $this->getTree();
        $collection = $this->getCollection();
        $this->hits = 0;
        $tree->each(function ($item) use ($collection) {
            $key = $collection->search($item->getModel());
            $this->assertIsInt($key);
            $collection->pull($key);
            $this->hits++;
        });
        $this->assertSame(11, $this->hits);
        $this->assertCount(0, $collection);
    }

    /**
     * @test
     */
    public function toTreeOnEmptyCollection(): void
    {
        $collection = new LTreeCollection();
        $tree = $collection->toTree();

        $this->assertInstanceOf(LTreeNode::class, $tree);
    }

    /**
     * @test
     */
    public function toCollection()
    {
        $tree = $this->getTree();

        $this->assertSame('1', $tree->findInTree(1)->pathAsString());

        $collection = $tree->toCollection();
        $this->assertCount(11, $collection);
        for ($id = 1; $id <= 11; $id++) {
            $collection->find($id);
        }
    }

    /**
     * @test
     */
    public function toTreeArray()
    {
        $formatter = static function ($item) {
            return [
                'my_id' => $item->id,
                'custom' => $item->id * 10,
            ];
        };
        $tree = $this->getTree();
        $array = $tree->toTreeArray($formatter);
        $this->assertIsArray($array);
        $this->assertCount(5, $array);
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

    /**
     * @test
     */
    public function nodePresenter()
    {
        $tree = $this->getTree();
        $node = $tree->findInTree(1);
        // node method
        $this->assertTrue(method_exists($node, 'getChildren'));
        $this->assertNotNull($node->getChildren());
        // model method
        $this->assertFalse(method_exists($node, 'getTable'));
        $this->assertNotNull($node->getTable());
    }

    public function getCollection()
    {
        return $this->getLtreeModelsCollection(
            [
                [
                    'id' => 1,
                    'parent_id' => null,
                    'path' => '1',
                ],
                [
                    'id' => 2,
                    'parent_id' => 1,
                    'path' => '1.2',
                ],
                [
                    'id' => 3,
                    'parent_id' => 2,
                    'path' => '1.2.3',
                ],
                [
                    'id' => 4,
                    'parent_id' => 2,
                    'path' => '1.2.4',
                ],
                [
                    'id' => 5,
                    'parent_id' => 1,
                    'path' => '1.5',
                ],
                [
                    'id' => 6,
                    'parent_id' => 1,
                    'path' => '1.6',
                ],
                [
                    'id' => 7,
                    'parent_id' => null,
                    'path' => '7',
                ],
                [
                    'id' => 8,
                    'parent_id' => null,
                    'path' => '8',
                ],
                [
                    'id' => 9,
                    'parent_id' => null,
                    'path' => '9',
                ],
                [
                    'id' => 10,
                    'parent_id' => null,
                    'path' => '10',
                ],
                [
                    'id' => 11,
                    'parent_id' => 10,
                    'path' => '10.11',
                ],
            ]
        );
    }

    public function getTree(): LTreeNode
    {
        $collection = $this->getCollection()->shuffle();
        return $collection->toTree();
    }

    public function getTreeWithUnknownParent(): LTreeNode
    {
        $collection = $this->getCollection();
        $collection->add($this->getLtreeModel([
            'id' => 888,
            'parent_id' => 777,
            'path' => '777.888',
        ]));
        return $collection->toTree();
    }

    public function getTreeWithSelfParentNode(): LTreeNode
    {
        $collection = $this->getCollection();
        $collection->add($this->getLtreeModel([
            'id' => 777,
            'parent_id' => 777,
            'path' => '777.777',
        ]));
        return $collection->toTree();
    }

    public function provideUnknownNodes()
    {
        yield [-1];
        yield [0];
        yield [99];
    }

    /**
     * @test
     */
    public function sort()
    {
        $tree = $this->getUnsortedTree()->toTree();
        $tree->sortTree(['name' => 'asc']);
        $sorted = $this->getSortedTree();
        foreach ($tree->getChildren() as $key => $node) {
            $this->assertSame($sorted[$key]['id'], $node->id);
            $this->assertCount(count($sorted[$key]['children']), $node->getChildren());
            foreach ($node->getChildren() as $childKey => $child) {
                $this->assertSame($child->id, $sorted[$key]['children'][$childKey]);
            }
        }
    }

    /**
     * @test
     */
    public function sortFail()
    {
        $tree = $this->getUnsortedTree()->toTree();
        $this->expectException(InvalidArgumentException::class);
        $tree->sortTree(['name']);
    }

    public function getUnsortedTree()
    {
        return $this->getLtreeModelsCollection(
            [
                [
                    'id' => 1,
                    'parent_id' => null,
                    'path' => '1',
                    'name' => 'Vegetables',
                ],
                [
                    'id' => 5,
                    'parent_id' => null,
                    'path' => '5',
                    'name' => 'Fruits',
                ],
                [
                    'id' => 2,
                    'parent_id' => 5,
                    'path' => '5.2',
                    'name' => 'Banana',
                ],
                [
                    'id' => 3,
                    'parent_id' => 5,
                    'path' => '5.3',
                    'name' => 'Orange',
                ],
                [
                    'id' => 4,
                    'parent_id' => 5,
                    'path' => '5.4',
                    'name' => 'Apple',
                ],
                [
                    'id' => 6,
                    'parent_id' => 5,
                    'path' => '5.6',
                    'name' => 'Apple',
                ],
            ]);
    }

    public function getSortedTree()
    {
        return [
            [
                'id' => 5,
                'children' => [4, 6, 2, 3],
            ],
            [
                'id' => 1,
                'children' => [],
            ],
        ];
    }

    private function getLtreeModelsCollection(array $items)
    {
        $collection = new LTreeCollection();
        foreach ($items as $item) {
            $collection->add($this->getLtreeModel($item));
        }
        return $collection;
    }

    private function getLtreeModel($data)
    {
        return new class($data) extends Model implements LTreeModelInterface {
            use LTreeModelTrait;

            protected $fillable = ['id', 'path', 'parent_id', 'name'];
        };
    }
}
