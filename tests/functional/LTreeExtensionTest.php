<?php

declare(strict_types=1);

namespace Umbrellio\LTree\tests\functional;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Umbrellio\LTree\tests\FunctionalTestCase;
use Umbrellio\Postgres\Helpers\ColumnAssertions;

class LTreeExtensionTest extends FunctionalTestCase
{
    use RefreshDatabase;
    use ColumnAssertions;

    /**
     * @test
     */
    public function schemaLtreeType(): void
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
        DB::statement("COMMENT ON COLUMN categories.path IS '(DC2Type:ltree)'");

        $this->assertLaravelTypeColumn('categories', 'path', 'ltree');
    }
}
