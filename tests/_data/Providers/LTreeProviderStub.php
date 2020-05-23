<?php

declare(strict_types=1);

namespace Umbrellio\LTree\tests\_data\Providers;

use Illuminate\Database\Schema\Grammars\PostgresGrammar;
use Illuminate\Support\ServiceProvider;
use Umbrellio\LTree\Interfaces\LTreeServiceInterface;
use Umbrellio\LTree\Services\LTreeService;
use Umbrellio\LTree\Types\LTreeType;

class LTreeProviderStub extends ServiceProvider
{
    public function register()
    {
        PostgresGrammar::macro('typeLtree', function (): string {
            return LTreeType::TYPE_NAME;
        });
        $this->app->bind(LTreeServiceInterface::class, LTreeService::class);
    }
}
