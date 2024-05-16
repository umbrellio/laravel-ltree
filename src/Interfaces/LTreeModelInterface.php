<?php

declare(strict_types=1);

namespace Umbrellio\LTree\Interfaces;

use Illuminate\Database\Eloquent\Model;

/**
 * @see Model
 */
interface LTreeModelInterface extends HasLTreeRelations, HasLTreeScopes, ModelInterface, LTreeInterface
{
    public function getLtreeProxyDeleteColumns(): array;
    public function getLtreeProxyUpdateColumns(): array;
    public function isParentOf(int $id): bool;
    public function getAncestorByLevel(int $level = 1);
}
