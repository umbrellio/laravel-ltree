<?php

declare(strict_types=1);

namespace Umbrellio\LTree\tests\functional\Helpers;

use Generator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Umbrellio\LTree\Collections\LTreeCollection;
use Umbrellio\LTree\Exceptions\LTreeReflectionException;
use Umbrellio\LTree\Exceptions\LTreeUndefinedNodeException;
use Umbrellio\LTree\Helpers\LTreeNode;
use Umbrellio\LTree\Interfaces\LTreeModelInterface;
use Umbrellio\LTree\tests\_data\Models\CategoryStub;
use Umbrellio\LTree\tests\_data\Models\CategoryStubResourceCollection;
use Umbrellio\LTree\tests\_data\Traits\HasLTreeTables;
use Umbrellio\LTree\tests\FunctionalTestCase;

class LtreeTest extends FunctionalTestCase
{
    use HasLTreeTables;

    private $hits;

    protected function setUp(): void
    {
        parent::setUp();

        $this->initLTreeService();
        $this->initLTreeCategories();
    }

    /**
     * @test
     */
    public function collectionCanBeConvertedIntoTree()
    {
        $tree = $this->getCategories()
            ->toTree();
        $this->assertSame(2, $tree->getChildren()->count());
        $this->assertSame(3, $tree->getChildren()[0]->getChildren()->count());
        $this->assertSame(1, $tree->getChildren()->find(11)->getChildren()->count());
        $this->assertSame($tree, $tree->getChildren()[0]->getParent());
    }

    /**
     * @test
     */
    public function nodeCantHaveUnknownParent()
    {
        $this->expectException(LTreeUndefinedNodeException::class);
        $this->getCategoriesWithUnknownParent()
            ->toTree();
    }

    /**
     * @test
     */
    public function nodeCantBeParentToItself()
    {
        $this->expectException(LTreeReflectionException::class);
        $this->getCategoriesWithSelfParent()
            ->toTree();
    }

    /**
     * @test
     */
    public function findSuccess(): void
    {
        $tree = $this->getCategories()
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

    public function provideUnknownNodes(): Generator
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

    /**
     * @test
     * @dataProvider provideUnknownNodes
     */
    public function findFail($node): void
    {
        $tree = $this->getCategories()
            ->toTree();
        $this->assertNull($tree->findInTree($node));
    }

    /**
     * @test
     */
    public function countDescendants(): void
    {
        $tree = $this->getCategories()
            ->toTree();
        $this->assertSame(12, $tree->countDescendants());
        $this->assertSame(9, $tree->findInTree(1)->countDescendants());
        $this->assertSame(1, $tree->findInTree(2)->countDescendants());
        $this->assertSame(5, $tree->findInTree(3)->countDescendants());
        $this->assertSame(3, $tree->findInTree(6)->countDescendants());
        $this->assertSame(1, $tree->findInTree(11)->countDescendants());
    }

    /**
     * @test
     */
    public function each()
    {
        $tree = $this->getCategories()
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

    /**
     * @test
     */
    public function toTreeOnEmptyCollection(): void
    {
        $collection = new LTreeCollection();
        $this->assertInstanceOf(LTreeNode::class, $collection->toTree());
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

    /**
     * @test
     */
    public function resources(): void
    {
        $resource = new CategoryStubResourceCollection(
            CategoryStub::query()->whereKey([7, 12])->get(),
            [
                'id' => 'desc',
            ]
        );
        $this->assertSame($resource->toArray(new Request()), [
            [
                'id' => 11,
                'path' => '11',
                'children' => [
                    [
                        'id' => 12,
                        'path' => '11.12',
                        'children' => [],
                    ],
                ],
            ],
            [
                'id' => 1,
                'path' => '1',
                'children' => [
                    [
                        'id' => 3,
                        'path' => '1.3',
                        'children' => [
                            [
                                'id' => 7,
                                'path' => '1.3.7',
                                'children' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function toCollection(): void
    {
        $tree = $this->getCategories()
            ->toTree();

        $this->assertSame('1', $tree->findInTree(1)->pathAsString());

        $collection = $tree->toCollection();
        $this->assertCount(12, $collection);
        for ($id = 1; $id <= 12; $id++) {
            $collection->find($id);
        }
    }

    /**
     * @test
     */
    public function toTreeArray(): void
    {
        $formatter = static function ($item) {
            return [
                'my_id' => $item->id,
                'custom' => $item->id * 10,
            ];
        };
        $tree = $this->getCategories()
            ->toTree();
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

    /**
     * @test
     */
    public function nodePresenter()
    {
        $tree = $this->getCategories()
            ->toTree();
        $node = $tree->findInTree(1);
        // node method
        $this->assertTrue(method_exists($node, 'getChildren'));
        $this->assertNotNull($node->getChildren());
        // model method
        $this->assertFalse(method_exists($node, 'getTable'));
        $this->assertNotNull($node->model->getTable());
    }

    /**
     * @test
     */
    public function sortFail(): void
    {
        $tree = $this->getRandomCategories()
            ->toTree();
        $this->expectException(InvalidArgumentException::class);
        $tree->sortTree(['name']);
    }

    /**
     * @test
     */
    public function sort()
    {
        $tree = $this->getRandomCategories()
            ->whereIn('id', [1, 2, 3, 4])->toTree();
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

    private function getCategoriesWithSelfParent(): LTreeCollection
    {
        $this->createCategory([
            'id' => 13,
            'parent_id' => 13,
            'path' => '13.13',
            'name' => 'Self parent',
        ]);
        return $this->getCategories();
    }

    private function getCategoriesWithUnknownParent(): LTreeCollection
    {
        CategoryStub::query()->find(11)->delete();
        return $this->getCategories();
    }

    private function getCategories(): LTreeCollection
    {
        return CategoryStub::query()->orderBy('name')->get();
    }

    private function getRandomCategories(): LTreeCollection
    {
        return CategoryStub::query()->inRandomOrder()->get();
    }
}
