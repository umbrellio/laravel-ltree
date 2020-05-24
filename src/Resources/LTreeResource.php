<?php

declare(strict_types=1);

namespace Umbrellio\LTree\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Umbrellio\LTree\Helpers\LTreeNode;

/**
 * @property LTreeNode $resource
 */
abstract class LTreeResource extends JsonResource
{
    final public function toArray($request)
    {
        return array_merge($this->toTreeArray($request), [
            'children' => static::collection($this->resource->getChildren()),
        ]);
    }

    abstract protected function toTreeArray($request);
}
