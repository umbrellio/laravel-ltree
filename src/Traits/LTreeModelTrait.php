<?php

declare(strict_types=1);

namespace Umbrellio\LTree\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Umbrellio\LTree\Collections\LTreeCollection;
use Umbrellio\LTree\Interfaces\LTreeModelInterface;
use Umbrellio\LTree\Types\LTreeType;

/**
 * @mixin Model
 * @mixin SoftDeletes
 */
trait LTreeModelTrait
{
    public function newCollection(array $models = []): LTreeCollection
    {
        return new LTreeCollection($models);
    }

    public function getLtreeKeyColumn(): string
    {
        return LTreeModelInterface::LTREE_KEY_COLUMN;
    }

    public function getLtreeParentColumn(): string
    {
        return LTreeModelInterface::LTREE_PARENT_COLUMN;
    }

    public function getLtreePathColumn(): string
    {
        return LTreeModelInterface::LTREE_PATH_COLUMN;
    }

    /**
     * @return int|string
     */
    public function getLtreeKey()
    {
        return $this->getAttribute($this->getLtreeKeyColumn());
    }

    /**
     * @return int|string|null
     */
    public function getLtreeParentId()
    {
        return $this->getAttribute($this->getLtreeParentColumn()) ?: null;
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

    public function ltreeParent()
    {
        return $this->belongsTo(static::class, $this->getLtreeParentColumn());
    }

    public function ltreeChildrens()
    {
        return $this->hasMany(static::class, $this->getLtreeParentColumn());
    }

    public function isParentOf($id): bool
    {
        return self::descendantsOf($this)
            ->withoutSelf($this->getLtreeKey())
            ->where($this->getLtreeKeyColumn(), '=', $id) !== null;
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

    public function scopeWithoutSelf(Builder $query, $id): Builder
    {
        return $query->whereRaw(sprintf('%s <> %s', $this->getLtreeKeyColumn(), $id));
    }

    public function getLtreeProxyUpdateColumns(): array
    {
        return ['updated_at'];
    }

    public function getLtreeProxyDeleteColumns(): array
    {
        return ['deleted_at'];
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
