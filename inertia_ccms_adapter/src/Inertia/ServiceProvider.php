<?php

namespace Inertia;

use LogicException;
use ReflectionException;
use Inertia\Ssr\Gateway;
use Inertia\Ssr\HttpGateway;
use Inertia\Support\Header;
use Inertia\Middleware as InertiaMiddleware;

use Concrete\Core\Http\Request;
use Concrete\Core\Routing\Router;
use Concrete\Core\Http\ServerInterface;
use Package;

use InertiaRouter\RouteList;

//use Illuminate\View\FileViewFinder;
//use Illuminate\Testing\TestResponse;

use Concrete\Core\Foundation\Service\Provider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function register(): void
    {
        // Register app singletons to be used with $app->make
        $this->app->singleton(ResponseFactory::class);
        $this->app->bind(Gateway::class, HttpGateway::class);

        $this->mergeConfigFrom(
            __DIR__ . '/../../config/inertia.php', 
            'inertia'
        );

        $this->registerMiddleware();
        $this->registerRoutes();

        // Register global inertia helper functions
        require_once __DIR__ . '/helpers.php';
        
        /**
         * TODO: Testing macros and console command support
         */
        //$this->registerTestingMacros();

        // $this->app->bind('inertia.testing.view-finder', function ($app) {
        //     return new FileViewFinder(
        //         $app['files'],
        //         $app['config']->get('inertia.testing.page_paths'),
        //         $app['config']->get('inertia.testing.page_extensions')
        //     );
        // });

        // $this->registerConsoleCommands();
    }

    protected function registerMiddleware(): void
    {
        $server = $this->app->make(ServerInterface::class);
        $server->addMiddleware($this->app->make(InertiaMiddleware::class));
    }

    protected function registerRoutes(): void 
    {
        $router = $this->app->make('router');
        $list = new RouteList();
        $list->loadRoutes($router);
    }

    protected function registerConsoleCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            Commands\CreateMiddleware::class,
            Commands\StartSsr::class,
            Commands\StopSsr::class,
        ]);
    }

    /**
     * @throws ReflectionException|LogicException
     */
    // protected function registerTestingMacros(): void
    // {
    //     if (class_exists(TestResponse::class)) {
    //         TestResponse::mixin(new TestResponseMacros());

    //         return;
    //     }

    //     throw new LogicException('Could not detect TestResponse class.');
    // }

    private function mergeConfigFrom($file, $key){
        // Load configuration values from file
        $cfg = include $file;
        $pkg = Package::getByHandle('inertia_ccms_adapter');
        $pkg->getFileConfig()->save($key, $cfg);
    }
}
