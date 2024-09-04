<?php

namespace InertiaRouter;
defined('C5_EXECUTE') or die('Access Denied');

use Concrete\Core\Support\Facade\Application;

class InertiaRouter {

    /**
     * Get a route URI by its name (Don't think Concrete has this already?)
     * @param string $routeName The name of the route
     * @return string|null The URI of the route, or null if none was found
     */
    public static function getUriByName(string $routeName) {
        $app = Application::getFacadeApplication();
        $router = $app->make('router');
        $route = $router->getRoutes()->get('routename');
        return (isset($route)) ? $route->getPath() : null;
    }
}