<?php

declare(strict_types=1);

namespace Umbrellio\LTree\Providers;

use Illuminate\Database\Schema\Grammars\PostgresGrammar;
use Illuminate\Support\ServiceProvider;
use Umbrellio\LTree\Types\LTreeType;

class LTreeGrammarProvider extends ServiceProvider
{
    public function register()
    {
        PostgresGrammar::macro('typeLtree', function (): string {
            return LTreeType::TYPE_NAME;
        });
    }
}
