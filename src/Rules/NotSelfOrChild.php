<?php

declare(strict_types=1);

namespace Umbrellio\LTree\Rules;

use Illuminate\Contracts\Validation\Rule;
use Umbrellio\LTree\Interfaces\LTreeModelInterface;

final class NotSelfOrChild implements Rule
{
    private $model;

    public function __construct(?LTreeModelInterface $model)
    {
        $this->model = $model;
    }

    public function passes($attribute, $value)
    {
        if ($this->model) {
            return !($this->isSelf($value) || $this->isChild($value));
        }
        return true;
    }

    public function message()
    {
        return trans('ltree::validation.self_or_child');
    }

    protected function isSelf($value)
    {
        return $this->model->getKey() === (int) $value;
    }

    protected function isChild($value)
    {
        return $this->model->isParentOf((int) $value);
    }
}
