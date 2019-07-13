<?php

declare(strict_types=1);

namespace Umbrellio\LTree\Helpers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Umbrellio\LTree\Interfaces\LTreeModelInterface;
use Umbrellio\Presenter\AbstractPresenter;
use Umbrellio\Presenter\PresenterServiceProvider;

class LTreeNode extends AbstractPresenter
{
    protected $parent;
    protected $children;

    /**
     * LTreeNode constructor.
     * @param Model|PresenterServiceProvider $model
     */
    public function __construct($model = null)
    {
        parent::__construct($model);
    }

    public function isRoot(): bool
    {
        return $this->model === null;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): void
    {
        $this->parent = $parent;
    }

    public function addChild(self $node): void
    {
        $this->getChildren()->add($node);
        $node->setParent($this);
    }

    public function getChildren(): Collection
    {
        if (!$this->children) {
            $this->children = new Collection();
        }
        return $this->children;
    }

    public function countDescendants(): int
    {
        return $this->getChildren()->reduce(function (int $count, self $node) {
            return $count + $node->countDescendants();
        }, $this->getChildren()->count());
    }

    public function findInTree(int $id): ?self
    {
        if (!$this->isRoot() && $this->model->id === $id) {
            return $this;
        }
        foreach ($this->getChildren() as $child) {
            $result = $child->findInTree($id);
            if ($result !== null) {
                return $result;
            }
        }
        return null;
    }

    public function each(callable $callback): void
    {
        if (!$this->isRoot()) {
            $callback($this);
        }
        $this->getChildren()->each(function (self $node) use ($callback) {
            $node->each($callback);
        });
    }

    public function toCollection(): Collection
    {
        $collection = new Collection();
        $this->each(static function (self $item) use ($collection) {
            $collection->add($item->model);
        });
        return $collection;
    }

    public function pathAsString()
    {
        return $this->getLtreePath(LTreeModelInterface::AS_STRING);
    }
}
