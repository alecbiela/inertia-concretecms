<?php

namespace Inertia\Tests;

use Closure;
use LogicException;
use Inertia\Inertia;
use Inertia\Middleware;
use Inertia\ServiceProvider;
use Inertia\Support\Header;
use Inertia\Testing\AssertableInertia;
use Inertia\Tests\Stubs\ExampleMiddleware;
use Package;
use Concrete\Core\Application\ApplicationAwareInterface;
use Concrete\Core\Application\ApplicationAwareTrait;
use Concrete\Core\Http\Request;
use Concrete\Core\Http\ServerInterface;
use Concrete\Core\Cache\Cache;
use Concrete\Core\Support\Facade\Application;
use Mockery\Adapter\Phpunit\MockeryTestCase as PHPUnitTestCase;
use PHPUnit\Framework\AssertionFailedError;
use Symfony\Component\Finder\Finder as FileViewFinder;

abstract class TestCase extends PHPUnitTestCase implements ApplicationAwareInterface
{
    use ApplicationAwareTrait;

    protected $config;

    public function setUp(): void
    {
        parent::setUp();

        // Enables use of $this->app in TestCase and Unit Tests that extend it
        $this->setApplication(Application::getFacadeApplication());

        Inertia::setRootView('welcome');

        $pkg = Package::getByHandle('inertia_ccms_adapter');
        $cfg = $pkg->getFileConfig();
        $cfg->save('inertia.testing.ensure_pages_exist', false);
        $cfg->save('inertia.testing.page_paths', [realpath(__DIR__)]);
        $this->config = $cfg;

        // We need to disable caching, since Concrete will cache the Inertia page responses
        // causing some tests to fail because the response was cached from the previous test
        Cache::disableAll();

        $this->app->bind('inertia.testing.view-finder', function ($app) use ($cfg) {
            $fv = new FileViewFinder();
            $fv->ignoreUnreadableDirs();

            $paths = array() + $cfg->get('inertia.testing.page_paths');
            $exts = array() + $cfg->get('inertia.testing.page_extensions');

            if(empty($paths)) {
                $fv->in(DIR_BASE)
                ->exclude('concrete')
                ->exclude('updates')
                ->exclude('node_modules')
                ->exclude('application/files');
            } else {
                $fv->in($paths);
            }

            if(!empty($exts)){
                foreach($exts as $ext){
                    $fv->name('*.'.$ext);
                }    
            }

            return $fv;
        });
    }

    /**
     * Prepares an endpoint at "/" for calling by the mock request
     * @param mixed         $version   The Inertia version
     * @param array         $shared    Array of shared props
     * @param Closure|null  $callback  A custom callback function to execute on the route
     * @param string|null   $method    The HTTP method to bind to (default: GET)
     */
    protected function prepareMockEndpoint($version = null, $shared = [], $middleware = null, $callback = null, $method = 'GET'): \Concrete\Core\Routing\RouteBuilder
    {
        if (is_null($version)) {
            $version = Request::getInstance()->headers->get(Header::VERSION, '');
            Inertia::version($version);
        }

        if (is_null($middleware)) {
            $middleware = new ExampleMiddleware($version, $shared);
        }

        $router = $this->app->make('router');
        if(is_null($callback)){
            return $router->$method('/', function() {
                $request = Request::getInstance();
                return Inertia::render('User/Edit', ['user' => ['name' => 'Jonathan']])->toResponse($request);
            })->addMiddleware($middleware);
        } else {
            return $router->$method('/', $callback)->addMiddleware($middleware);
        }
    }

    /**
     * Runs a request object through ConcreteCMS and returns the result
     * @param string|Request $uri The URI of the request
     * @param string $method The HTTP method of the request or NULL if request provided
     * @param array $headers Additional headers to set on the request [header => value]
     * @param array $server  Additional server globals to set on the request [global => value]
     * @return Concrete\Core\Http\Response (specific type varies based on factors like Redirects)
     */
    protected function processMockRequest(mixed $uri = '/', mixed $method = 'GET', array $headers = [], array $server = [])
    {
        $baseServer = [
            'HTTP_HOST' => 'www.requestdomain.com', 
            'SCRIPT_NAME' => '/path/to/server/index.php',
            'REQUEST_URI' => $uri
        ];

        // If $uri is already a request object, just set it, else create request
        if($uri instanceof Request) {
            $request = $uri;
        } else {
            $request = Request::create($uri, $method, [], [], [], $baseServer + $server, null);
        }

        if(count($headers)) $request->headers->add($headers);
        Request::setInstance($request);

        $server = $this->app->make(ServerInterface::class);
        return $server->handleRequest($request);
    }

    /**
     * Similar to the previous function, but only creates the request object, and does not produce a response
     * @param $uri must be a string
     * @return Request
     */
    protected function createMockRequest(string $uri = '/', mixed $method = 'GET', array $headers = [], array $server = []) {
        $baseServer = [
            'HTTP_HOST' => 'www.requestdomain.com', 
            'SCRIPT_NAME' => '/path/to/server/index.php',
            'REQUEST_URI' => $uri
        ];

        $request = Request::create($uri, $method, [], [], [], $baseServer + $server, null);

        if(count($headers)) $request->headers->add($headers);
        Request::setInstance($request);

        return $request;
    }

    /**
     * Asserts that a response that was returned is a valid Inertia response
     * This assert is unit tested in Testing\TestResponseMacrosTest
     * Returns the AssertableInertia object generated from the response
     */
    protected function assertInertia($response, Closure $callback = null)
    {
        // Fails within this method if not valid inertia
        $ai = AssertableInertia::fromResponse($response);

        if(is_null($callback)) return $ai;

        $callback($ai);
        return $ai;
    }

    public function inertiaPage()
    {
        return function () {
            return AssertableInertia::fromResponse($this)->toArray();
        };
    }
}
