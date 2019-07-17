<?php

declare(strict_types=1);

namespace Umbrellio\LTree;

use Illuminate\Support\ServiceProvider;
use Umbrellio\LTree\Interfaces\LTreeServiceInterface;
use Umbrellio\LTree\Services\LTreeService;
use Umbrellio\Postgres\PostgresConnection;

class LTreeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        PostgresConnection::registerExtension(LTreeExtension::class);
        $this->app->bind(LTreeServiceInterface::class, LTreeService::class);
    }
}
