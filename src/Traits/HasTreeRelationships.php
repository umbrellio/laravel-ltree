<?php

declare(strict_types=1);

namespace Umbrellio\LTree\Traits;

use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Database\Eloquent\Model;
use Umbrellio\LTree\Relations\BelongsToLevel;

/**
 * @mixin HasRelationships
 * @mixin LTreeModelTrait
 * @mixin Model
 */
trait HasTreeRelationships
{
    protected function belongsToLevel($related, int $level = 1, ?Model $instance = null, ?string $relation = null)
    {
        if ($relation === null) {
            $relation = $this->guessBelongsToRelation();
        }

        $instance = $instance ?: $this->newRelatedInstance($related);

        return new BelongsToLevel($instance->newQuery(), $this, $level, $relation);
    }
}
