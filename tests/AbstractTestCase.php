<?php

namespace Bryanjamesmiller\Tests\LaravelInky;

use Bryanjamesmiller\LaravelInky\InkyServiceProvider;
use GrahamCampbell\TestBench\AbstractPackageTestCase;

abstract class AbstractTestCase extends AbstractPackageTestCase
{
    protected function getServiceProviderClass($app)
    {
        return InkyServiceProvider::class;
    }
}