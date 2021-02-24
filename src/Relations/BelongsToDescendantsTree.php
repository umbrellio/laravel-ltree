<?php

declare(strict_types=1);

namespace Umbrellio\LTree\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Umbrellio\LTree\Interfaces\LTreeModelInterface;
use Umbrellio\LTree\Traits\LTreeModelTrait;

class BelongsToDescendantsTree extends AbstractBelongsToTree
{
    /**
     * @param Builder|LTreeModelTrait|Model $query
     * @param Model|LTreeModelInterface $model
     */
    protected function modifyQuery($query, Model $model): Builder
    {
        return $query->descendantsOf($model);
    }

    protected function getOperator(): string
    {
        return '<@';
    }
}
