<?php

declare(strict_types=1);

namespace Umbrellio\LTree\Services;

use Illuminate\Database\Eloquent\Model;
use Umbrellio\LTree\Helpers\LTreeHelper;
use Umbrellio\LTree\Interfaces\LTreeModelInterface;
use Umbrellio\LTree\Interfaces\LTreeServiceInterface;

final class LTreeService implements LTreeServiceInterface
{
    private $helper;

    public function __construct(LTreeHelper $helper)
    {
        $this->helper = $helper;
    }

    /** @testing coverage checker */
    public function testMethod(LTreeModelInterface $model): void
    {
        $this->helper->buildPath($model);
    }

    public function createPath(LTreeModelInterface $model): void
    {
        $this->helper->buildPath($model);
    }

    /**
     * @param LTreeModelInterface|Model $model
     */
    public function updatePath(LTreeModelInterface $model): void
    {
        $columns = array_intersect_key($model->getAttributes(), array_flip($model->getLtreeProxyUpdateColumns()));

        $this->helper->moveNode($model, $model->ltreeParent, $columns);
        $this->helper->buildPath($model);
    }

    /**
     * @param LTreeModelInterface|Model $model
     */
    public function dropDescendants(LTreeModelInterface $model): void
    {
        $columns = array_intersect_key($model->getAttributes(), array_flip($model->getLtreeProxyDeleteColumns()));

        $this->helper->dropDescendants($model, $columns);
    }
}
