<?php

declare(strict_types=1);

namespace Umbrellio\LTree\tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Umbrellio\LTree\Providers\LTreeExtensionProvider;
use Umbrellio\LTree\Providers\LTreeServiceProvider;
use Umbrellio\Postgres\UmbrellioPostgresProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [UmbrellioPostgresProvider::class, LTreeExtensionProvider::class, LTreeServiceProvider::class];
    }
}
