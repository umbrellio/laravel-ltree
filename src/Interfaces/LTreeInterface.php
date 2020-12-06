<?php

declare(strict_types=1);

namespace Umbrellio\LTree\Interfaces;

interface LTreeInterface
{
    public const AS_STRING = 1;
    public const AS_ARRAY = 2;

    public function getLtreeParentColumn(): string;
    public function getLtreeParentId(): ?int;
    public function getLtreePathColumn(): string;
    public function getLtreePath($mode = self::AS_ARRAY);
    public function getLtreeLevel(): int;
}
