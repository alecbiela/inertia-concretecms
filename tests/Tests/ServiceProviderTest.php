<?php
/**
 * Request and Route macros are currently unsupported in the Concrete CMS adapter
 * Disable this test for now, will be re-enabled if macros are added.
 */
namespace Inertia\Tests;

use Concrete\Core\Http\Request;
use Concrete\Core\Routing\Route;

class ServiceProviderTest extends TestCase
{

    // public function test_request_macro_is_registered(): void
    // {
    //     $request = Request::create('/user/123', 'GET');

    //     $this->assertFalse($request->inertia());

    //     $request->headers->add(['X-Inertia' => 'true']);

    //     $this->assertTrue($request->inertia());
    // }

    // public function test_route_macro_is_registered(): void
    // {
    //     $route = Route::inertia('/', 'User/Edit', ['user' => ['name' => 'Jonathan']]);
    //     $routes = Route::getRoutes();

    //     $this->assertNotEmpty($routes->getRoutes());
    //     $this->assertEquals($route, $routes->getRoutes()[0]);
    //     $this->assertEquals(['GET', 'HEAD'], $route->methods);
    //     $this->assertEquals('/', $route->uri);
    //     $this->assertEquals(['uses' => '\Inertia\Controller@__invoke', 'controller' => '\Inertia\Controller'], $route->action);
    //     $this->assertEquals(['component' => 'User/Edit', 'props' => ['user' => ['name' => 'Jonathan']]], $route->defaults);
    // }
}
