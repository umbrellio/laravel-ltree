<?php

declare(strict_types=1);

namespace Umbrellio\LTree\Tests\Functional;

use Illuminate\Database\Eloquent\Model;
use Umbrellio\LTree\Traits\HasTreeRelationships;

final class SomeStub extends Model
{
    use HasTreeRelationships;

    protected $table = 'products';

    protected $fillable = ['id', 'some_id'];

    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected $dates = ['created_at', 'updated_at'];

    public function some()
    {
        return $this->belongsTo(self::class);
    }

    public function someTree()
    {
        return $this->belongsToTree(self::class, 'some');
    }
}
