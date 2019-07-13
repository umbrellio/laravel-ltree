<?php

declare(strict_types=1);

namespace Umbrellio\LTree\Interfaces;

interface LTreeServiceInterface
{
    public function createPath(LTreeModelInterface $model): void;

    public function updatePath(LTreeModelInterface $model): void;

    public function dropDescendants(LTreeModelInterface $model): void;
}
