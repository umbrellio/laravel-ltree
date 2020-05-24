<?php

declare(strict_types=1);

namespace Umbrellio\LTree\tests\_data\Models;

use Illuminate\Database\Eloquent\Model;
use Umbrellio\LTree\Resources\LTreeResource;

/**
 * @property Model $resource
 */
class CategoryStubResource extends LTreeResource
{
    protected function toTreeArray($request)
    {
        return [
            'id' => $this->resource->getKey(),
        ];
    }
}
