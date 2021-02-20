<?php

declare(strict_types=1);

namespace Umbrellio\LTree\tests\_data\Models;

use Illuminate\Database\Eloquent\Model;
use Umbrellio\LTree\Interfaces\LTreeModelInterface;
use Umbrellio\LTree\Traits\HasTreeRelationships;
use Umbrellio\LTree\Traits\LTreeModelTrait;

final class ProductStub extends Model implements LTreeModelInterface
{
    use LTreeModelTrait;
    use HasTreeRelationships;

    protected $table = 'products';

    protected $fillable = ['id', 'category_id'];

    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected $dates = ['created_at', 'updated_at'];

    public function category()
    {
        return $this->belongsTo(CategoryStub::class);
    }

    public function categoryParentsTree()
    {
        return $this->belongsToParentsTree(CategoryStub::class, 'category');
    }

    public function categoryDescendantsTree()
    {
        return $this->belongsToDescendantsTree(CategoryStub::class, 'category');
    }
}
