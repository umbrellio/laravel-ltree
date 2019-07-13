<?php

namespace Umbrellio\LTree;

use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Macroable;
use ReflectionClass;
use ReflectionMethod;
use Umbrellio\LTree\Collections\LTreeCollection;
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
        Collection::class => LTreeCollection::class,
    ];

    public static function register(): void
    {
        collect(static::$mixins)->each(static function ($mixin, $extension) {
            /** @var Macroable $extension */
            $extension::mixin(new $mixin());
        });
    }
}
