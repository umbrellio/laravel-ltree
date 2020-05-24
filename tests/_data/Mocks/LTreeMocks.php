<?php

declare(strict_types=1);

namespace Umbrellio\LTree\tests\unit\_data\Mocks;

use Mockery;
use Umbrellio\LTree\Interfaces\LTreeServiceInterface;

trait LTreeMocks
{
    private function mockLtree(): LTreeServiceInterface
    {
        return Mockery::mock(LTreeServiceInterface::class);
    }
}
