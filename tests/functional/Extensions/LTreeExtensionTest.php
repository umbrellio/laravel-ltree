<?php

declare(strict_types=1);

namespace Umbrellio\LTree\tests\functional\Extensions;

use Umbrellio\LTree\tests\FunctionalTestCase;
use Umbrellio\Postgres\Helpers\ColumnAssertions;

class LTreeExtensionTest extends FunctionalTestCase
{
    use ColumnAssertions;

    /**
     * @test
     */
    public function schemaLtreeType(): void
    {
        $this->markTestIncomplete();
    }
}
