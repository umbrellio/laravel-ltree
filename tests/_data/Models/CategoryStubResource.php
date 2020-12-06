<?php

declare(strict_types=1);

namespace Umbrellio\LTree\tests\_data\Models;

use Umbrellio\LTree\Helpers\LTreeNode;
use Umbrellio\LTree\Interfaces\LTreeInterface;
use Umbrellio\LTree\Interfaces\ModelInterface;
use Umbrellio\LTree\Resources\LTreeResource;

/**
 * @property LTreeNode $resource
 */
class CategoryStubResource extends LTreeResource
{
    /**
     * @param LTreeInterface|ModelInterface $model
     */
    protected function toTreeArray($request, $model)
    {
        return [
            'id' => $model->getKey(),
            'path' => $model->getLtreePath(LTreeInterface::AS_STRING),
        ];
    }
}
