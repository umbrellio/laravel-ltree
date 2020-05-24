<?php

declare(strict_types=1);

namespace Umbrellio\LTree\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Umbrellio\LTree\Collections\LTreeCollection;

abstract class LTreeResourceCollection extends ResourceCollection
{
    /**
     * @param LTreeCollection $resource
     */
    public function __construct($resource, $sort = null, bool $usingSort = true)
    {
        $collection = $resource->toTree($usingSort);

        if ($sort) {
            $collection->sortTree($sort);
        }

        parent::__construct($collection->getChildren());
    }
}
