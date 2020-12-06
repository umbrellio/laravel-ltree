<?php

declare(strict_types=1);

namespace Umbrellio\LTree\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Umbrellio\LTree\Helpers\LTreeNode;
use Umbrellio\LTree\Interfaces\LTreeInterface;

/**
 * @property LTreeNode $resource
 */
abstract class LTreeResource extends JsonResource
{
    final public function toArray($request)
    {
        return array_merge($this->toTreeArray($request, $this->resource->model), [
            'children' => static::collection($this->resource->getChildren())->toArray($request),
        ]);
    }

    /**
     * @param LTreeInterface $model
     */
    abstract protected function toTreeArray($request, $model);
}
