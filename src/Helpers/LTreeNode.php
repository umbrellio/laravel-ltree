<?php

declare(strict_types=1);

namespace Umbrellio\LTree\Helpers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Umbrellio\Common\Contracts\AbstractPresenter;
use Umbrellio\LTree\Interfaces\LTreeModelInterface;

/**
 * @property LTreeModelInterface $model
 */
class LTreeNode extends AbstractPresenter
{
    protected $parent;
    protected $children;

    public function __construct(Model $model = null)
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
        return $this->getChildren()->reduce(static function (int $count, self $node) {
            return $count + $node->countDescendants();
        }, $this->getChildren()->count());
    }

    public function findInTree(int $id): ?self
    {
        if (!$this->isRoot() && $this->model->getKey() === $id) {
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
        $this->getChildren()->each(static function (self $node) use ($callback) {
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
        return $this->model ? $this->model->getLtreePath(LTreeModelInterface::AS_STRING) : null;
    }

    public function toTreeArray(callable $callback)
    {
        return $this->fillTreeArray($this->getChildren(), $callback);
    }

    /**
     * Usage sortTree(['name' =>'asc', 'category'=>'desc'])
     * or callback with arguments ($a, $b) and return -1 | 0 | 1
     * @param array|callable $options
     */
    public function sortTree($options)
    {
        $children = $this->getChildren();
        $callback = $options;
        if (!is_callable($options)) {
            $callback = $this->optionsToCallback($options);
        }
        $children->each(static function ($child) use ($callback) {
            /** @var LTreeNode $child */
            $child->sortTree($callback);
        });
        $this->children = $children->sort($callback)->values();
    }

    private function fillTreeArray(iterable $nodes, callable $callback)
    {
        $data = [];
        foreach ($nodes as $node) {
            $item = $callback($node);
            $children = $this->fillTreeArray($node->getChildren(), $callback);
            $item['children'] = $children;
            $data[] = $item;
        }
        return $data;
    }

    private function optionsToCallback(array $options): callable
    {
        return function ($a, $b) use ($options) {
            foreach ($options as $property => $sort) {
                if (!in_array(strtolower($sort), ['asc', 'desc'], true)) {
                    throw new InvalidArgumentException("Order '${sort}'' must be asc or desc");
                }
                $order = strtolower($sort) === 'desc' ? -1 : 1;
                $result = $a->{$property} <=> $b->{$property};
                if ($result !== 0) {
                    return $result * $order;
                }
            }
            return 0;
        };
    }
}
