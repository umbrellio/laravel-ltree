<?php

declare(strict_types=1);

namespace Umbrellio\LTree\Helpers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Umbrellio\LTree\Interfaces\LTreeModelInterface;
use Umbrellio\LTree\Types\LTreeType;

class LTreeHelper
{
    /**
     * @param LTreeModelInterface|Model $model
     */
    public function buildPath(LTreeModelInterface $model): void
    {
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

    /**
     * @param LTreeModelInterface|Model $model
     * @param LTreeModelInterface|Model|null $to
     */
    public function moveNode(LTreeModelInterface $model, ?LTreeModelInterface $to = null, array $columns = []): void
    {
        $pathName = $model->getLtreePathColumn();
        $oldPath = $model->getLtreePath(LTreeModelInterface::AS_STRING);
        $newPath = $to ? $to->getLtreePath(LTreeModelInterface::AS_STRING) : '';
        $expressions = static::wrapExpressions($columns);
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

    /**
     * @param LTreeModelInterface|Model $model
     */
    public function dropDescendants(LTreeModelInterface $model, array $columns = []): void
    {
        $sql = sprintf(
            "UPDATE %s SET %s WHERE (%s <@ text2ltree('%s')) = true",
            $model->getTable(),
            implode(', ', static::wrapExpressions($columns)),
            $model->getLtreePathColumn(),
            $model->getLtreePath(LTreeModelInterface::AS_STRING)
        );
        DB::statement($sql);
        $model->refresh();
    }

    public function getAncestors(Collection $collection): Collection
    {
        if ($collection->count() === 0) {
            return new Collection();
        }
        /** @var LTreeModelInterface|Model|Builder $first */
        $first = $collection->first();
        $paths = $collection->pluck($first->getLtreePathColumn())->map(function ($item) {
            return "'${item}'";
        });
        $paths = $paths->implode(', ');
        return $first::where($first->getLtreePathColumn(), '@>', DB::raw("array[${paths}]::ltree[]"))->get();
    }

    private function wrapExpressions(array $columns): array
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
