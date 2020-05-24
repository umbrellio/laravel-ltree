<?php

declare(strict_types=1);

namespace Umbrellio\LTree\Exceptions;

use LogicException;

class LTreeUndefinedNodeException extends LogicException
{
    public function __construct($id)
    {
        parent::__construct("There is no node with id '${id}'");
    }
}
