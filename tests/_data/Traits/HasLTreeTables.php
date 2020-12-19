<?php

declare(strict_types=1);

namespace Umbrellio\LTree\tests\_data\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Umbrellio\LTree\Collections\LTreeCollection;
use Umbrellio\LTree\Interfaces\LTreeModelInterface;
use Umbrellio\LTree\Interfaces\LTreeServiceInterface;
use Umbrellio\LTree\tests\_data\Models\CategoryStub;
use Umbrellio\LTree\tests\_data\Models\ProductStub;
use Umbrellio\LTree\tests\FunctionalTestCase;
use Umbrellio\Postgres\Schema\Blueprint;

/**
 * @mixin FunctionalTestCase
 * @property LTreeServiceInterface $ltreeService
 */
trait HasLTreeTables
{
    protected $ltreeService;

    protected function getCategoriesWithSelfParent(): LTreeCollection
    {
        $this->createCategory([
            'id' => 13,
            'parent_id' => 13,
            'path' => '13.13',
            'name' => 'Self parent',
        ]);
        return $this->getCategories();
    }

    protected function getCategoriesWithUnknownParent(): LTreeCollection
    {
        CategoryStub::query()->find(11)->delete();
        return $this->getCategories();
    }

    protected function getCategories(): LTreeCollection
    {
        return CategoryStub::query()->orderBy('name')->get();
    }

    protected function getRandomCategories(): LTreeCollection
    {
        return CategoryStub::query()->inRandomOrder()->get();
    }

    protected function createCategory(array $attributes): LTreeModelInterface
    {
        $model = new CategoryStub();
        $model->fill($attributes);
        $model->save();

        return $model;
    }

    protected function createProduct(array $attributes): LTreeModelInterface
    {
        $model = new ProductStub();
        $model->fill($attributes);
        $model->save();

        return $model;
    }

    protected function getRoot(): CategoryStub
    {
        return $this->getCategories()
            ->find(1);
    }

    protected function findNodeByPath(string $path): CategoryStub
    {
        return $this
            ->getCategories()
            ->where('path', $path)
            ->first();
    }

    private function initLTreeService(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS LTREE');
        Schema::create('categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('parent_id')
                ->nullable();
            $table->ltree('path')
                ->nullable();
            $table->index('parent_id');
            $table->timestamps(6);
            $table->string('name')
                ->nullable();
            $table->softDeletes();
            $table->tinyInteger('is_deleted')
                ->unsigned()
                ->default(1);
            $table->unique('path');
        });
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('category_id')
                ->nullable();
            $table->timestamps(6);

            $table->foreign('category_id')
                ->on('categories')
                ->references('id');
        });
        DB::statement("COMMENT ON COLUMN categories.path IS '(DC2Type:ltree)'");
        $this->ltreeService = app()
            ->make(LTreeServiceInterface::class);
    }

    private function initLTreeCategories(): void
    {
        foreach ($this->getTreeNodes() as $data) {
            $this->createCategory([
                'id' => $data[0],
                'path' => $data[1],
                'parent_id' => $data[2],
                'name' => $data[3],
            ]);
        }
    }

    private function getTreeNodes(): array
    {
        return [
            1 => [1, '1', null, 'Russia'],
            2 => [2, '1.2', 1, 'Saint-Petersburg'],
            5 => [5, '1.2.5', 2, 'Gatchina'],
            3 => [3, '1.3', 1, 'Moscow'],
            6 => [6, '1.3.6', 3, 'Kazan'],
            8 => [8, '1.3.6.8', 6, 'Tver'],
            9 => [9, '1.3.6.9', 6, 'Romanovo'],
            10 => [10, '1.3.6.10', 6, 'Sheremetevo'],
            7 => [7, '1.3.7', 3, 'Rublevka'],
            4 => [4, '1.4', 1, 'Omsk'],
            11 => [11, '11', null, 'Britain'],
            12 => [12, '11.12', 11, 'London'],
        ];
    }
}
