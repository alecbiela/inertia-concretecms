<?php

namespace Inertia;

use LogicException;
use ReflectionException;
use Inertia\Ssr\Gateway;
use Inertia\Ssr\HttpGateway;
use Inertia\Support\Header;

use Concrete\Core\Http\Request;
use Concrete\Core\Routing\Router;

//use Illuminate\View\FileViewFinder;
//use Illuminate\Testing\TestResponse;

use Concrete\Core\Foundation\Service\Provider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ResponseFactory::class);
        $this->app->bind(Gateway::class, HttpGateway::class);
        
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
}
