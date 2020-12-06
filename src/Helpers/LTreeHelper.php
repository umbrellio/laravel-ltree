<?php

declare(strict_types=1);

namespace Umbrellio\LTree\Helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Umbrellio\LTree\Interfaces\LTreeInterface;
use Umbrellio\LTree\Interfaces\LTreeModelInterface;
use Umbrellio\LTree\Types\LTreeType;

class LTreeHelper
{
    /**
     * @param LTreeInterface|Model $model
     */
    public function buildPath($model): void
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
     * @param LTreeInterface|Model $model
     * @param LTreeInterface|Model|null $to
     */
    public function moveNode($model, $to = null, array $columns = []): void
    {
        $pathName = $model->getLtreePathColumn();
        $oldPath = $model->getLtreePath(LTreeModelInterface::AS_STRING);
        $newPath = $to ? $to->getLtreePath(LTreeModelInterface::AS_STRING) : '';
        $expressions = static::wrapExpressions($columns);
        $expressions[] = "
            \"${pathName}\" = (text2ltree('${newPath}') || subpath(\"${pathName}\", (nlevel(text2ltree('${oldPath}')) - 1)))
        ";

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
     * @param LTreeInterface|Model $model
     */
    public function dropDescendants($model, array $columns = []): void
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

    private function wrapExpressions(array $columns): array
    {
        $expressions = [];
        foreach ($columns as $column => $value) {
            if (is_numeric($value)) {
                $expressions[] = sprintf('%s = %s', (string) $column, (string) $value);
            } else {
                $expressions[] = sprintf("%s = '%s'", (string) $column, (string) $value);
            }
        }
        return $expressions;
    }
}
