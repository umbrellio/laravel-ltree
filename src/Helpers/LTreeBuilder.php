<?php

declare(strict_types=1);

namespace Umbrellio\LTree\Helpers;

use Umbrellio\LTree\Collections\LTreeCollection;
use Umbrellio\LTree\Exceptions\LTreeReflectionException;
use Umbrellio\LTree\Exceptions\LTreeUndefinedNodeException;

class LTreeBuilder
{
    private $pathField;
    private $idField;
    private $parentIdField;
    private $nodes = [];
    private $root = null;

    public function __construct(string $pathField, string $idField, string $parentIdField)
    {
        $this->pathField = $pathField;
        $this->idField = $idField;
        $this->parentIdField = $parentIdField;
    }

    public function build(LTreeCollection $items, bool $usingSort = true): LTreeNode
    {
        if ($usingSort === true) {
            $items = $items->sortBy($this->pathField, SORT_STRING);
        }

        $this->root = new LTreeNode();

        foreach ($items as $item) {
            $node = new LTreeNode($item);
            $id = $item->{$this->idField};
            $this->nodes[$id] = $node;
        }

        foreach ($items as $item) {
            [$id, $parentId] = $this->getNodeIds($item);
            $node = $this->nodes[$id];
            $parentNode = $this->getNode($parentId);
            $parentNode->addChild($node);
        }
        return $this->root;
    }

    private function getNodeIds($item): array
    {
        $parentId = $item->{$this->parentIdField};
        $id = $item->{$this->idField};

        if ($id === $parentId) {
            throw new LTreeReflectionException($id);
        }
        return [$id, $parentId];
    }

    private function getNode(?int $id): LTreeNode
    {
        if ($id === null) {
            return $this->root;
        }
        if (!isset($this->nodes[$id])) {
            throw new LTreeUndefinedNodeException($id);
        }
        return $this->nodes[$id];
    }
}
