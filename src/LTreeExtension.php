<?php

declare(strict_types=1);

namespace Umbrellio\LTree;

use Umbrellio\LTree\Connections\LTreeConnection;
use Umbrellio\LTree\Schema\Grammars\LTreeSchemaGrammar;
use Umbrellio\LTree\Schema\LTreeBlueprint;
use Umbrellio\Postgres\PostgresConnection;
use Umbrellio\Postgres\Schema\Blueprint;
use Umbrellio\Postgres\Schema\Grammars\PostgresGrammar;

class LTreeExtension
{
    public const NAME = 'ltree';

    protected static $mixins = [
        Blueprint::class => LTreeBlueprint::class,
        PostgresConnection::class => LTreeConnection::class,
        PostgresGrammar::class => LTreeSchemaGrammar::class,
    ];

    public static function register(): void
    {
        collect(static::$mixins)->each(static function ($mixin, $extension) {
            $extension::mixin(new $mixin());
        });
    }
}
