<?php

declare(strict_types=1);

namespace Umbrellio\LTree;

use Umbrellio\LTree\Schema\Grammars\LTreeSchemaGrammar;
use Umbrellio\LTree\Schema\LTreeBlueprint;
use Umbrellio\LTree\Types\LTreeType;
use Umbrellio\Postgres\Extensions\AbstractExtension;
use Umbrellio\Postgres\Schema\Blueprint;
use Umbrellio\Postgres\Schema\Grammars\PostgresGrammar;

class LTreeExtension extends AbstractExtension
{
    public const NAME = LTreeType::TYPE_NAME;

    public static function getMixins(): array
    {
        return [
            LTreeBlueprint::class => Blueprint::class,
            LTreeSchemaGrammar::class => PostgresGrammar::class,
        ];
    }

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
