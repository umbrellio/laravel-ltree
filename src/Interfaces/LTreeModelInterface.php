<?php

declare(strict_types=1);

namespace Umbrellio\LTree\Interfaces;

use App\Infrastructure\Interfaces\AncestryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property LTreeModelInterface|Model|BelongsTo|AncestryInterface $ltreeParent
 * @property LTreeModelInterface[]|Model[]|Collection|HasMany|AncestryInterface[] $ltreeChildrens
 * @method static Builder|LTreeModelInterface|AncestryInterface descendantsOf($model, bool $reverse = true)
 * @method static Builder|LTreeModelInterface|AncestryInterface ancestorsOf($model, bool $reverse = true)
 * @method static Builder|LTreeModelInterface|AncestryInterface withoutSelf(int $id)
 */
interface LTreeModelInterface
{
    public const AS_STRING = 1;
    public const AS_ARRAY = 2;

    public function getKeyName();
    public function getKey();
    public function getLtreeParentColumn(): string;
    public function getLtreeParentId(): ?int;
    public function getLtreePathColumn(): string;
    public function getLtreePath($mode = self::AS_ARRAY);
    public function getLtreeLevel(): int;
    public function getLtreeProxyDeleteColumns(): array;
    public function getLtreeProxyUpdateColumns(): array;
    public function isParentOf(int $id): bool;

    // relations
    public function ltreeParent(): BelongsTo;
    public function ltreeChildrens(): HasMany;

    // scopes
    public function scopeDescendantsOf(Builder $query, $model, bool $reverse = true): Builder;
    public function scopeAncestorsOf(Builder $query, $model, bool $reverse = true): Builder;
    public function scopeWithoutSelf(Builder $query, int $id): Builder;
}
