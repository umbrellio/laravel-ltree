<?php

declare(strict_types=1);

namespace Umbrellio\LTree\Services;

use Umbrellio\LTree\Helpers\LTreeHelper;
use Umbrellio\LTree\Interfaces\LTreeModelInterface;
use Umbrellio\LTree\Interfaces\LTreeServiceInterface;

final class LTreeService implements LTreeServiceInterface
{
    public function createPath(LTreeModelInterface $model): void
    {
        LTreeHelper::buildPath($model);
    }

    public function updatePath(LTreeModelInterface $model): void
    {
        LTreeHelper::moveNode($model, $model->ltreeParent, array_intersect_key(
            $model->getAttributes(),
            array_flip($model->getLtreeProxyUpdateColumns())
        ));
        LTreeHelper::buildPath($model);
    }

    public function dropDescendants(LTreeModelInterface $model): void
    {
        LTreeHelper::dropDescendants($model, array_intersect_key(
            $model->getAttributes(),
            array_flip($model->getLtreeProxyDeleteColumns())
        ));
    }
}
