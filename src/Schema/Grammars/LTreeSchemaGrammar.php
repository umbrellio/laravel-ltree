<?php

declare(strict_types=1);

namespace Umbrellio\LTree\Schema\Grammars;

use Umbrellio\LTree\Types\LTreeType;
use Umbrellio\Postgres\Extensions\Schema\Grammar\AbstractGrammar;

class LTreeSchemaGrammar extends AbstractGrammar
{
    protected function typeLtree()
    {
        return function (): string {
            return LTreeType::TYPE_NAME;
        };
    }
}
