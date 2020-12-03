<?php

declare(strict_types=1);

namespace Umbrellio\LTree\tests\_data\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Umbrellio\LTree\Interfaces\LTreeModelInterface;
use Umbrellio\LTree\Interfaces\LTreeServiceInterface;
use Umbrellio\LTree\tests\_data\Models\CategoryStub;
use Umbrellio\LTree\tests\FunctionalTestCase;
use Umbrellio\Postgres\Schema\Blueprint;

/**
 * @mixin FunctionalTestCase
 */
trait HasLTreeTables
{
    private function initLTreeService(): void
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

    private function initLTreeCategories(): void
    {
        $this->createTreeNodes(CategoryStub::class, $this->getTreeNodes());
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

    private function createTreeNodes(string $class, array $items): void
    {
        foreach ($items as $data) {
            $this->createLTreeNode($class, $data[0], $data[1], $data[2]);
        }
    }

    private function createLTreeNode(string $class, int $id, ?string $path, ?int $parent_id): void
    {
        $model = $this->getModel($class);

        $this->ltreeFactory($class, [
            $model->getKeyName() => $id,
            $model->getLtreePathColumn() => $path,
            $model->getLtreeParentColumn() => $parent_id,
        ]);
    }

    private function getModel(string $class, array $data = []): LTreeModelInterface
    {
        return new $class($data);
    }

    private function ltreeFactory(string $class, array $data = []): void
    {
        $model = $this->getModel($class, $data);
        $model->save();
    }
}
