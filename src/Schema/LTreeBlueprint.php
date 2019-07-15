<?php

declare(strict_types=1);

namespace Umbrellio\LTree\Schema;

use Illuminate\Support\Fluent;
use Umbrellio\LTree\Types\LTreeType;
use Umbrellio\Postgres\Extensions\Schema\AbstractBlueprint;

class LTreeBlueprint extends AbstractBlueprint
{
    public function ltree()
    {
        return function (string $column): Fluent {
            return $this->addColumn(LTreeType::TYPE_NAME, $column);
        };
    }
}
