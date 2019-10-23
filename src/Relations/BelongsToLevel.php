<?php

declare(strict_types=1);

namespace Umbrellio\LTree\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Concerns\SupportsDefaultModels;
use Illuminate\Database\Query\JoinClause;
use Umbrellio\LTree\Interfaces\LTreeModelInterface;

class BelongsToLevel extends BelongsTo
{
    use SupportsDefaultModels;

    protected $level;
    protected $throwRelationName;

    public function __construct(
        Builder $query,
        Model $child,
        string $throwRelationName,
        int $level,
        string $foreignKey,
        string $ownerKey,
        string $relationName
    ) {
        $this->level = $level;
        $this->throwRelationName = $throwRelationName;

        parent::__construct($query, $child, $foreignKey, $ownerKey, $relationName);
    }

    public function addConstraints()
    {
        if (static::$constraints) {
            $relation = $this->child->{$this->throwRelationName};

            if ($relation) {
                $this->query = $relation->newQuery()->ancestorByLevel($this->level);
            }
        }
    }

    public function addEagerConstraints(array $models)
    {
        $key = $this->related->getTable() . '.' . $this->ownerKey;

        $whereIn = $this->whereInMethod($this->related, $this->ownerKey);

        $this->query->{$whereIn}($key, $this->getEagerModelKeys($models));

        $table = $this->getModel()->getTable();
        $alias = sprintf('%s_depends', $table);

        $this->query->join(
            sprintf('%s as %s', $table, $alias),
            function (JoinClause $query) use ($alias, $table) {
                /** @var LTreeModelInterface $related */
                $related = $this->child->{$this->throwRelationName}()->related;

                $query->whereRaw(sprintf(
                    '%1$s.%2$s @> %3$s.%2$s and nlevel(%1$s.%2$s) = %4$d',
                    $alias,
                    $related->getLtreePathColumn(),
                    $table,
                    $this->level
                ));
            }
        );

        $this->query->selectRaw(sprintf('%s.*, json_agg(%s.%s) as relation_ids', $alias, $table, $this->ownerKey));

        $this->query->groupBy($alias . '.id');
    }

    public function match(array $models, Collection $results, $relation)
    {
        $owner = $this->ownerKey;

        $dictionary = [];

        foreach ($results as $result) {
            $result->relation_ids = json_decode($result->relation_ids);
            $dictionary[$result->getAttribute($owner)] = $result;
        }

        foreach ($models as $model) {
            foreach ($dictionary as $related) {
                if (in_array($model->getAttribute($this->foreignKey), $related->relation_ids, true)) {
                    $model->setRelation($relation, $related);
                }
            }
        }

        return $models;
    }

    protected function getEagerModelKeys(array $models)
    {
        $keys = [];

        foreach ($models as $model) {
            if (($value = $model->{$this->foreignKey}) !== null) {
                $keys[] = $value;
            }
        }

        sort($keys);

        return array_values(array_unique($keys));
    }
}
