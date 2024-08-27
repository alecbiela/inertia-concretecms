<?php

namespace Inertia\Tests;

use LogicException;
use Inertia\Inertia;
use Inertia\ServiceProvider;

use Illuminate\Testing\TestResponse;

use Concrete\Support\Facade\Application;
use Concrete\Core\View\View;
use Mockery\Adapter\Phpunit\MockeryTestCase as PHPUnitTestCase;
use ReflectionProperty;
use Package;

abstract class TestCase extends PHPUnitTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            ServiceProvider::class,
        ];
    }

    public function setUp(): void
    {
        parent::setUp();

        //View::addLocation(__DIR__.'/Stubs');

        Inertia::setRootView('welcome');

        $pkg = Package::getByHandle('inertia_ccms_adapter');
        $cfg = $pkg->getFileConfig();
        $cfg->save('inertia.testing.ensure_pages_exist', false);
        $cfg->save('inertia.testing.page_paths', [realpath(__DIR__)]);
    }

    /**
     * @throws LogicException
     */
    protected function getTestResponseClass(): string
    {
        // Laravel >= 7.0
        if (class_exists(TestResponse::class)) {
            return TestResponse::class;
        }

        throw new LogicException('Could not detect TestResponse class.');
    }

    /** @returns TestResponse|LegacyTestResponse */
    protected function makeMockRequest($view)
    {
        $app = Application::getFacadeApplication();
        $router = $app->make('router');
        $router->get('/example-url', function () use ($view) {
            return $view;
        });

        return $this->get('/example-url');
    }


    protected static function setNonPublicPropertyValues(object $object, array $properties): void
    {
        foreach ($properties as $propertyName => $propertyValue) {
            self::setNonPublicPropertyValue($object, $propertyName, $propertyValue);
        }
    }

    protected static function setNonPublicPropertyValue(object $object, string $propertyName, $propertyValue): void
    {
        $property = new ReflectionProperty($object, $propertyName);
        if (PHP_VERSION_ID < 80100) { // As of PHP 8.1.0, calling this method has no effect
            $property->setAccessible(true);
        }
        $property->setValue($object, $propertyValue);
    }
}
