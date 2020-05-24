<?php

declare(strict_types=1);

namespace Umbrellio\LTree\Exceptions;

use LogicException;

class LTreeReflectionException extends LogicException
{
    public function __construct($id)
    {
        parent::__construct("Node '${id}' can`t be parent for itself");
    }
}
