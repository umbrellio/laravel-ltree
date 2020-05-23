<?php

declare(strict_types=1);

namespace Umbrellio\LTree\tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Umbrellio\LTree\Providers\LTreeGrammarProvider;
use Umbrellio\LTree\Providers\LTreeServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [LTreeGrammarProvider::class, LTreeServiceProvider::class];
    }
}
