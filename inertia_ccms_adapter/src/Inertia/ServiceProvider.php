<?php

namespace Inertia;

use Inertia\Ssr\Gateway;
use Inertia\Ssr\HttpGateway;
use Inertia\Support\Header;
use Inertia\Middleware;
use InertiaRouter\RouteList;

use Package;
use Concrete\Core\Foundation\Service\Provider as BaseServiceProvider;
use Concrete\Core\Http\ServerInterface;

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
         * TODO: console command support
         */

        // $this->registerConsoleCommands();
    }

    protected function registerMiddleware(): void
    {
        $server = $this->app->make(ServerInterface::class);
        $server->addMiddleware($this->app->make(Middleware::class));
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

    protected function mergeConfigFrom($file, $key){
        // Load configuration values from file
        $cfg = include $file;
        $pkg = Package::getByHandle('inertia_ccms_adapter');
        $pkg->getFileConfig()->save($key, $cfg);
    }
}
