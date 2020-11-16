<?php

declare(strict_types=1);

namespace Umbrellio\LTree\Interfaces;

interface LTreeModelInterface
{
    public const LTREE_KEY_COLUMN = 'id';
    public const LTREE_PATH_COLUMN = 'path';
    public const LTREE_PARENT_COLUMN = 'parent_id';

    public const AS_STRING = 1;
    public const AS_ARRAY = 2;

    /**
     * @return int|string|null
     */
    public function getLtreeKey();
    public function getLtreeKeyColumn();

    /**
     * @return int|string|null
     */
    public function getLtreeParentId();
    public function getLtreeParentColumn(): string;

    public function getLtreePathColumn(): string;
    public function getLtreePath($mode = self::AS_ARRAY);
    public function getLtreeLevel(): int;
    public function getLtreeProxyDeleteColumns(): array;
    public function getLtreeProxyUpdateColumns(): array;

    /**
     * @param int|string $id
     */
    public function isParentOf($id): bool;
    public function getAncestorByLevel(int $level = 1);

    public function ltreeParent();
    public function ltreeChildrens();
}
