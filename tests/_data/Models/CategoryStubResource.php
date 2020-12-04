<?php

declare(strict_types=1);

namespace Umbrellio\LTree\tests\_data\Models;

use Umbrellio\LTree\Interfaces\LTreeModelInterface;
use Umbrellio\LTree\Resources\LTreeResource;

/**
 * @property LTreeModelInterface $resource
 */
class CategoryStubResource extends LTreeResource
{
    protected function toTreeArray($request)
    {
        return [
            'id' => $this->resource->getKey(),
            'path' => $this->resource->getLtreePath(LTreeModelInterface::AS_STRING),
        ];
    }
}
