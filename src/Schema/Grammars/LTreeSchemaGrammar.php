<?php

declare(strict_types=1);

namespace Umbrellio\LTree\Schema\Grammars;

use Umbrellio\LTree\LTreeExtension;
use Umbrellio\Postgres\Schema\Extensions\AbstractGrammarObject;

class LTreeSchemaGrammar extends AbstractGrammarObject
{
    protected function typeLtree()
    {
        return function (): string {
            return LTreeExtension::NAME;
        };
    }
}
