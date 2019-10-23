<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Concerns\SupportsDefaultModels;
use Illuminate\Database\Eloquent\Relations\Relation;

class BelongsToLevel extends Relation
{
    use SupportsDefaultModels;

    protected $level;

    protected $relationName;

    public function __construct(Builder $query, Model $parent, int $level, $relationName)
    {
        $this->level = $level;
        $this->relationName = $relationName;

        parent::__construct($query, $parent);
    }

    public function addConstraints()
    {
        $this->query->ancestorByLevel($this->level);
    }

    public function addEagerConstraints(array $models)
    {
        $this->query->ancestorByLevel($this->level);
    }

    public function initRelation(array $models, $relation)
    {
        foreach ($models as $model) {
            $model->setRelation($relation, $this->getDefaultFor($model));
        }

        return $models;
    }

    public function match(array $models, Collection $results, $relation)
    {
        return $models;
    }

    public function getResults()
    {
        return $this->query->first();
    }

    protected function newRelatedInstanceFor(Model $parent)
    {
        return $this->related->newInstance();
    }
}
