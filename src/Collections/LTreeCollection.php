<?php

declare(strict_types=1);

namespace Umbrellio\LTree\Collections;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Umbrellio\LTree\Helpers\LTreeBuilder;
use Umbrellio\LTree\Helpers\LTreeNode;
use Umbrellio\LTree\Interfaces\LTreeInterface;
use Umbrellio\LTree\Interfaces\ModelInterface;

/**
 * @method LTreeInterface|ModelInterface first()
 * @property LTreeInterface[]|ModelInterface[] $items
 */
class LTreeCollection extends Collection
{
    public function toTree(bool $usingSort = true): LTreeNode
    {
        if (!$model = $this->first()) {
            return new LTreeNode();
        }

        $this->loadMissingNodes($model);

        $builder = new LTreeBuilder(
            $model->getLtreePathColumn(),
            $model->getKeyName(),
            $model->getLtreeParentColumn()
        );

        return $builder->build($collection ?? $this, $usingSort);
    }

    /**
     * This method loads the missing nodes, making the tree branches correct.
     */
    private function loadMissingNodes($model): self
    {
        if ($this->hasMissingNodes($model)) {
            $this->appendAncestors($model);
        }

        return $this;
    }

    /**
     * @param LTreeInterface|ModelInterface $model
     */
    private function hasMissingNodes($model): bool
    {
        $paths = collect();

        foreach ($this->items as $item) {
            $paths = $paths->merge($item->getLtreePath());
        }

        return $paths
            ->unique()
            ->diff($this->pluck($model->getKeyName()))
            ->isNotEmpty();
    }

    /**
     * @param LTreeInterface|ModelInterface $model
     */
    private function appendAncestors($model): void
    {
        $paths = $this->pluck($model->getLtreePathColumn())->toArray();
        $ids = $this->pluck($model->getKeyName())->toArray();

        /** @var Model $model */
        $parents = $model::parentsOf($paths)
            ->whereKeyNot($ids)
            ->get();

        foreach ($parents as $item) {
            $this->add($item);
        }
    }
}
