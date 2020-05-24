<?php

declare(strict_types=1);

namespace Umbrellio\LTree\tests\_data\Models;

use Umbrellio\LTree\Resources\LTreeResourceCollection;

/**
 * @property CategoryStub $resource
 */
class CategoryStubResourceCollection extends LTreeResourceCollection
{
    public $resource = CategoryStubResource::class;
}
