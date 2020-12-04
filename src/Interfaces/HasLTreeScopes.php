<?php

declare(strict_types=1);

namespace Umbrellio\LTree\Interfaces;

use Illuminate\Database\Eloquent\Builder;

interface HasLTreeScopes
{
    public function scopeDescendantsOf(Builder $query, LTreeModelInterface $model, bool $reverse = true): Builder;
    public function scopeAncestorsOf(Builder $query, LTreeModelInterface $model, bool $reverse = true): Builder;
    public function scopeParentsOf(Builder $query, array $paths): Builder;
    public function scopeWithoutSelf(Builder $query, int $id): Builder;
    public function scopeAncestorByLevel(Builder $query, int $level = 1, ?string $path = null): Builder;
}
