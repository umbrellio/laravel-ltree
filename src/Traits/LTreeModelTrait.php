<?php

declare(strict_types=1);

namespace Umbrellio\LTree\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Umbrellio\LTree\Collections\LTreeCollection;
use Umbrellio\LTree\Interfaces\LTreeModelInterface;
use Umbrellio\LTree\Types\LTreeType;

/**
 * @mixin Model
 * @mixin LTreeModelInterface
 * @mixin SoftDeletes
 * @const string DELETED_AT
 */
trait LTreeModelTrait
{
    public function newCollection(array $models = []): LTreeCollection
    {
        return new LTreeCollection($models);
    }

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

    /**
     * @deprecated Will be removed since 5.*
     */
    public function ltreeChildrens(): HasMany
    {
        return $this->hasMany(static::class, $this->getLtreeParentColumn());
    }

    public function ltreeChildren(): HasMany
    {
        return $this->hasMany(static::class, $this->getLtreeParentColumn());
    }

    public function isParentOf(int $id): bool
    {
        return self::descendantsOf($this)->withoutSelf($this->getKey())->find($id) !== null;
    }

    public function scopeParentsOf(Builder $query, array $paths): Builder
    {
        return $query->whereRaw(sprintf(
            "%s @> array['%s']::ltree[]",
            $this->getLtreePathColumn(),
            implode("', '", $paths)
        ));
    }

    public function scopeRoot(Builder $query): Builder
    {
        return $query->whereNull($this->getLtreeParentColumn());
    }

    public function scopeDescendantsOf(Builder $query, LTreeModelInterface $model, bool $reverse = true): Builder
    {
        return $query->whereRaw(sprintf(
            "({$this->getLtreePathColumn()} <@ text2ltree('%s')) = %s",
            $model->getLtreePath(LTreeModelInterface::AS_STRING),
            $reverse ? 'true' : 'false'
        ));
    }

    public function scopeAncestorsOf(Builder $query, LTreeModelInterface $model, bool $reverse = true): Builder
    {
        return $query->whereRaw(sprintf(
            "({$this->getLtreePathColumn()} @> text2ltree('%s')) = %s",
            $model->getLtreePath(LTreeModelInterface::AS_STRING),
            $reverse ? 'true' : 'false'
        ));
    }

    public function scopeWithoutSelf(Builder $query, int $id): Builder
    {
        return $query->whereRaw(sprintf('%s <> %s', $this->getKeyName(), $id));
    }

    public function getLtreeProxyUpdateColumns(): array
    {
        return [$this->getUpdatedAtColumn()];
    }

    public function getLtreeProxyDeleteColumns(): array
    {
        return [$this->getDeletedAtColumn()];
    }

    public function getAncestorByLevel(int $level = 1)
    {
        return static::ancestorByLevel($level)->first();
    }

    public function scopeAncestorByLevel(Builder $query, int $level = 1, ?string $path = null): Builder
    {
        return $query->whereRaw(sprintf(
            "({$this->getLtreePathColumn()} @> text2ltree('%s')) and nlevel({$this->getLtreePathColumn()}) = %d",
            $path ?: $this->getLtreePath(LTreeModelInterface::AS_STRING),
            $level
        ));
    }
}
