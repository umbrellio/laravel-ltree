<?php

declare(strict_types=1);

namespace Umbrellio\LTree\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Umbrellio\LTree\Interfaces\LTreeModelInterface;
use Umbrellio\LTree\Traits\LTreeModelTrait;

class BelongsToAncestorsTree extends AbstractBelongsToTree
{
    /**
     * @param Builder|LTreeModelTrait|Model $query
     * @param Model|LTreeModelInterface $model
     */
    protected function modifyQuery($query, Model $model): Builder
    {
        return $query->ancestorsOf($model);
    }

    protected function getOperator(): string
    {
        return '@>';
    }
}
