<?php

declare(strict_types=1);

namespace Umbrellio\LTree\tests\_data\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Umbrellio\LTree\Interfaces\LTreeModelInterface;
use Umbrellio\LTree\Traits\LTreeModelTrait;

final class CategoryStub extends Model implements LTreeModelInterface
{
    use SoftDeletes;
    use LTreeModelTrait {
        getLtreeProxyDeleteColumns as getBaseLtreeProxyDeleteColumns;
    }

    protected $table = 'categories';

    protected $fillable = ['id', 'parent_id', 'path', 'is_deleted', 'name'];

    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    public function getLtreeProxyDeleteColumns(): array
    {
        return array_merge($this->getBaseLtreeProxyDeleteColumns(), ['is_deleted']);
    }
}
