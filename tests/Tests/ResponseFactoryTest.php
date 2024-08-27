<?php

namespace Inertia\Tests;

use Inertia\Inertia;
use Inertia\LazyProp;
use Inertia\AlwaysProp;
use Inertia\ResponseFactory;
use Inertia\Support\Header;
use Inertia\Tests\Stubs\ExampleMiddleware;

use Concrete\Core\Http\Response;
use Concrete\Core\Routing\RedirectResponse;
use Concrete\Core\Routing\Redirect;
use Concrete\Core\Routing\Route;
use Concrete\Core\Http\Request;
use Illuminate\Contracts\Support\Arrayable;
use Concrete\Core\Support\Facade\Application;

class ResponseFactoryTest extends TestCase
{
    public function test_can_macro(): void
    {
        $factory = new ResponseFactory();
        $factory->macro('foo', function () {
            return 'bar';
        });

        $this->assertEquals('bar', $factory->foo());
    }

    public function test_location_response_for_inertia_requests(): void
    {
        $req = Request::getInstance();
        $req->headers->set(Header::INERTIA, true);

        $response = (new ResponseFactory())->location('https://inertiajs.com/');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(Response::HTTP_CONFLICT, $response->getStatusCode());
        $this->assertEquals('https://inertiajs.com/', $response->headers->get('X-Inertia-Location'));
    }

    public function test_location_response_for_non_inertia_requests(): void
    {
        $req = Request::getInstance();
        $req->headers->set(Header::INERTIA, false);

        $response = (new ResponseFactory())->location('https://inertiajs.com/');

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
        $this->assertEquals('https://inertiajs.com/', $response->headers->get('location'));
    }

    public function test_location_response_for_inertia_requests_using_redirect_response(): void
    {
        $req = Request::getInstance();
        $req->headers->set(Header::INERTIA, true);

        $redirect = Redirect::url('https://inertiajs.com/');
        $response = (new ResponseFactory())->location($redirect);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(409, $response->getStatusCode());
        $this->assertEquals('https://inertiajs.com/', $response->headers->get('X-Inertia-Location'));
    }

    public function test_location_response_for_non_inertia_requests_using_redirect_response(): void
    {
        $req = Request::getInstance();
        $req->headers->set(Header::INERTIA, false);
        
        $redirect = Redirect::url('https://inertiajs.com/');
        $response = (new ResponseFactory())->location($redirect);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
        $this->assertEquals('https://inertiajs.com/', $response->headers->get('location'));
    }

    public function test_location_redirects_are_not_modified(): void
    {
        $response = (new ResponseFactory())->location('/foo');

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
        $this->assertEquals('/foo', $response->headers->get('location'));
    }

    public function test_location_response_for_non_inertia_requests_using_redirect_response_with_existing_session_and_request_properties(): void
    {
        // This test needs to be rewritten or removed - Symfony has no way to grab request/session from a response
        // the setRequest() method is part of Concrete's RedirectResponse wrapper class and currently does nothing
        // Additionally, session handling in Symfony is done in the Request
        $redirect = Redirect::url('https://inertiajs.com');
        $redirect->setRequest($request = new Request());
        $response = (new ResponseFactory())->location($redirect);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
        $this->assertEquals('https://inertiajs.com', $response->headers->get('location'));
        //$this->assertSame($request, $response->getRequest());
        $this->assertSame($response, $redirect);
    }

    public function test_the_version_can_be_a_closure(): void
    {
        $app = Application::getFacadeApplication();
        $router = $app->make('router');
        $router->get('/', function () {
            $this->assertSame('', Inertia::getVersion());

            Inertia::version(function () {
                return md5('Inertia');
            });

            return Inertia::render('User/Edit');
        })->addMiddleware(ExampleMiddleware::class);

        $request = Request::getInstance();
        $request->headers->set('X-Inertia', true);
        $request->headers->set('X-Inertia-Version', 'b19a24ee5c287f42ee1d465dab77ab37');
        $outRoute = $router->matchRoute($request)->getRoute();
        $action = $router->resolveAction($outRoute);
        $response = $action->execute($request, $outRoute, [])->toResponse($request);

        $this->assertTrue($response->isSuccessful());

        $json = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('component', $json);
        $this->assertEquals($json['component'], 'User/Edit');
    }

    public function test_shared_data_can_be_shared_from_anywhere(): void
    {
        $app = Application::getFacadeApplication();
        $router = $app->make('router');
        $router->get('/', function () {
            Inertia::share('foo', 'bar');

            return Inertia::render('User/Edit');
        })->addMiddleware(ExampleMiddleware::class);

        $request = Request::getInstance();
        $request->headers->set('X-Inertia', true);
        $outRoute = $router->matchRoute($request)->getRoute();
        $action = $router->resolveAction($outRoute);
        $response = $action->execute($request, $outRoute, [])->toResponse($request);

        $this->assertTrue($response->isSuccessful());

        $json = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('component', $json);
        $this->assertEquals($json['component'], 'User/Edit');
        $this->assertArrayHasKey('props', $json);
        $this->assertEquals($json['props'], array('foo'=>'bar'));
    }

    public function test_can_flush_shared_data(): void
    {
        Inertia::share('foo', 'bar');
        $this->assertSame(['foo' => 'bar'], Inertia::getShared());
        Inertia::flushShared();
        $this->assertSame([], Inertia::getShared());
    }

    public function test_can_create_lazy_prop(): void
    {
        $factory = new ResponseFactory();
        $lazyProp = $factory->lazy(function () {
            return 'A lazy value';
        });

        $this->assertInstanceOf(LazyProp::class, $lazyProp);
    }

    public function test_can_create_always_prop(): void
    {
        $factory = new ResponseFactory();
        $alwaysProp = $factory->always(function () {
            return 'An always value';
        });

        $this->assertInstanceOf(AlwaysProp::class, $alwaysProp);
    }

    public function test_will_accept_arrayabe_props()
    {
        $app = Application::getFacadeApplication();
        $router = $app->make('router');
        $router->get('/', function () {
            Inertia::share('foo', 'bar');

            return Inertia::render('User/Edit', new class() implements Arrayable {
                public function toArray()
                {
                    return [
                        'foo' => 'bar',
                    ];
                }
            });
        })->addMiddleware(ExampleMiddleware::class);

        $request = Request::getInstance();
        $request->headers->set('X-Inertia', true);
        $outRoute = $router->matchRoute($request)->getRoute();
        $action = $router->resolveAction($outRoute);
        $response = $action->execute($request, $outRoute, [])->toResponse($request);

        $this->assertTrue($response->isSuccessful());

        $json = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('component', $json);
        $this->assertEquals($json['component'], 'User/Edit');
        $this->assertArrayHasKey('props', $json);
        $this->assertEquals($json['props'], array('foo'=>'bar'));
    }
}
