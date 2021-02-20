<?php

declare(strict_types=1);

namespace Umbrellio\LTree\tests\_data\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Umbrellio\LTree\Traits\HasTreeRelationships;

final class CategorySomeStub extends Model
{
    use HasTreeRelationships;
    use SoftDeletes;

    protected $table = 'categories';

    protected $fillable = ['id', 'parent_id', 'path', 'is_deleted'];

    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(static::class);
    }

    public function parentParentsTree()
    {
        return $this->belongsToParentsTree(static::class, 'parent');
    }

    public function parentDescendantsTree()
    {
        return $this->belongsToDescendantsTree(static::class, 'parent');
    }
}
