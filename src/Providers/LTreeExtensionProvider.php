<?php

declare(strict_types=1);

namespace Umbrellio\LTree\Providers;

use Illuminate\Support\ServiceProvider;
use Umbrellio\LTree\LTreeExtension;
use Umbrellio\Postgres\PostgresConnection;

class LTreeExtensionProvider extends ServiceProvider
{
    public function register(): void
    {
        PostgresConnection::registerExtension(LTreeExtension::class);
    }
}
