<?php

declare(strict_types=1);

namespace Umbrellio\LTree\Traits;

use App\Infrastructure\Interfaces\AncestryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Umbrellio\LTree\Types\LTreeType;
use Umbrellio\LTree\Helpers\LTreeHelper;
use Umbrellio\LTree\Interfaces\LTreeModelInterface;

/**
 * @property LTreeModelInterface|Model|BelongsTo $ltreeParent
 * @property LTreeModelInterface[]|Model[]|Collection|HasMany $ltreeChildrens
 * @method static Builder|LTreeModelInterface|AncestryInterface descendantsOf($model, bool $reverse = true)
 * @method static Builder|LTreeModelInterface|AncestryInterface ancestorsOf($model, bool $reverse = true)
 * @method static Builder|LTreeModelInterface|AncestryInterface withoutSelf(int $id)
 */
trait LTreeModelTrait
{
    public function getLtreeParentColumn(): string
    {
        return 'parent_id';
    }

    public function getLtreePathColumn(): string
    {
        return 'path';
    }

    public function getLtreeParentId(): ?int
    {
        $value = $this->getAttribute($this->getLtreeParentColumn());
        return $value ? (int) $value : null;
    }

    public function getLtreePath($mode = LTreeModelInterface::AS_ARRAY)
    {
        $path = $this->getAttribute($this->getLtreePathColumn());
        if ($mode === LTreeModelInterface::AS_ARRAY) {
            return $path !== null ? explode(LTreeType::TYPE_SEPARATE, $path) : [];
        }
        return (string) $path;
    }

    public function getLtreeLevel(): int
    {
        return is_array($path = $this->getLtreePath()) ? count($path) : 1;
    }

    public function ltreeParent(): BelongsTo
    {
        return $this->belongsTo(static::class, $this->getLtreeParentColumn());
    }

    public function ltreeChildrens(): HasMany
    {
        return $this->hasMany(static::class, $this->getLtreeParentColumn());
    }

    public function isParentOf(int $id): bool
    {
        return self::descendantsOf($this)->withoutSelf($this->getKey())->find($id) !== null;
    }

    public function scopeDescendantsOf(Builder $query, $model, bool $reverse = true): Builder
    {
        return $query->whereRaw(sprintf(
            "({$this->getLtreePathColumn()} <@ text2ltree('%s')) = %s",
            LTreeHelper::pathAsString($model->getLtreePath()),
            $reverse ? 'true' : 'false'
        ));
    }

    public function scopeAncestorsOf(Builder $query, $model, bool $reverse = true): Builder
    {
        return $query->whereRaw(sprintf(
            "({$this->getLtreePathColumn()} @> text2ltree('%s')) = %s",
            LTreeHelper::pathAsString($model->getLtreePath()),
            $reverse ? 'true' : 'false'
        ));
    }

    public function scopeWithoutSelf(Builder $query, int $id): Builder
    {
        return $query->whereRaw(sprintf('id <> %s', $id));
    }

    public function getLtreeProxyUpdateColumns(): array
    {
        return [
            'updated_at',
        ];
    }

    public function getLtreeProxyDeleteColumns(): array
    {
        return [
            'deleted_at',
            'is_deleted',
            'deleted_by',
        ];
    }

    public function renderAsLtree($value, $pad_string = LTreeHelper::PAD_STRING, $pad_type = LTreeHelper::PAD_TYPE)
    {
        return LTreeHelper::renderAsLTree($value, $this->getLtreeLevel(), $pad_string, $pad_type);
    }
}
