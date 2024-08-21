<?php
/**
 * RouteList class - Holds a list of our routes and route groups
 * This is loaded in the package controller's on_start() method
 * The uri syntax is akin to Laravel routing with wildcard/optional route vars
 * Package handle appended to each route, see below
 */
namespace InertiaRouter;

defined('C5_EXECUTE') or die('Access Denied');

use Concrete\Core\Routing\RouteListInterface;
use Concrete\Core\Routing\Router;
use Concrete\Core\Routing\Route;
use InertiaConcrete\Inertia;

class RouteList implements RouteListInterface
{
    private $_router;

    public function loadRoutes(Router $router): void
    {
        $this->_router = $router;
        
        // Instead of Router::inertia() from the documentation, write those routes like:
        // $this->inertia('/about', 'About');

        
    }

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