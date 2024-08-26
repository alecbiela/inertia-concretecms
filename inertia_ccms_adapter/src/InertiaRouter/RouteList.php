<?php
/**
 * RouteList class - Holds a list of our routes and route groups
 * The uri syntax is akin to Laravel routing with wildcard/optional route vars
 * @see https://documentation.concretecms.org/developers/framework/routing/introduction
 */
namespace InertiaRouter;

defined('C5_EXECUTE') or die('Access Denied');

use Package;
use Concrete\Core\Routing\RouteListInterface;
use Concrete\Core\Routing\Router;
use Concrete\Core\Routing\Route;
use Inertia\Inertia;

class RouteList implements RouteListInterface
{
    private $_router;
    private $_pkg;

    /**
     * Registers API routes with the ConcreteCMS router
     * @param Router $router The CMS router instance to attach routes to
     * @return void
     */
    public function loadRoutes(Router $router): void
    {
        $this->_router = $router;
        
        // Instead of Router::inertia() from the documentation, write those routes like:
        // $this->inertia('/about', 'About');

        // Route groups are hierarchical, so we have the one "big" group with smaller groups under it
        $allRoutes = $router->buildGroup();

        $allRoutes->buildGroup()
        //->addMiddleware(MyMiddlewareClass::class)
        ->routes('web.php', 'inertia_ccms_adapter');

        $allRoutes->buildGroup()
        ->setPrefix('/api')
        ->routes('api.php', 'inertia_ccms_adapter');
    }

/**
 * TODO: Create a new class extending from the default Router to include ::inertia macro support
 */
    /**
     * Replacement for the Laravel implementation's Router::inertia macro
     * @param string      $uri        The URI slug to trigger this route
     * @param string      $component  The name of the component to render on the frontend
     * @param array|null  $props      A KV-array of props to pass to the component
     */
    private function inertia(string $uri, string $component, array $props = []): RouteBuilder 
    {
        $route = new Route($this->_router->normalizePath($uri));
        $route->setMethods(['GET', 'HEAD']);
        $route->setAction(function () use ($component, $props) {
            return Inertia::render($component, $props);
        });

        return new RouteBuilder($this->_router, $route);
    }
}