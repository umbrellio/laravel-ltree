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

    /**
     * @method LTreeCollection|Collection|Builder[] get($columns = ['*'])
     */
    class Builder
    {
    }
}

namespace Umbrellio\LTree\Interfaces {

    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Collection;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\HasMany;

    /**
     * @property LTreeModelInterface|Model|BelongsTo $ltreeParent
     * @property LTreeModelInterface[]|Model[]|Collection|HasMany $ltreeChildrens
     * @method static Builder|LTreeModelInterface descendantsOf($model, bool $reverse = true)
     * @method static Builder|LTreeModelInterface ancestorsOf($model, bool $reverse = true)
     * @method static Builder|LTreeModelInterface parentsOf(array $paths)
     * @method static Builder|LTreeModelInterface withoutSelf($id)
     * @method static Builder|LTreeModelInterface ancestorByLevel(int $level = 1, ?string $path = null)
     */
    interface LTreeModelInterface
    {
    }
}

namespace Umbrellio\LTree\Traits {

    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Collection;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\HasMany;

    /**
     * @property LTreeModelTrait|Model|BelongsTo $ltreeParent
     * @property LTreeModelTrait[]|Model[]|Collection|HasMany $ltreeChildrens
     * @method static Builder|LTreeModelTrait descendantsOf($model, bool $reverse = true)
     * @method static Builder|LTreeModelTrait ancestorsOf($model, bool $reverse = true)
     * @method static Builder|LTreeModelTrait parentsOf(array $paths)
     * @method static Builder|LTreeModelTrait withoutSelf($id)
     * @method static Builder|LTreeModelTrait ancestorByLevel(int $level = 1, ?string $path = null)
     */
    interface LTreeModelTrait
    {
    }
}
