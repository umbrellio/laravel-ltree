<?php

namespace Umbrellio\LTree\Collections;

use Umbrellio\LTree\Helpers\LTreeBuilder;
use Umbrellio\LTree\Helpers\LTreeNode;
use Umbrellio\Postgres\Schema\Extensions\AbstractObject;

class LTreeCollection extends AbstractObject
{
    public function getToTreeFunction()
    {
        return function (
            string $pathField = 'path',
            string $idField = 'id',
            string $parentIdField = 'parent_id'
        ): LTreeNode {
            $builder = new LTreeBuilder($pathField, $idField, $parentIdField);
            return $builder->build($this);
        };
    }
}
