<?php

namespace Inertia\Tests;

use DOMDocument;
use DOMElement;
use Mockery;
use Inertia\LazyProp;
use Inertia\Response;
use Inertia\AlwaysProp;
use Inertia\Support\Header;
use Inertia\Tests\Stubs\FakeResource;

use Concrete\Core\Http\Request;
use Concrete\Core\Http\Response as BaseResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

use Illuminate\View\View;
use Illuminate\Support\Fluent;

use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ResponseTest extends TestCase
{
    public function test_can_macro(): void
    {
        $response = new Response('User/Edit', []);
        $response->macro('foo', function () {
            return 'bar';
        });

        $this->assertEquals('bar', $response->foo());
    }

    public function test_server_response(): void
    {
        $request = $this->createMockRequest('/user/123', 'GET');

        $user = ['name' => 'Jonathan'];
        $response = new Response('User/Edit', ['user' => $user], 'app', '123');
        $response = $response->toResponse($request);

        $this->assertInstanceOf(BaseResponse::class, $response);

        $content = $response->getContent();
        $dom = new DOMDocument;
        $dom->loadHTML($content);
        $app = $dom->getElementById('app');
        if($app instanceof DOMElement){
            $json = $app->getAttribute('data-page');
            $this->assertJson($json);
            $this->assertSame('{"component":"User\/Edit","props":{"user":{"name":"Jonathan"}},"url":"\/user\/123","version":"123"}', $json);
            $page = json_decode($json, true);
            $this->assertSame('User/Edit', $page['component']);
            $this->assertSame('Jonathan', $page['props']['user']['name']);
            $this->assertSame('/user/123', $page['url']);
            $this->assertSame('123', $page['version']);
        } else {
            $this->fail("Could not parse the page object from the response.");
        }
    }

    public function test_xhr_response(): void
    {
        $request = $this->createMockRequest('/user/123', 'GET', [
            Header::INERTIA => true
        ]);

        $user = (object) ['name' => 'Jonathan'];
        $response = new Response('User/Edit', ['user' => $user], 'app', '123');
        $response = $response->toResponse($request);
        $json = $response->getContent();
        $page = json_decode($json);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertJson($json);
        $this->assertIsObject($page);

        $this->assertObjectHasAttribute('component', $page);
        $this->assertObjectHasAttribute('props', $page);
        $this->assertObjectHasAttribute('url', $page);
        $this->assertObjectHasAttribute('version', $page);

        $this->assertSame('User/Edit', $page->component);
        $this->assertSame('Jonathan', $page->props->user->name);
        $this->assertSame('/user/123', $page->url);
        $this->assertSame('123', $page->version);
    }

    /**
     * Because of how resources work in Concrete, we can't run the next few tests
     */


    // public function test_resource_response(): void
    // {
    //     $request = Request::create('/user/123', 'GET');
    //     $request->headers->add(['X-Inertia' => 'true']);

    //     $resource = new FakeResource(['name' => 'Jonathan']);

    //     $response = new Response('User/Edit', ['user' => $resource], 'app', '123');
    //     $response = $response->toResponse($request);
    //     $page = $response->getData();

    //     $this->assertInstanceOf(JsonResponse::class, $response);
    //     $this->assertSame('User/Edit', $page->component);
    //     $this->assertSame('Jonathan', $page->props->user->name);
    //     $this->assertSame('/user/123', $page->url);
    //     $this->assertSame('123', $page->version);
    // }

    // public function test_lazy_resource_response(): void
    // {
    //     $request = Request::create('/users', 'GET', ['page' => 1]);
    //     $request->headers->add(['X-Inertia' => 'true']);

    //     $users = Collection::make([
    //         new Fluent(['name' => 'Jonathan']),
    //         new Fluent(['name' => 'Taylor']),
    //         new Fluent(['name' => 'Jeffrey']),
    //     ]);

    //     $callable = static function () use ($users) {
    //         $page = new LengthAwarePaginator($users->take(2), $users->count(), 2);

    //         return new class($page, JsonResource::class) extends ResourceCollection {
    //         };
    //     };

    //     $response = new Response('User/Index', ['users' => $callable], 'app', '123');
    //     $response = $response->toResponse($request);
    //     $page = $response->getData();

    //     $expected = [
    //         'data' => $users->take(2),
    //         'links' => [
    //             'first' => '/?page=1',
    //             'last' => '/?page=2',
    //             'prev' => null,
    //             'next' => '/?page=2',
    //         ],
    //         'meta' => [
    //             'current_page' => 1,
    //             'from' => 1,
    //             'last_page' => 2,
    //             'path' => '/',
    //             'per_page' => 2,
    //             'to' => 2,
    //             'total' => 3,
    //         ],
    //     ];

    //     $this->assertInstanceOf(JsonResponse::class, $response);
    //     $this->assertSame('User/Index', $page->component);
    //     $this->assertSame('/users?page=1', $page->url);
    //     $this->assertSame('123', $page->version);
    //     tap($page->props->users, function ($users) use ($expected) {
    //         $this->assertSame(json_encode($expected['data']), json_encode($users->data));
    //         $this->assertSame(json_encode($expected['links']), json_encode($users->links));
    //         $this->assertSame('/', $users->meta->path);
    //     });
    // }

    // public function test_nested_lazy_resource_response(): void
    // {
    //     $request = Request::create('/users', 'GET', ['page' => 1]);
    //     $request->headers->add(['X-Inertia' => 'true']);

    //     $users = Collection::make([
    //         new Fluent(['name' => 'Jonathan']),
    //         new Fluent(['name' => 'Taylor']),
    //         new Fluent(['name' => 'Jeffrey']),
    //     ]);

    //     $callable = static function () use ($users) {
    //         $page = new LengthAwarePaginator($users->take(2), $users->count(), 2);

    //         // nested array with ResourceCollection to resolve
    //         return [
    //             'users' => new class($page, JsonResource::class) extends ResourceCollection {},
    //         ];
    //     };

    //     $response = new Response('User/Index', ['something' => $callable], 'app', '123');
    //     $response = $response->toResponse($request);
    //     $page = $response->getData();

    //     $expected = [
    //         'users' => [
    //             'data' => $users->take(2),
    //             'links' => [
    //                 'first' => '/?page=1',
    //                 'last' => '/?page=2',
    //                 'prev' => null,
    //                 'next' => '/?page=2',
    //             ],
    //             'meta' => [
    //                 'current_page' => 1,
    //                 'from' => 1,
    //                 'last_page' => 2,
    //                 'path' => '/',
    //                 'per_page' => 2,
    //                 'to' => 2,
    //                 'total' => 3,
    //             ],
    //         ],
    //     ];

    //     $this->assertInstanceOf(JsonResponse::class, $response);
    //     $this->assertSame('User/Index', $page->component);
    //     $this->assertSame('/users?page=1', $page->url);
    //     $this->assertSame('123', $page->version);
    //     tap($page->props->something->users, function ($users) use ($expected) {
    //         $this->assertSame(json_encode($expected['users']['data']), json_encode($users->data));
    //         $this->assertSame(json_encode($expected['users']['links']), json_encode($users->links));
    //         $this->assertSame('/', $users->meta->path);
    //     });
    // }

    // public function test_arrayable_prop_response(): void
    // {
    //     $request = Request::create('/user/123', 'GET');
    //     $request->headers->add(['X-Inertia' => 'true']);

    //     $resource = FakeResource::make(['name' => 'Jonathan']);

    //     $response = new Response('User/Edit', ['user' => $resource], 'app', '123');
    //     $response = $response->toResponse($request);
    //     $page = $response->getData();

    //     $this->assertInstanceOf(JsonResponse::class, $response);
    //     $this->assertSame('User/Edit', $page->component);
    //     $this->assertSame('Jonathan', $page->props->user->name);
    //     $this->assertSame('/user/123', $page->url);
    //     $this->assertSame('123', $page->version);
    // }

    public function test_promise_props_are_resolved(): void
    {
        $request = $this->createMockRequest('/user/123', 'GET', [
            Header::INERTIA => true
        ]);

        $user = (object) ['name' => 'Jonathan'];

        $promise = Mockery::mock('GuzzleHttp\Promise\PromiseInterface')
            ->shouldReceive('wait')
            ->andReturn($user)
            ->mock();

        $response = new Response('User/Edit', ['user' => $promise], 'app', '123');
        $response = $response->toResponse($request);
        $json = $response->getContent();
        $page = json_decode($json);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertJson($json);
        $this->assertIsObject($page);

        $this->assertObjectHasAttribute('component', $page);
        $this->assertObjectHasAttribute('props', $page);
        $this->assertObjectHasAttribute('url', $page);
        $this->assertObjectHasAttribute('version', $page);

        $this->assertSame('User/Edit', $page->component);
        $this->assertSame('Jonathan', $page->props->user->name);
        $this->assertSame('/user/123', $page->url);
        $this->assertSame('123', $page->version);
    }

    public function test_xhr_partial_response(): void
    {
        $request = $this->createMockRequest('/user/123', 'GET', [            
            Header::INERTIA => true,
            Header::PARTIAL_COMPONENT => 'User/Edit',
            Header::PARTIAL_ONLY => 'partial'
        ]);

        $user = (object) ['name' => 'Jonathan'];
        $response = new Response('User/Edit', ['user' => $user, 'partial' => 'partial-data'], 'app', '123');
        $response = $response->toResponse($request);
        $json = $response->getContent();
        $page = json_decode($json);

        $props = get_object_vars($page->props);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertJson($json);
        $this->assertIsObject($page);

        $this->assertObjectHasAttribute('component', $page);
        $this->assertObjectHasAttribute('props', $page);
        $this->assertObjectHasAttribute('url', $page);
        $this->assertObjectHasAttribute('version', $page);

        $this->assertFalse(isset($props['user']));
        $this->assertCount(1, $props);
        $this->assertSame('User/Edit', $page->component);
        $this->assertSame('/user/123', $page->url);
        $this->assertSame('123', $page->version);
    }

    public function test_exclude_props_from_partial_response(): void
    {
        $request = $this->createMockRequest('/user/123', 'GET', [            
            Header::INERTIA => true,
            Header::PARTIAL_COMPONENT => 'User/Edit',
            Header::PARTIAL_EXCEPT => 'user'
        ]);

        $user = (object) ['name' => 'Jonathan'];
        $response = new Response('User/Edit', ['user' => $user, 'partial' => 'partial-data'], 'app', '123');
        $response = $response->toResponse($request);
        $json = $response->getContent();
        $page = json_decode($json);

        $props = get_object_vars($page->props);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertJson($json);
        $this->assertIsObject($page);

        $this->assertObjectHasAttribute('component', $page);
        $this->assertObjectHasAttribute('props', $page);
        $this->assertObjectHasAttribute('url', $page);
        $this->assertObjectHasAttribute('version', $page);

        $this->assertFalse(isset($props['user']));
        $this->assertCount(1, $props);
        $this->assertSame('partial-data', $page->props->partial);
        $this->assertSame('User/Edit', $page->component);
        $this->assertSame('/user/123', $page->url);
        $this->assertSame('123', $page->version);
    }

    public function test_lazy_props_are_not_included_by_default(): void
    {
        $request = $this->createMockRequest('/user/123', 'GET', [
            Header::INERTIA => true
        ]);

        $lazyProp = new LazyProp(function () {
            return 'A lazy value';
        });

        $response = new Response('Users', ['users' => [], 'lazy' => $lazyProp], 'app', '123');
        $response = $response->toResponse($request);
        $json = $response->getContent();
        $page = json_decode($json);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertJson($json);
        $this->assertIsObject($page);
        $this->assertSame([], $page->props->users);
        $this->assertFalse(property_exists($page->props, 'lazy'));
    }

    public function test_lazy_props_are_included_in_partial_reload(): void
    {
        $request = $this->createMockRequest('/users', 'GET', [            
            Header::INERTIA => true,
            Header::PARTIAL_COMPONENT => 'Users',
            Header::PARTIAL_ONLY => 'lazy'
        ]);

        $lazyProp = new LazyProp(function () {
            return 'A lazy value';
        });

        $response = new Response('Users', ['users' => [], 'lazy' => $lazyProp], 'app', '123');
        $response = $response->toResponse($request);
        $json = $response->getContent();
        $page = json_decode($json);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertJson($json);
        $this->assertIsObject($page);

        $this->assertFalse(property_exists($page->props, 'users'));
        $this->assertSame('A lazy value', $page->props->lazy);
    }

    public function test_always_props_are_included_on_partial_reload(): void
    {
        $request = $this->createMockRequest('/user/edit/123', 'GET', [            
            Header::INERTIA => true,
            Header::PARTIAL_COMPONENT => 'Users',
            Header::PARTIAL_ONLY => 'data'
        ]);

        $props = [
            'user' => new LazyProp(function () {
                return [
                    'name' => 'Jonathan Reinink',
                    'email' => 'jonathan@example.com',
                ];
            }),
            'data' => [
                'name' => 'Taylor Otwell',
            ],
            'errors' => new AlwaysProp(function () {
                return [
                    'name' => 'The email field is required.',
                ];
            }),
        ];

        $response = new Response('User/Edit', $props, 'app', '123');
        $response = $response->toResponse($request);
        $json = $response->getContent();
        $page = json_decode($json);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertJson($json);
        $this->assertIsObject($page);

        $this->assertSame('The email field is required.', $page->props->errors->name);
        $this->assertSame('Taylor Otwell', $page->props->data->name);
        $this->assertFalse(isset($page->props->user));
    }

    public function test_top_level_dot_props_get_unpacked(): void
    {
        $props = [
            'auth' => [
                'user' => [
                    'name' => 'Jonathan Reinink',
                ],
            ],
            'auth.user.can' => [
                'do.stuff' => true,
            ],
            'product' => ['name' => 'My example product'],
        ];

        $request = $this->createMockRequest('/products/123', 'GET', [
            Header::INERTIA => true
        ]);

        $response = new Response('User/Edit', $props, 'app', '123');
        $response = $response->toResponse($request);
        $json = $response->getContent();
        $page = json_decode($json, true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertJson($json);
        $this->assertIsArray($page);

        $user = $page['props']['auth']['user'];
        $this->assertSame('Jonathan Reinink', $user['name']);
        $this->assertTrue($user['can']['do.stuff']);
        $this->assertFalse(array_key_exists('auth.user.can', $page['props']));
    }

    public function test_nested_dot_props_do_not_get_unpacked(): void
    {
        $props = [
            'auth' => [
                'user.can' => [
                    'do.stuff' => true,
                ],
                'user' => [
                    'name' => 'Jonathan Reinink',
                ],
            ],
            'product' => ['name' => 'My example product'],
        ];

        $request = $this->createMockRequest('/products/123', 'GET', [
            Header::INERTIA => true
        ]);

        $response = new Response('User/Edit', $props, 'app', '123');
        $response = $response->toResponse($request);
        $json = $response->getContent();
        $page = json_decode($json, true);

        $auth = $page['props']['auth'];
        $this->assertSame('Jonathan Reinink', $auth['user']['name']);
        $this->assertTrue($auth['user.can']['do.stuff']);
        $this->assertFalse(array_key_exists('can', $auth));
    }

    // public function test_responsable_with_invalid_key(): void
    // {
    //     $request = Request::create('/user/123', 'GET');
    //     $request->headers->add(['X-Inertia' => 'true']);

    //     $resource = new FakeResource(["\x00*\x00_invalid_key" => 'for object']);

    //     $response = new Response('User/Edit', ['resource' => $resource], 'app', '123');
    //     $response = $response->toResponse($request);
    //     $page = $response->getData(true);

    //     $this->assertSame(
    //         ["\x00*\x00_invalid_key" => 'for object'],
    //         $page['props']['resource']
    //     );
    // }

    /**
     * It doesn't appear Concrete supports prefixing URLs like this - Symfony doesn't employ X_FORWARDED_PREFIX
     */
    // public function test_the_page_url_is_prefixed_with_the_proxy_prefix(): void
    // {
    //     if (version_compare(app()->version(), '7', '<')) {
    //         $this->markTestSkipped('This test requires Laravel 7 or higher.');
    //     }

    //     Request::setTrustedProxies(['1.2.3.4'], Request::HEADER_X_FORWARDED_PREFIX);

    //     $request = Request::create('/user/123', 'GET');
    //     $request->server->set('REMOTE_ADDR', '1.2.3.4');
    //     $request->headers->set('X_FORWARDED_PREFIX', '/sub/directory');

    //     $user = ['name' => 'Jonathan'];
    //     $response = new Response('User/Edit', ['user' => $user], 'app', '123');
    //     $response = $response->toResponse($request);
    //     $view = $response->getOriginalContent();
    //     $page = $view->getData()['page'];

    //     $this->assertInstanceOf(BaseResponse::class, $response);
    //     $this->assertInstanceOf(View::class, $view);

    //     $this->assertSame('/sub/directory/user/123', $page['url']);
    // }

    public function test_the_page_url_doesnt_double_up(): void
    {
        $request = $this->createMockRequest('/subpath/product/123', 'GET', [
            Header::INERTIA => true
        ],[
            'SCRIPT_FILENAME' => '/project/public/index.php',
            'SCRIPT_NAME' => '/subpath/index.php'
        ]);

        $response = new Response('Product/Show', []);
        $response = $response->toResponse($request);
        $page = $response->getContent();
        $data = json_decode($page);

        $this->assertSame('/subpath/product/123', $data->url);
    }

    public function test_prop_as_basic_array(): void
    {
        $request = $this->createMockRequest('/years', 'GET');

        $response = new Response('Years', ['years' => [2022, 2023, 2024]], 'app', '123');

        $response = $response->toResponse($request);

        $this->assertInstanceOf(BaseResponse::class, $response);

        $content = $response->getContent();
        $dom = new DOMDocument();
        $dom->loadHTML($content);
        $app = $dom->getElementById('app');
        if($app instanceof DOMElement){
            $json = $app->getAttribute('data-page');
            $this->assertJson($json);
            $page = json_decode($json, true);
            $this->assertSame([2022, 2023, 2024], $page['props']['years']);
        } else {
            $this->fail("Could not parse the page object from the response.");
        }
    }

    public function test_dot_notation_props_are_merged_with_shared_props(): void
    {
        $request = $this->createMockRequest('/testshared', 'GET');

        $response = new Response('Test', [
            'auth' => ['user' => ['name' => 'Jonathan']],
            'auth.user.is_super' => true,
        ], 'app', '123');

        $response = $response->toResponse($request);

        $this->assertInstanceOf(BaseResponse::class, $response);

        $content = $response->getContent();
        $dom = new DOMDocument();
        $dom->loadHTML($content);
        $app = $dom->getElementById('app');
        if($app instanceof DOMElement){
            $json = $app->getAttribute('data-page');
            $this->assertJson($json);
            $page = json_decode($json, true);

            $this->assertSame([
                'auth' => [
                    'user' => [
                        'name' => 'Jonathan',
                        'is_super' => true,
                    ],
                ],
            ], $page['props']);
        } else {
            $this->fail("Could not parse the page object from the response.");
        }
    }

    public function test_dot_notation_props_are_merged_with_lazy_shared_props(): void
    {
        $request = $this->createMockRequest('/testlazy', 'GET');

        $response = new Response('Test', [
            'auth' => function () {
                return ['user' => ['name' => 'Jonathan']];
            },
            'auth.user.is_super' => true,
        ], 'app', '123');

        $response = $response->toResponse($request);

        $this->assertInstanceOf(BaseResponse::class, $response);

        $content = $response->getContent();
        $dom = new DOMDocument();
        $dom->loadHTML($content);
        $app = $dom->getElementById('app');
        if($app instanceof DOMElement){
            $json = $app->getAttribute('data-page');
            $this->assertJson($json);
            $page = json_decode($json, true);

            $this->assertSame([
                'auth' => [
                    'user' => [
                        'name' => 'Jonathan',
                        'is_super' => true,
                    ],
                ],
            ], $page['props']);
        } else {
            $this->fail("Could not parse the page object from the response.");
        }
    }

    public function test_dot_notation_props_are_merged_with_other_dot_notation_props(): void
    {
        $request = $this->createMockRequest('/testdotdot', 'GET');

        $response = new \Inertia\Response('Test', [
            'auth.user' => ['name' => 'Jonathan'],
            'auth.user.is_super' => true,
        ], 'app', '123');

        $response = $response->toResponse($request);
        $this->assertInstanceOf(BaseResponse::class, $response);

        $content = $response->getContent();

        $dom = new DOMDocument();
        $dom->loadHTML($content);
        $app = $dom->getElementById('app');
        if($app instanceof DOMElement){
            $json = $app->getAttribute('data-page');
            $this->assertJson($json);
            $page = json_decode($json, true);

            $this->assertSame([
                'auth' => [
                    'user' => [
                        'name' => 'Jonathan',
                        'is_super' => true,
                    ],
                ],
            ], $page['props']);
        } else {
            $this->fail("Could not parse the page object from the response.");
        }
    }
}
