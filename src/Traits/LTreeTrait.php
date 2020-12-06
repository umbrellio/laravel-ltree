<?php

declare(strict_types=1);

namespace Umbrellio\LTree\Traits;

use Illuminate\Database\Eloquent\Model;
use Umbrellio\LTree\Interfaces\LTreeInterface;
use Umbrellio\LTree\Interfaces\LTreeModelInterface;
use Umbrellio\LTree\Types\LTreeType;

/**
 * @mixin Model
 */
trait LTreeTrait
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

    public function getLtreePath($mode = LTreeInterface::AS_ARRAY)
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
}
