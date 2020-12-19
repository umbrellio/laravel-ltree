<?php

declare(strict_types=1);

namespace Umbrellio\LTree\tests\functional;

use Generator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Umbrellio\LTree\Exceptions\InvalidTraitInjectionClass;
use Umbrellio\LTree\Interfaces\LTreeModelInterface;
use Umbrellio\LTree\Services\LTreeService;
use Umbrellio\LTree\tests\_data\Models\CategorySomeStub;
use Umbrellio\LTree\tests\_data\Models\CategoryStub;
use Umbrellio\LTree\tests\_data\Models\ProductStub;
use Umbrellio\LTree\tests\_data\Traits\HasLTreeTables;
use Umbrellio\LTree\tests\FunctionalTestCase;
use Umbrellio\LTree\Traits\LTreeModelTrait;

class LTreeTest extends FunctionalTestCase
{
    use HasLTreeTables;

    /**
     * @var LTreeService
     */
    private $ltreeService;

    /**
     * @var LTreeModelInterface|LTreeModelTrait|Model
     */
    private $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->initLTreeService();
    }

    /**
     * @test
     */
    public function createRoot(): void
    {
        $node = $this->createTreeNode($this->getNode());
        $this->assertNull($node->getLtreeParentId());
        $this->assertTrue($node::descendantsOf($node)->exists());
    }

    /**
     * @test
     */
    public function createViaServiceRoot(): void
    {
        $node = $this->createTreeNode($this->getNodeWithoutPath());
        $this->assertSame([], $node->getLtreePath());
        $this->ltreeService->createPath($node);
        $this->assertSame('1', $node->getLtreePath(LTreeModelInterface::AS_STRING));
    }

    /**
     * @test
     */
    public function createChild(): void
    {
        $parent = $this->createTreeNode($this->getTreeNodes()[1]);
        $this->assertNull($parent->getLtreeParentId());
        $this->assertTrue($parent::descendantsOf($parent)->exists());
        $this->assertSame(0, $parent::descendantsOf($parent)->withoutSelf(1)->count());
        $child = $this->createTreeNode($this->getTreeNodes()[2]);
        $this->assertSame($parent->getKey(), $child->getLtreeParentId());
        $this->assertSame(1, $child::descendantsOf($parent)->withoutSelf(1)->count());
    }

    /**
     * @test
     */
    public function children(): void
    {
        $parent = $this->createTreeNode($this->getTreeNodes()[1]);
        $this->assertNull($parent->getLtreeParentId());
        $this->assertTrue($parent::descendantsOf($parent)->exists());
        $this->assertSame(0, $parent::descendantsOf($parent)->withoutSelf(1)->count());
        $child = $this->createTreeNode($this->getTreeNodes()[2]);
        $child->refresh();
        $parent->refresh();
        $this->assertSame(1, $parent->ltreeChildren->count());
        $this->assertSame($child->getKey(), $parent->ltreeChildren->first()->getKey());
    }

    /**
     * @test
     */
    public function renderLtree(): void
    {
        $parent = $this->createTreeNode($this->getTreeNodes()[1]);
        $this->assertNull($parent->getLtreeParentId());
        $this->assertTrue($parent::descendantsOf($parent)->exists());
        $this->assertSame(0, $parent::descendantsOf($parent)->withoutSelf(1)->count());
        /** @var LTreeModelTrait $child */
        $child = $this->createTreeNode($this->getTreeNodes()[2]);
        $this->assertSame($child->getLtreeParentId(), $parent->getKey());
    }

    /**
     * @test
     */
    public function moveSubtrees(): void
    {
        $nodes = $this->createTreeNodes($this->getTreeNodes());
        $parentColumn = $nodes[1]->getLtreeParentColumn();
        $this->assertSame(1, $nodes[1]::descendantsOf($nodes[11])->withoutSelf(11)->count());
        $nodes[1]->update([
            $parentColumn => 11,
        ]);
        $this->ltreeService->updatePath($nodes[1]);
        $this->assertSame(11, $nodes[1]::descendantsOf($nodes[11])->withoutSelf(11)->count());
        $this->assertSame(11, $nodes[1]->getLtreeParentId());
    }

    /**
     * @test
     */
    public function deleteRoot(): void
    {
        $node = $this->createTreeNode($this->getNode());
        $this->assertTrue($node::descendantsOf($node)->exists());
        $node::descendantsOf($node)->delete();
        $this->assertFalse($node::descendantsOf($node)->exists());
    }

    /**
     * @test
     */
    public function deleteSubtree(): void
    {
        $nodes = $this->createTreeNodes($this->getTreeNodes());
        $this->assertSame(9, $nodes[1]::descendantsOf($nodes[1])->withoutSelf(1)->count());
        $nodes[1]::descendantsOf($nodes[1])->withoutSelf(1)->delete();
        $this->assertFalse($nodes[1]::descendantsOf($nodes[1])->withoutSelf(1)->exists());
        $this->assertSame(1, $nodes[1]::descendantsOf($nodes[1])->count());
    }

    /**
     * @test
     */
    public function deleteViaServiceSubtree(): void
    {
        $nodes = $this->createTreeNodes($this->getTreeNodes());
        $this->assertSame(9, $nodes[1]::descendantsOf($nodes[1])->withoutSelf(1)->count());
        $nodes[1]->update([
            'is_deleted' => 1,
        ]);
        $nodes[1]->delete();
        $nodes[1]->refresh();
        $this->ltreeService->dropDescendants($nodes[1]);
        $this->assertFalse($nodes[1]::whereKey($nodes[1]->getKey())->exists());
    }

    /**
     * @test
     */
    public function ancestors(): void
    {
        $nodes = $this->createTreeNodes($this->getTreeNodes());
        $this->assertSame(3, $nodes[1]::ancestorsOf($nodes[6])->get()->count());
        $this->assertTrue($nodes[2]->isParentOf(5));
    }

    public function providePaths(): Generator
    {
        yield 'single_as_array' => [
            'paths' => ['11.12'],
            'expected' => 2,
        ];
        yield 'all_as_array' => [
            'paths' => ['11.12', '1.2.5'],
            'expected' => 5,
        ];
    }

    /**
     * @test
     * @dataProvider providePaths
     */
    public function parentsOf(array $paths, int $expectedCount): void
    {
        $this->createTreeNodes($this->getTreeNodes());
        $this->assertCount($expectedCount, CategoryStub::parentsOf($paths)->get());
    }

    /**
     * @test
     */
    public function root(): void
    {
        $nodes = $this->createTreeNodes($this->getTreeNodes());
        $roots = $nodes[1]::root()->get();
        foreach ($roots as $root) {
            $this->assertNull($root->parent_id);
        }
    }

    public function provideBelongsTree(): Generator
    {
        yield 'two_levels' => [
            'category_id' => 3,
            'count' => 2,
            'expected1' => 1,
            'expected2' => 3,
            'expected3' => null,
        ];
        yield 'three_levels' => [
            'category_id' => 6,
            'count' => 3,
            'expected1' => 1,
            'expected2' => 3,
            'expected3' => 6,
        ];
    }

    /**
     * @test
     * @dataProvider provideBelongsTree
     */
    public function getBelongsToTree($id, $count, $level1, $level2, $level3)
    {
        $tree = $this->createTreeNodes($this->getTreeNodes());
        $product = $this->getProduct();

        // 1.3
        $product->category()
            ->associate($tree[$id]);
        $product->save();

        $find = ProductStub::first();
        $this->assertFalse(array_key_exists('category_tree', $find->toArray()));
        $this->assertSame($level1, optional($find->categoryTree->get(0))->getKey());
        $this->assertSame($level2, optional($find->categoryTree->get(1))->getKey());
        $this->assertSame($level3, optional($find->categoryTree->get(2))->getKey());

        $find = ProductStub::with('categoryTree')->first();
        $this->assertTrue(array_key_exists('category_tree', $find->toArray()));
        $this->assertSame($count, $find->categoryTree->count());
        $this->assertSame($level1, optional($find->categoryTree->get(0))->getKey());
        $this->assertSame($level2, optional($find->categoryTree->get(1))->getKey());
        $this->assertSame($level3, optional($find->categoryTree->get(2))->getKey());
    }

    /**
     * @test
     */
    public function missingLtreeModel(): void
    {
        $rootSome = $this->getCategorySome();
        $rootSome->save();

        $childSome = $this->getCategorySome([
            'parent_id' => $rootSome->getKey(),
        ]);
        $childSome->save();

        $this->expectException(InvalidTraitInjectionClass::class);
        $childSome->parentTree();
    }

    /**
     * @test
     */
    public function getAncestorByLevel(): void
    {
        $tree = $this->createTreeNodes($this->getTreeNodes());
        $parent = $tree[1];
        $descendants = $parent::descendantsOf($parent)->withoutSelf(1);
        $this->assertGreaterThan(0, $descendants->count());
        $descendants->each(static function ($descendant) use ($parent) {
            $descendant->getAncestorByLevel($parent->getKey());
        });

        $this->assertSame($tree[5]->getAncestorByLevel(2)->getKey(), $tree[2]->getKey());
        $this->assertSame($tree[8]->getAncestorByLevel(3)->getKey(), $tree[6]->getKey());
    }

    public function provideLevels(): Generator
    {
        yield 'root' => [
            'data' => [
                'path' => '1',
            ],
            'level' => 1,
        ];
        yield 'second-level' => [
            'data' => [
                'path' => '1.2',
            ],
            'level' => 2,
        ];
        yield 'third-level' => [
            'data' => [
                'path' => '1.2.3',
            ],
            'level' => 3,
        ];
    }

    /**
     * @test
     * @dataProvider provideLevels
     */
    public function getLtreeLevel(array $data, int $level): void
    {
        $this->assertSame($level, $this->getModel($data)->getLtreeLevel());
    }

    /**
     * @return LTreeModelInterface|Model
     */
    private function createTreeNode(array $data, $scenario = 'create')
    {
        return $this->createLTreeNode($scenario, $data[0], $data[1], $data[2]);
    }

    private function getNode(): array
    {
        return [1, '1', null];
    }

    private function getNodeWithoutPath(): array
    {
        return [1, null, null];
    }

    private function getTreeNodes(): array
    {
        return [
            1 => [1, '1', null],
            2 => [2, '1.2', 1],
            5 => [5, '1.2.5', 2],
            3 => [3, '1.3', 1],
            6 => [6, '1.3.6', 3],
            8 => [8, '1.3.6.8', 6],
            9 => [9, '1.3.6.9', 6],
            10 => [10, '1.3.6.10', 6],
            7 => [7, '1.3.7', 3],
            4 => [4, '1.4', 1],
            11 => [11, '11', null],
            12 => [12, '11.12', 11],
        ];
    }

    /**
     * @return LTreeModelInterface[]|Model[]|LTreeModelTrait[]
     */
    private function createTreeNodes(array $items, $scenario = 'create'): array
    {
        $nodes = [];
        foreach ($items as $data) {
            $nodes[$data[0]] = $this->createLTreeNode($scenario, $data[0], $data[1], $data[2]);
        }
        return $nodes;
    }

    private function getProduct(array $data = []): ProductStub
    {
        return new ProductStub($data);
    }

    private function getCategorySome(array $data = []): CategorySomeStub
    {
        return new CategorySomeStub($data);
    }

    /**
     * @return LTreeModelInterface|Model|LTreeModelTrait
     */
    private function createLTreeNode(string $scenario, int $id, ?string $path = null, ?int $parent_id = null)
    {
        /** @var LTreeModelInterface $model */
        $model = $this->getModel();
        return $this->ltreeFactory($scenario, [
            $model->getKeyName() => $id,
            $model->getLtreePathColumn() => $path,
            $model->getLtreeParentColumn() => $parent_id,
        ]);
    }

    private function getModel(array $data = []): CategoryStub
    {
        return new CategoryStub($data);
    }

    /**
     * @return LTreeModelInterface|LTreeModelInterface[]|Collection|Model[]|Model
     */
    private function ltreeFactory(string $scenario, array $data = [])
    {
        /** @var Model $model */
        $model = $this->getModel($data);
        if ($scenario === 'make') {
            return $model;
        }
        $model->save();
        return $model;
    }
}
