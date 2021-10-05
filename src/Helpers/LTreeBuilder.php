<?php

declare(strict_types=1);

namespace Umbrellio\LTree\Helpers;

use Umbrellio\LTree\Collections\LTreeCollection;
use Umbrellio\LTree\Exceptions\LTreeReflectionException;
use Umbrellio\LTree\Exceptions\LTreeUndefinedNodeException;
use Umbrellio\LTree\Interfaces\LTreeModelInterface;

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

    /**
     * @param LTreeCollection|LTreeModelInterface $items
     */
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

        /** @var LTreeModelInterface $item */
        foreach ($items as $item) {
            [$id, $parentId, $path] = $this->getNodeIds($item);
            $node = $this->nodes[$id];
            $parentNode = $this->getNode($id, $path, $parentId);
            $parentNode->addChild($node);
        }
        return $this->root;
    }

    private function getNodeIds($item): array
    {
        $parentId = $item->{$this->parentIdField};
        $id = $item->{$this->idField};
        $path = $item->{$this->pathField};

        if ($id === $parentId) {
            throw new LTreeReflectionException($id);
        }
        return [$id, $parentId, $path];
    }

    /**
     * correct (missing: [1,2]) id  path        parent_id 1   1           null 2   1.2         1 3   1.2.3       2 4  
     * 1.2.3.4     3 5   1.2.3.4.5   4 6   1.2.3.4.6   4 7   1.2.3.4.7   4 8   1.2.3.8     3 9   1.2.3.9     3
     *
     * correct (missing: [1,2]) id  path        parent_id 3   1.2.3       2 4   1.2.3.4     3 5   1.2.3.4.5   4 6  
     * 1.2.3.4.6   4 7   1.2.3.4.7   4 8   1.2.3.8     3 9   1.2.3.9     3
     *
     *
     *
     * correct (missing: [1]) id  path        parent_id 2   1.2         1 3   1.2.3       2 4   1.2.3.4     3 5  
     * 1.2.3.4.5   4 6   1.2.3.4.6   4 7   1.2.3.4.7   4 8   1.2.3.8     3 9   1.2.3.9     3
     *
     * incorrect(missing: [2], but existing: [1]) id  path        parent_id 1   1           null 3   1.2.3       2 4  
     * 1.2.3.4     3 5   1.2.3.4.5   4 6   1.2.3.4.6   4 7   1.2.3.4.7   4 8   1.2.3.8     3 9   1.2.3.9     3
     */
    private function getNode(int $id, string $path, ?int $parentId): LTreeNode
    {
        if ($parentId === null || $this->hasNoMissingNodes($id, $path)) {
            return $this->root;
        }
        if (!isset($this->nodes[$parentId])) {
            throw new LTreeUndefinedNodeException($parentId);
        }
        return $this->nodes[$parentId];
    }

    private function hasNoMissingNodes(int $id, string $path): bool
    {
        $subpath = substr($path, 0, -strlen(".{$id}"));
        $subpathIds = explode('.', $subpath);

        $missingNodes = 0;
        foreach ($subpathIds as $parentId) {
            if (!isset($this->nodes[$parentId])) {
                $missingNodes++;
            }
        }

//        dd(compact('id', 'path', 'missingNodes', 'subpathIds', 'subpath'));

        return $subpathIds > 0 && $missingNodes === count($subpathIds);
    }
}
