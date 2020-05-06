<?php

declare(strict_types=1);

namespace Umbrellio\LTree\Helpers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Umbrellio\LTree\Interfaces\LTreeModelInterface;
use Umbrellio\LTree\Types\LTreeType;

class LTreeHelper
{
    public const PAD_STRING = '... ';
    public const PAD_TYPE = STR_PAD_LEFT;

    public static function renderAsLTree($value, $level = 1, $pad_string = self::PAD_STRING, $pad_type = self::PAD_TYPE)
    {
        return str_pad(
            $value,
            strlen($value) + strlen($pad_string) * (($level < 1 ? 1 : $level) - 1),
            $pad_string,
            $pad_type
        );
    }

    public static function buildPath(LTreeModelInterface $model): void
    {
        /** @var Model|LTreeModelInterface $model */
        $pathValue = [];
        if ($model->getLtreeParentId()) {
            $parent = $model->ltreeParent;
            $pathValue = array_merge($pathValue, $parent->getLtreePath());
        }
        $pathValue[] = $model->getKey();
        DB::statement(sprintf(
            "UPDATE %s SET %s = text2ltree('%s') WHERE %s = %s",
            $model->getTable(),
            $model->getLtreePathColumn(),
            implode(LTreeType::TYPE_SEPARATE, $pathValue),
            $model->getKeyName(),
            $model->getKey()
        ));
        $model->refresh();
    }

    public static function moveNode(
        LTreeModelInterface $model,
        ?LTreeModelInterface $to = null,
        array $proxyColumns = []
    ): void {
        $pathName = $model->getLtreePathColumn();
        $oldPath = $model->getLtreePath(LTreeModelInterface::AS_STRING);
        $newPath = $to ? $to->getLtreePath(LTreeModelInterface::AS_STRING) : '';
        $expressions = static::wrapExpressions($proxyColumns);
        $expressions[] = sprintf(
            "\"%2\$s\" = (text2ltree('%1\$s') || subpath(\"%2\$s\", (nlevel(text2ltree('%3\$s')) - 1)))",
            $newPath,
            $pathName,
            $oldPath
        );
        DB::statement(sprintf(
            "UPDATE %s SET %s WHERE (%s <@ text2ltree('%s')) = true",
            $model->getTable(),
            implode(', ', $expressions),
            $pathName,
            $oldPath
        ));
        $model->refresh();
    }

    public static function dropDescendants(LTreeModelInterface $model, array $proxyColumns = []): void
    {
        $sql = sprintf(
            "UPDATE %s SET %s WHERE (%s <@ text2ltree('%s')) = true",
            $model->getTable(),
            implode(', ', static::wrapExpressions($proxyColumns)),
            $model->getLtreePathColumn(),
            $model->getLtreePath(LTreeModelInterface::AS_STRING)
        );
        DB::statement($sql);
        $model->refresh();
    }

    public static function pathAsString($path): string
    {
        if (is_array($path)) {
            $path = implode(LTreeType::TYPE_SEPARATE, $path);
        }
        return $path;
    }

    public static function getAncestors(Collection $collection): Collection
    {
        if ($collection->count() === 0) {
            return new Collection();
        }
        $first = $collection->first();
        $paths = $collection->pluck($first->getLtreePathColumn())->map(function ($item) {
            return "'${item}'";
        });
        $paths = $paths->implode(', ');
        return $first::where($first->getLtreePathColumn(), '@>', DB::raw("array[${paths}]::ltree[]"))->get();
    }

    protected static function wrapExpressions(array $columns): array
    {
        $expressions = [];
        foreach ($columns as $column => $value) {
            if (is_numeric($value)) {
                $expressions[] = sprintf('%s = %s', $column, $value);
            } else {
                $expressions[] = sprintf("%s = '%s'", $column, $value);
            }
        }
        return $expressions;
    }
}
