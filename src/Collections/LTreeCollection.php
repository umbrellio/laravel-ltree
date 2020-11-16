<?php

declare(strict_types=1);

namespace Umbrellio\LTree\Collections;

use Illuminate\Database\Eloquent\Collection;
use Umbrellio\LTree\Helpers\LTreeBuilder;
use Umbrellio\LTree\Helpers\LTreeNode;
use Umbrellio\LTree\Interfaces\LTreeModelInterface;

class LTreeCollection extends Collection
{
    public function toTree(bool $usingSort = true): LTreeNode
    {
        /** @var LTreeModelInterface $model */
        if (!$model = $this->first()) {
            return new LTreeNode();
        }

        $builder = new LTreeBuilder(
            $model->getLtreePathColumn(),
            $model->getLtreeKeyColumn(),
            $model->getLtreeParentColumn()
        );

        return $builder->build($this, $usingSort);
    }
}
