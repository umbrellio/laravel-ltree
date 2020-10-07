<?php

declare(strict_types=1);

namespace Umbrellio\LTree\Collections;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Umbrellio\LTree\Helpers\LTreeBuilder;
use Umbrellio\LTree\Helpers\LTreeNode;
use Umbrellio\LTree\Interfaces\LTreeModelInterface;
use Umbrellio\LTree\Traits\LTreeModelTrait;

class LTreeCollection extends Collection
{
    public function toTree(bool $usingSort = true): LTreeNode
    {
        /** @var LTreeModelInterface|Model|LTreeModelTrait $model */
        if (!$model = $this->first()) {
            return new LTreeNode();
        }

        $this->loadParents($model);

        $builder = new LTreeBuilder(
            $model->getLtreePathColumn(),
            $model->getKeyName(),
            $model->getLtreeParentColumn()
        );

        return $builder->build($this, $usingSort);
    }

    /**
     * @param LTreeModelInterface|LTreeModelTrait $model
     */
    private function loadParents(LTreeModelInterface $model): void
    {
        $paths = $this->pluck($model->getLtreePathColumn())->toArray();
        $ids = $this->pluck($model->getKeyName())->toArray();

        $parents = $model::parentsOf($paths)
            ->whereKeyNot($ids)
            ->getQuery()
            ->get();

        foreach ($parents as $item) {
            $this->items[$item->getKey()] = $item;
        }
    }
}
