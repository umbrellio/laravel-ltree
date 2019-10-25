<?php

declare(strict_types=1);

namespace Umbrellio\LTree\Traits;

use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Database\Eloquent\Model;
use Umbrellio\LTree\Exceptions\InvalidTraitInjectionClass;
use Umbrellio\LTree\Interfaces\LTreeModelInterface;
use Umbrellio\LTree\Relations\BelongsToTree;

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
     * @param string|null $foreignKey
     * @param null $ownerKey
     * @return BelongsToTree
     *
     * @throws InvalidTraitInjectionClass
     */
    protected function belongsToTree(
        string $related,
        string $throwRelation,
        ?string $foreignKey = null,
        $ownerKey = null
    ) {
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

        return new BelongsToTree($instance->newQuery(), $this, $throwRelation, $foreignKey, $ownerKey);
    }
}
