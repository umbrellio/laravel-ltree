<?php

declare(strict_types=1);

namespace Umbrellio\LTree\tests\functional\Collections;

use Generator;
use Umbrellio\LTree\tests\_data\Traits\HasLTreeTables;
use Umbrellio\LTree\tests\FunctionalTestCase;

class LTreeCollectionTest extends FunctionalTestCase
{
    use HasLTreeTables;

    protected function setUp(): void
    {
        parent::setUp();
        $this->initLTreeService();
        $this->initLTreeCategories();
    }

    public function provideItems(): Generator
    {
        yield 'only_leaves' => [
            'ids' => [5, 12],
            'expectedIds' => [1, 2, 5, 11, 12],
        ];
    }

    /**
     * @test
     * @dataProvider provideItems
     */
    public function lTreeResource(array $ids, array $expected): void
    {
        $this->markTestIncomplete();
    }
}
