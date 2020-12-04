<?php

namespace Illuminate\Database\Schema {

    /**
     * @method ColumnDefinition ltree(string $column)
     */
    class Blueprint
    {
    }
}

namespace Illuminate\Database\Eloquent {

    use Umbrellio\LTree\Collections\LTreeCollection;
    use Umbrellio\LTree\Interfaces\LTreeModelInterface;

    /**
     * @method LTreeCollection|Collection|Builder[]|LTreeModelInterface[] get($columns = ['*'])
     */
    class Builder
    {
    }

    /**
     * @method Builder descendantsOf(LTreeModelInterface|\Umbrellio\LTree\Interfaces\self $model, bool $reverse = true)
     * @method Builder ancestorsOf(LTreeModelInterface|Model $model, bool $reverse = true)
     * @method Builder parentsOf(array $paths)
     * @method Builder withoutSelf(int $id)
     * @method Builder ancestorByLevel(int $level = 1, ?string $path = null)
     *
     * @property LTreeCollection $ltreeChildren
     * @property LTreeModelInterface $ltreeParent
     */
    class Model
    {
    }
}
