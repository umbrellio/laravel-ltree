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
