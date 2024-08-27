<?php

namespace Inertia\Tests;

use Inertia\Controller;
use Inertia\Response;
use Illuminate\Support\Facades\Route;
use Inertia\Tests\Stubs\ExampleMiddleware;
use Illuminate\Session\Middleware\StartSession;

use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Http\Request;

class ControllerTest extends TestCase
{
    // TODO: Refactor this into a test which asserts that the first page visit uses the inertia response info
    
    // public function test_controller_returns_an_inertia_response(): void
    // {
    //     $app = Application::getFacadeApplication();
    //     $router = $app->make('router');
    //     $request = Request::getInstance();

    //     $route = $router
    //         ->get('/', Controller::class)
    //         ->setDefault('component', 'User/Edit')
    //         ->setDefault('props', [
    //             'user' => ['name' => 'Jonathan'],
    //         ]);
        
    //     // These 3 lines essentially mock a request to '/' and resolve the route
    //     $outRoute = $router->matchRoute($request)->getRoute();
    //     $action = $router->resolveAction($outRoute);
    //     $response = $action->execute($request, $outRoute, []);
    //     if($response instanceof Response) $response = $response->toResponse($request);
    //     var_dump($response);
    //     die();

    //     $this->assertEquals($response->viewData('page'), [
    //         'component' => 'User/Edit',
    //         'props' => [
    //             'user' => ['name' => 'Jonathan'],
    //             'errors' => (object) [],
    //         ],
    //         'url' => '/',
    //         'version' => '',
    //     ]);
    // }
}
