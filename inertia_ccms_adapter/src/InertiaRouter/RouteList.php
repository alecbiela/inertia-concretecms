<?php
/**
 * RouteList class - Holds a list of our routes and route groups
 * This is loaded in the package controller's on_start() method
 * The syntax is akin to Laravel routing with wildcard/optional route vars
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

    public function loadRoutes($router)
    {
        $this->_router = $router;
        
        // Instead of Router::inertia() from the documentation, write those routes like:
        // $this->inertia('/about', 'About');

        
    }

    /**
     * Replacement for the Laravel implementation's Router::inertia macro
     */
    private function inertia($uri, $component, $props = []) {
        $route = new Route($this->_router->normalizePath($uri));
        $route->setMethods(['GET', 'HEAD']);
        $route->setAction(function () use ($component, $props) {
            return Inertia::render($component, $props);
        });

        return new RouteBuilder($this->_router, $route);
    }
}