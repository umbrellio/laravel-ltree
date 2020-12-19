<?php

declare(strict_types=1);

namespace Umbrellio\LTree\tests;

use Umbrellio\LTree\tests\_data\Traits\HasLTreeTables;

class LTreeBaseTestCase extends FunctionalTestCase
{
    use HasLTreeTables;

    protected function setUp(): void
    {
        parent::setUp();

        $this->initLTreeService();
        $this->initLTreeCategories();
    }
}
