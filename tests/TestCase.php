<?php

declare(strict_types=1);

namespace Umbrellio\LTree\tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Umbrellio\LTree\tests\_data\Providers\LTreeProviderStub;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [LTreeProviderStub::class];
    }
}
