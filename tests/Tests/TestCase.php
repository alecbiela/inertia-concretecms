<?php

namespace Inertia\Tests;

use LogicException;
use Inertia\Inertia;
use Inertia\ServiceProvider;

use Illuminate\Testing\TestResponse;

use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Http\Request;
use Concrete\Core\View\View;
use Mockery\Adapter\Phpunit\MockeryTestCase as PHPUnitTestCase;
use ReflectionProperty;
use Package;

use Concrete\Core\Application\ApplicationAwareInterface;
use Concrete\Core\Application\ApplicationAwareTrait;
use Concrete\Core\Http\ServerInterface;

abstract class TestCase extends PHPUnitTestCase implements ApplicationAwareInterface
{
    use ApplicationAwareTrait;

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

        // Enables use of $this->app in TestCase and Unit Tests that extend it
        $this->setApplication(Application::getFacadeApplication());

        Inertia::setRootView('welcome');

        $pkg = Package::getByHandle('inertia_ccms_adapter');
        $cfg = $pkg->getFileConfig();
        $cfg->save('inertia.testing.ensure_pages_exist', false);
        $cfg->save('inertia.testing.page_paths', [realpath(__DIR__)]);
    }

    /**
     * Create, then execute a mock GET request to URI "/"
     * @param Inertia\Response $view - A response returned by Inertia::render
     * @return Concrete\Core\Http\Response
     */
    protected function makeMockRequest($view)
    {
        $router = $this->app->make('router');
        $router->get('/example-url', function () use ($view) {
            return $view;
        });

        $req = Request::create('/example-url', 'GET');
        return $this->processMockRequest($req);
    }

    /**
     * Runs a request object through ConcreteCMS and returns the result
     * @param Request $request - A Concrete CMS Request object
     * @return Concrete\Core\Http\Response (specific type varies based on factors like Redirects)
     */
    protected function processMockRequest(Request $request)
    {
        $server = $this->app->make(ServerInterface::class);
        $response = $server->handleRequest($request);
        return $response;
    }
}
