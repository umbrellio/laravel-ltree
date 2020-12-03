<?php

declare(strict_types=1);

namespace Umbrellio\LTree\Collections;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Umbrellio\LTree\Helpers\LTreeBuilder;
use Umbrellio\LTree\Helpers\LTreeNode;
use Umbrellio\LTree\Interfaces\LTreeModelInterface;

/**
 * @method LTreeModelInterface first()
 * @property LTreeModelInterface[] $items
 */
class LTreeCollection extends Collection
{
    public function makeConsistent(): self
    {
        if ($this->isEmpty()) {
            return $this;
        }

        $model = $this->first();

        if ($this->isConsistency($model)) {
            return $this;
        }

        $this->appendAncestors($model);

        return $this;
    }

    public function toTree(bool $usingSort = true): LTreeNode
    {
        if (!$model = $this->first()) {
            return new LTreeNode();
        }

        $builder = new LTreeBuilder(
            $model->getLtreePathColumn(),
            $model->getKeyName(),
            $model->getLtreeParentColumn()
        );

        return $builder->build($collection ?? $this, $usingSort);
    }

    private function isConsistency(LTreeModelInterface $model): bool
    {
        $paths = collect();

        foreach ($this->items as $item) {
            $paths = $paths->merge($item->getLtreePath());
        }

        return $paths
            ->unique()
            ->diff($this->pluck($model->getKeyName())->isEmpty())
            ->isEmpty();
    }

    private function appendAncestors(LTreeModelInterface $model): void
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

        $this->sortBy($model->getKeyName());
    }
}
