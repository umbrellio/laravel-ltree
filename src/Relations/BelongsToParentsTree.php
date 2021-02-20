<?php

declare(strict_types=1);

namespace Umbrellio\LTree\Relations;

use Illuminate\Database\Eloquent\Builder;
use Umbrellio\LTree\Traits\LTreeModelTrait;

class BelongsToParentsTree extends AbstractBelongsToTree
{
    /**
     * @param Builder|LTreeModelTrait $model
     */
    protected function getQueryForTree($model): Builder
    {
        return $model
            ->newQuery()
            ->ancestorsOf($model)
            ->orderBy($this->getLTreeRelated()->getLtreePathColumn());
    }

    protected function getOperator(): string
    {
        return '@>';
    }
}
