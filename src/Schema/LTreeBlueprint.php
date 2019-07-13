<?php

namespace Umbrellio\LTree\Schema;

use Illuminate\Support\Fluent;
use Umbrellio\LTree\LTreeExtension;
use Umbrellio\Postgres\Schema\Extensions\AbstractBlueprintObject;

class LTreeBlueprint extends AbstractBlueprintObject
{
    public function ltree()
    {
        return function (string $column): Fluent {
            return $this->addColumn(LTreeExtension::NAME, $column);
        };
    }
}
