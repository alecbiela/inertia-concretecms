<?php

namespace Inertia\Tests;

use LogicException;
use Inertia\Inertia;
use Inertia\AlwaysProp;
use Inertia\Middleware;
use Inertia\Support\Header;

use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Http\Request;
use Concrete\Core\Http\ServerInterface;
use Concrete\Core\Routing\RedirectResponse;

use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Inertia\Tests\Stubs\ExampleMiddleware;
use Illuminate\Session\Middleware\StartSession;

class MiddlewareTest extends TestCase
{

    public function test_no_response_value_by_default_means_automatically_redirecting_back_for_inertia_requests(): void
    {
        $fooCalled = false;
        $this->prepareMockEndpoint(null, [], Middleware::class, function() use (&$fooCalled){
            $fooCalled = true;
        }, 'PUT');

        $response = $this->processMockRequest('/', 'PUT', [
            'referer' => '/foo',
            Header::INERTIA => true,
            'Content-Type' => 'application/json'
        ]);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals($response->headers->get('location'), '/foo');
        $this->assertEquals($response->getStatusCode(), 303);
        $this->assertTrue($fooCalled);
    }

    public function test_no_response_value_can_be_customized_by_overriding_the_middleware_method(): void
    {
        $this->prepareMockEndpoint(null, [], null, function(){
            // Intentionally do nothing...
        });

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('An empty Inertia response was returned.');

        $response = $this->processMockRequest('/', 'GET', [
            'referer' => '/foo',
            Header::INERTIA => true,
            'Content-Type' => 'application/json'
        ]);
    }

    public function test_no_response_means_no_response_for_non_inertia_requests(): void
    {
        $fooCalled = false;
        $this->prepareMockEndpoint(null, [], Middleware::class, function() use (&$fooCalled){
            $fooCalled = true;
        }, 'PUT');

        $response = $this->processMockRequest('/', 'PUT', [
            'referer' => '/foo',
            'Content-Type' => 'application/json'
        ]);

        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertEmpty($response->getContent());
        $this->assertTrue($fooCalled);
    }

    public function test_the_version_is_optional(): void
    {
        $this->prepareMockEndpoint();

        $response = $this->processMockRequest('/', 'GET', [
            Header::INERTIA => true
        ]);

        $this->assertTrue($response->isSuccessful());
        $json = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('component', $json);
        $this->assertEquals($json['component'], 'User/Edit');
    }

    public function test_the_version_can_be_a_number(): void
    {
        $this->prepareMockEndpoint($version = 1597347897973);

        $response = $this->processMockRequest('/', 'GET', [
            Header::INERTIA => true,
            Header::VERSION => $version
        ]);

        $this->assertTrue($response->isSuccessful());
        $json = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('component', $json);
        $this->assertEquals($json['component'], 'User/Edit');
    }

    public function test_the_version_can_be_a_string(): void
    {
        $this->prepareMockEndpoint($version = 'foo-version');

        $response = $this->processMockRequest('/', 'GET', [
            Header::INERTIA => true,
            Header::VERSION => $version
        ]);

        $this->assertTrue($response->isSuccessful());
        $json = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('component', $json);
        $this->assertEquals($json['component'], 'User/Edit');
    }

    public function test_it_will_instruct_inertia_to_reload_on_a_version_mismatch(): void
    {
        $this->prepareMockEndpoint('1234');

        $request = Request::create('/', 'GET');
        $response = $this->processMockRequest($request, null, [
            Header::INERTIA => true,
            Header::VERSION => '4321'
        ]);

        $this->assertEquals($response->getStatusCode(), 409);
        $baseUrl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
        $this->assertEquals($response->headers->get(Header::LOCATION), $baseUrl);
        $this->assertEmpty($response->getContent());
    }

    public function test_validation_errors_are_registered_as_of_default(): void
    {
        $this->prepareMockEndpoint(null, [], null, function(){
            $this->assertInstanceOf(AlwaysProp::class, Inertia::getShared('errors'));
        });

        $response = $this->processMockRequest();
    }

    public function test_validation_errors_can_be_empty(): void
    {
        $this->prepareMockEndpoint(null, [], null, function(){
            $errors = Inertia::getShared('errors')();

            $this->assertIsObject($errors);
            $this->assertEmpty(get_object_vars($errors));
        });

        $response = $this->processMockRequest();
    }

    public function test_validation_errors_are_returned_in_the_correct_format(): void
    {
        $errors = $this->app->make('error');
        $errors->add('The name field is required.', 'name');
        $errors->add('Not a valid email address.', 'email');
        $this->app->make('session')->getFlashBag()->set('errors', array('default'=>$errors));

        $this->prepareMockEndpoint(null, [], null, function(){
            $errors = Inertia::getShared('errors')();

            $this->assertIsObject($errors);
            $this->assertSame('The name field is required.', $errors->name);
            $this->assertSame('Not a valid email address.', $errors->email);
        });

        $response = $this->processMockRequest();
    }

    public function test_validation_errors_with_named_error_bags_are_scoped(): void
    {
        $errors = $this->app->make('error');
        $errors->add('The name field is required.', 'name');
        $errors->add('Not a valid email address.', 'email');
        $this->app->make('session')->getFlashBag()->set('errors', array('example'=>$errors));

        $this->prepareMockEndpoint(null, [], null, function(){
            $errors = Inertia::getShared('errors')();

            $this->assertIsObject($errors);
            $this->assertSame('The name field is required.', $errors->example->name);
            $this->assertSame('Not a valid email address.', $errors->example->email);
        });

        $response = $this->processMockRequest();
    }

    public function test_default_validation_errors_can_be_overwritten(): void
    {
        $errors = $this->app->make('error');
        $errors->add('The name field is required.', 'name');
        $errors->add('Not a valid email address.', 'email');
        $this->app->make('session')->getFlashBag()->set('errors', array('example'=>$errors));

        $this->prepareMockEndpoint(null, ['errors' => 'foo']);

        $response = $this->processMockRequest('/', 'GET', [
            Header::INERTIA => true
        ]);

        $this->assertTrue($response->isSuccessful());
        $json = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('props', $json);
        $this->assertArrayHasKey('errors', $json['props']);
        $this->assertEquals($json['props']['errors'], 'foo');
    }

    public function test_validation_errors_are_scoped_to_error_bag_header(): void
    {
        $errors = $this->app->make('error');
        $errors->add('The name field is required.', 'name');
        $errors->add('Not a valid email address.', 'email');
        $this->app->make('session')->getFlashBag()->set('errors', array('default'=>$errors));

        $this->prepareMockEndpoint(null, [], null, function(){
            $errors = Inertia::getShared('errors')();

            $this->assertIsObject($errors);
            $this->assertSame('The name field is required.', $errors->example->name);
            $this->assertSame('Not a valid email address.', $errors->example->email);
        });

        $response = $this->processMockRequest('/', 'GET', [
            Header::ERROR_BAG => 'example'
        ]);
    }

    public function test_middleware_can_change_the_root_view_via_a_property(): void
    {
        $this->prepareMockEndpoint(null, [], new class() extends Middleware {
            protected $rootView = 'welcome';
        });

        $response = $this->processMockRequest();
        $this->assertTrue($response->isOk());
        // A hacky way to see if there's html with the ID of the root view in the response HTML
        // Not sure how else to do this, since the first visit returns a Concrete page.
        // Maybe set up an event listener for a core page event?
        $foundRoot = (strpos($response->getContent(), 'id="welcome"') !== false);
        $this->assertTrue($foundRoot);
    }

    public function test_middleware_can_change_the_root_view_by_overriding_the_rootview_method(): void
    {
        $this->prepareMockEndpoint(null, [], new class() extends Middleware {
            public function rootView(SymfonyRequest $request): string
            {
                return 'welcome';
            }
        });

        $response = $this->processMockRequest();
        $this->assertTrue($response->isOk());
        // See previous function
        $foundRoot = (strpos($response->getContent(), 'id="welcome"') !== false);
        $this->assertTrue($foundRoot);
    }

    /**
     * Prepares an endpoint at "/" for calling by the mock request
     * @param mixed         $version   The Inertia version
     * @param array         $shared    Array of shared props
     * @param Closure|null  $callback  A custom callback function to execute on the route
     * @param string|null   $method    The HTTP method to bind to (default: GET)
     */
    private function prepareMockEndpoint($version = null, $shared = [], $middleware = null, $callback = null, $method = 'GET'): \Concrete\Core\Routing\RouteBuilder
    {
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
}
