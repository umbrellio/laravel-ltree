<?php

declare(strict_types=1);

namespace Umbrellio\LTree\Traits;

use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Database\Eloquent\Model;
use Umbrellio\LTree\Exceptions\InvalidTraitInjectionClass;
use Umbrellio\LTree\Interfaces\LTreeModelInterface;
use Umbrellio\LTree\Relations\BelongsToLevel;

/**
 * @mixin HasRelationships
 * @mixin LTreeModelTrait
 * @mixin Model
 */
trait HasTreeRelationships
{
    /**
     * @param string $related
     * @param string $throwRelation
     * @param int $level
     * @param string|null $foreignKey
     * @param null $ownerKey
     * @param string|null $relation
     * @return BelongsToLevel
     *
     * @throws InvalidTraitInjectionClass
     */
    protected function belongsToLevel(
        string $related,
        string $throwRelation,
        int $level = 1,
        ?string $foreignKey = null,
        $ownerKey = null,
        ?string $relation = null
    ) {
        if ($relation === null) {
            $relation = $this->guessBelongsToRelation();
        }

        $instance = $this->newRelatedInstance($related);

        if (!$instance instanceof LTreeModelInterface) {
            throw new InvalidTraitInjectionClass(sprintf(
                'A class using this trait must implement an interface %s',
                LTreeModelInterface::class
            ));
        }

        if ($foreignKey === null) {
            $foreignKey = $this->{$throwRelation}()->getForeignKeyName();
        }

        $ownerKey = $ownerKey ?: $instance->getKeyName();

        return new BelongsToLevel(
            $instance->newQuery(),
            $this,
            $throwRelation,
            $level,
            $foreignKey,
            $ownerKey,
            $relation
        );
    }
}
