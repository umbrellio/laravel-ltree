<?php

declare(strict_types=1);

namespace Umbrellio\LTree\Interfaces;

use Illuminate\Database\Eloquent\Model;

/**
 * @see Model
 */
interface LTreeModelInterface extends HasLTreeRelations, HasLTreeScopes, ModelInterface
{
    public const AS_STRING = 1;
    public const AS_ARRAY = 2;

    public function getLtreeParentColumn(): string;
    public function getLtreeParentId(): ?int;
    public function getLtreePathColumn(): string;
    public function getLtreePath($mode = self::AS_ARRAY);
    public function getLtreeLevel(): int;
    public function getLtreeProxyDeleteColumns(): array;
    public function getLtreeProxyUpdateColumns(): array;

    public function isParentOf(int $id): bool;

    public function getAncestorByLevel(int $level = 1);
}
