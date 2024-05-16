<?php

declare(strict_types=1);

namespace Umbrellio\LTree\Collections;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Umbrellio\LTree\Helpers\LTreeBuilder;
use Umbrellio\LTree\Helpers\LTreeNode;
use Umbrellio\LTree\Interfaces\HasLTreeRelations;
use Umbrellio\LTree\Interfaces\LTreeInterface;
use Umbrellio\LTree\Interfaces\ModelInterface;
use Umbrellio\LTree\Traits\LTreeModelTrait;

/**
 * @method LTreeInterface|ModelInterface first()
 * @property LTreeInterface[]|ModelInterface[]|HasLTreeRelations[] $items
 */
class LTreeCollection extends Collection
{
    private $withLeaves = true;

    public function toTree(bool $usingSort = true, bool $loadMissing = true): LTreeNode
    {
        if (!$model = $this->first()) {
            return new LTreeNode();
        }

        if ($loadMissing) {
            $this->loadMissingNodes($model);
        }

        if (!$this->withLeaves) {
            $this->excludeLeaves();
        }

        $builder = new LTreeBuilder(
            $model->getLtreePathColumn(),
            $model->getKeyName(),
            $model->getLtreeParentColumn()
        );

        return $builder->build($collection ?? $this, $usingSort);
    }

    public function withLeaves(bool $state = true): self
    {
        $this->withLeaves = $state;

        return $this;
    }

    public function loadMissingNodes($model): self
    {
        if ($this->hasMissingNodes($model)) {
            $this->appendAncestors($model);
        }

        return $this;
    }

    private function excludeLeaves(): void
    {
        foreach ($this->items as $key => $item) {
            /** @var LTreeModelTrait $item */
            if ($item->ltreeChildren->isEmpty() && $item->getLtreeParentId()) {
                $this->forget($key);
            }
        }
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
        $paths = $this
            ->pluck($model->getLtreePathColumn())
            ->toArray();
        $ids = $this
            ->pluck($model->getKeyName())
            ->toArray();

        /** @var Model $model */
        $parents = $model::parentsOf($paths)
            ->whereKeyNot($ids)
            ->get();

        foreach ($parents as $item) {
            $this->add($item);
        }
    }
}
