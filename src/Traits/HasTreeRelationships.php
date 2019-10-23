<?php

namespace App\Infrastructure\Traits;

use App\Infrastructure\Models\BelongsToLevel;
use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Database\Eloquent\Model;
use Umbrellio\LTree\Traits\LTreeModelTrait;

/**
 * @mixin HasRelationships
 * @mixin LTreeModelTrait
 * @mixin Model
 */
trait HasTreeRelationships
{
    protected function belongsToLevel($related, int $level = 1, ?Model $instance = null, ?string $relation = null)
    {
        if (is_null($relation)) {
            $relation = $this->guessBelongsToRelation();
        }

        $instance = $instance ?: $this->newRelatedInstance($related);

        return new BelongsToLevel($instance->newQuery(), $this, $level, $relation);
    }
}
