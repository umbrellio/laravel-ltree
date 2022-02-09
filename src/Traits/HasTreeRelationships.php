<?php

declare(strict_types=1);

namespace Umbrellio\LTree\Traits;

use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Database\Eloquent\Model;
use Umbrellio\LTree\Exceptions\InvalidTraitInjectionClass;
use Umbrellio\LTree\Interfaces\LTreeModelInterface;
use Umbrellio\LTree\Relations\AbstractBelongsToTree;
use Umbrellio\LTree\Relations\BelongsToAncestorsTree;
use Umbrellio\LTree\Relations\BelongsToDescendantsTree;

/**
 * @mixin HasRelationships
 * @mixin LTreeModelTrait
 * @mixin Model
 */
trait HasTreeRelationships
{
    final protected function belongsToAncestorsTree(
        string $related,
        string $throwRelation,
        ?string $foreignKey = null,
        $ownerKey = null
    ) {
        return $this->belongsToTree(BelongsToAncestorsTree::class, $related, $throwRelation, $foreignKey, $ownerKey);
    }


    final protected function belongsToDescendantsTree(
        string $related,
        string $throwRelation,
        ?string $foreignKey = null,
        $ownerKey = null
    ) {
        return $this->belongsToTree(
            BelongsToDescendantsTree::class,
            $related,
            $throwRelation,
            $foreignKey,
            $ownerKey
        );
    }

    final protected function belongsToTree(
        string $relationClass,
        string $related,
        string $throwRelation,
        ?string $foreignKey = null,
        $ownerKey = null
    ): AbstractBelongsToTree {
        $instance = $this->newRelatedInstance($related);

        if (!$instance instanceof LTreeModelInterface) {
            throw new InvalidTraitInjectionClass(sprintf(
                'A class using this trait must implement an interface %s',
                LTreeModelInterface::class
            ));
        }

        if ($foreignKey === null) {
            $foreignKey = $this
                ->{$throwRelation}()
                ->getForeignKeyName();
        }

        $ownerKey = $ownerKey ?: $instance->getKeyName();

        return new $relationClass($instance->newQuery(), $this, $throwRelation, $foreignKey, $ownerKey);
    }
}
