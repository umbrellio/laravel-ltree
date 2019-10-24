<?php

declare(strict_types=1);

namespace Umbrellio\LTree\Tests\Functional;

use Generator;
use Illuminate\Database\Eloquent\Collection as CollectionBase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Umbrellio\LTree\Helpers\LTreeHelper;
use Umbrellio\LTree\Interfaces\LTreeModelInterface;
use Umbrellio\LTree\Interfaces\LTreeServiceInterface;
use Umbrellio\LTree\Services\LTreeService;
use Umbrellio\LTree\Tests\FunctionalTestCase;
use Umbrellio\LTree\Traits\LTreeModelTrait;

class LtreeTest extends FunctionalTestCase
{
    use RefreshDatabase;

    /** @var LTreeService */
    private $ltreeService;

    /** @var LTreeModelInterface|LTreeModelTrait|Model */
    private $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->initLTreeService();
    }

    /** @test */
    public function createRoot(): void
    {
        $node = $this->createTreeNode($this->getNode());
        $this->assertNull($node->getLtreeParentId());
        $this->assertTrue($node::descendantsOf($node)->exists());
    }

    /** @test */
    public function createViaServiceRoot(): void
    {
        $node = $this->createTreeNode($this->getNodeWithoutPath());
        $this->assertSame([], $node->getLtreePath());
        $this->ltreeService->createPath($node);
        $this->assertSame('1', $node->getLtreePath(LTreeModelInterface::AS_STRING));
    }

    /** @test */
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

    /** @test */
    public function childrens(): void
    {
        $parent = $this->createTreeNode($this->getTreeNodes()[1]);
        $this->assertNull($parent->getLtreeParentId());
        $this->assertTrue($parent::descendantsOf($parent)->exists());
        $this->assertSame(0, $parent::descendantsOf($parent)->withoutSelf(1)->count());
        $child = $this->createTreeNode($this->getTreeNodes()[2]);
        $child->refresh();
        $parent->refresh();
        $this->assertSame(1, $parent->ltreeChildrens->count());
        $this->assertSame($child->id, $parent->ltreeChildrens->first()->id);
    }

    /** @test */
    public function renderLtree(): void
    {
        $parent = $this->createTreeNode($this->getTreeNodes()[1]);
        $this->assertNull($parent->getLtreeParentId());
        $this->assertTrue($parent::descendantsOf($parent)->exists());
        $this->assertSame(0, $parent::descendantsOf($parent)->withoutSelf(1)->count());
        /** @var LTreeModelTrait $child */
        $child = $this->createTreeNode($this->getTreeNodes()[2]);
        $this->assertSame('... 1.2', $child->renderAsLtree($child->getLtreePath(LTreeModelInterface::AS_STRING)));
        $this->assertSame($child->getLtreeParentId(), $parent->id);
    }

    /** @test */
    public function moveSubtrees(): void
    {
        $nodes = $this->createTreeNodes($this->getTreeNodes());
        $parentColumn = $nodes[1]->getLtreeParentColumn();
        $this->assertSame(1, $nodes[1]::descendantsOf($nodes[11])->withoutSelf(11)->count());
        $nodes[1]->update([$parentColumn => 11]);
        $this->ltreeService->updatePath($nodes[1]);
        $this->assertSame(11, $nodes[1]::descendantsOf($nodes[11])->withoutSelf(11)->count());
        $this->assertSame(11, $nodes[1]->getLtreeParentId());
    }

    /** @test */
    public function deleteRoot(): void
    {
        $node = $this->createTreeNode($this->getNode());
        $this->assertTrue($node::descendantsOf($node)->exists());
        $node::descendantsOf($node)->delete();
        $this->assertFalse($node::descendantsOf($node)->exists());
    }

    /** @test */
    public function deleteSubtree(): void
    {
        $nodes = $this->createTreeNodes($this->getTreeNodes());
        $this->assertSame(9, $nodes[1]::descendantsOf($nodes[1])->withoutSelf(1)->count());
        $nodes[1]::descendantsOf($nodes[1])->withoutSelf(1)->delete();
        $this->assertFalse($nodes[1]::descendantsOf($nodes[1])->withoutSelf(1)->exists());
        $this->assertSame(1, $nodes[1]::descendantsOf($nodes[1])->count());
    }

    /** @test */
    public function deleteViaServiceSubtree(): void
    {
        $nodes = $this->createTreeNodes($this->getTreeNodes());
        $this->assertSame(9, $nodes[1]::descendantsOf($nodes[1])->withoutSelf(1)->count());
        $nodes[1]->update(['is_deleted' => 1]);
        $nodes[1]->delete();
        $nodes[1]->refresh();
        $this->ltreeService->dropDescendants($nodes[1]);
        $this->assertFalse($nodes[1]::whereKey($nodes[1]->id)->exists());
    }

    /** @test */
    public function ancestors(): void
    {
        $nodes = $this->createTreeNodes($this->getTreeNodes());
        $this->assertSame(3, $nodes[1]::ancestorsOf($nodes[6])->get()->count());
        $this->assertTrue($nodes[2]->isParentOf(5));

        $collection = new CollectionBase();
        $collection->put(5, $nodes[5]);
        $collection->put(12, $nodes[12]);
        $filterNodes = LTreeHelper::getAncestors($collection);
        $this->assertSame(5, $filterNodes->count());
        $this->assertSame([1, 2, 5, 11, 12], $filterNodes->map(function (LTreeModelInterface $model) {
            return $model->id;
        })->toArray());
    }

    /** @test */
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
            'expected1' => 1,
            'expected2' => 3,
            'expected3' => null,
        ];
        yield 'three_levels' => [
            'category_id' => 6,
            'expected1' => 1,
            'expected2' => 3,
            'expected3' => 6,
        ];
    }

    /**
     * @test
     * @dataProvider provideBelongsTree
     */
    public function getBelongsToTree($id, $level1, $level2, $level3)
    {
        $tree = $this->createTreeNodes($this->getTreeNodes());
        $product = $this->getProduct();

        $product->category()->associate($tree[$id]); // 1.3
        $product->save();

        $find = ProductStub::first();
        $this->assertFalse(array_key_exists('categoryTree', $find->toArray()));
        $this->assertSame($level1, optional($find->categoryTree->get(0))->id);
        $this->assertSame($level2, optional($find->categoryTree->get(1))->id);
        $this->assertSame($level3, optional($find->categoryTree->get(2))->id);

        $find = ProductStub::with('categoryTree')->first();
        $this->assertSame(2, $find->categoryTree->count());
        $this->assertSame($level1, optional($find->categoryTree->get(0))->id);
        $this->assertSame($level2, optional($find->categoryTree->get(1))->id);
        $this->assertSame($level3, optional($find->categoryTree->get(2))->id);
    }

    /** @test */
    public function getAncestorByLevel(): void
    {
        $tree = $this->createTreeNodes($this->getTreeNodes());
        $parent = $tree[1];
        $descendants = $parent::descendantsOf($parent)->withoutSelf(1);
        $this->assertGreaterThan(0, $descendants->count());
        $descendants->each(static function ($descendant) use ($parent) {
            $descendant->getAncestorByLevel($parent->id);
        });

        $this->assertSame($tree[5]->getAncestorByLevel(2)->id, $tree[2]->id);
        $this->assertSame($tree[8]->getAncestorByLevel(3)->id, $tree[6]->id);
    }

    private function initLTreeService()
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS LTREE');
        Schema::create('categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('parent_id')->nullable();
            $table->ltree('path')->nullable();
            $table->index('parent_id');
            $table->timestamps(6);
            $table->softDeletes();
            $table->tinyInteger('is_deleted')->unsigned()->default(1);
            $table->unique('path');
        });
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('category_id')->nullable();
            $table->timestamps(6);

            $table->foreign('category_id')->on('categories')->references('id');
        });
        DB::statement("COMMENT ON COLUMN categories.path IS '(DC2Type:ltree)'");
        $this->ltreeService = app()->make(LTreeServiceInterface::class);
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

    /**
     * @return LTreeModelInterface|Model|LTreeModelTrait
     */
    private function createLTreeNode(string $scenario, int $id, ?string $path = null, ?int $parent_id = null)
    {
        /** @var LTreeModelInterface $model */
        $model = $this->getModel();
        return $this->ltreeFactory($scenario, [
            'id' => $id,
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
