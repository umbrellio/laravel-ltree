<?php

declare(strict_types=1);

namespace Umbrellio\LTree;

use Umbrellio\LTree\Connections\LTreeConnection;
use Umbrellio\LTree\Schema\Grammars\LTreeSchemaGrammar;
use Umbrellio\LTree\Schema\LTreeBlueprint;
use Umbrellio\LTree\Types\LTreeType;
use Umbrellio\Postgres\PostgresConnection;
use Umbrellio\Postgres\Schema\Blueprint;
use Umbrellio\Postgres\Schema\Extensions\AbstractExtension;
use Umbrellio\Postgres\Schema\Grammars\PostgresGrammar;

class LTreeExtension extends AbstractExtension
{
    public const NAME = 'ltree';

    protected static $mixins = [
        Blueprint::class => LTreeBlueprint::class,
        PostgresConnection::class => LTreeConnection::class,
        PostgresGrammar::class => LTreeSchemaGrammar::class,
    ];

    public static function getName(): string
    {
        return static::NAME;
    }

    public static function getTypes(): array
    {
        return [
            static::NAME => LTreeType::class,
        ];
    }
}
