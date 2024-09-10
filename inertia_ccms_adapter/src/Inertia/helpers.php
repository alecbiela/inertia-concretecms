<?php
/**
 * These functions are registered as globals and can be used anywhere after the Inertia Service Provider is registered.
 */
if (! function_exists('inertia')) {
    /**
     * Inertia helper.
     *
     * @param  null|string  $component
     * @param  array|\Illuminate\Contracts\Support\Arrayable  $props
     * @return \Inertia\ResponseFactory|\Inertia\Response
     */
    function inertia($component = null, $props = [])
    {
        $instance = \Inertia\Inertia::getFacadeRoot();

        if ($component) {
            return $instance->render($component, $props);
        }

        return $instance;
    }
}

if (! function_exists('inertia_location')) {
    /**
     * Inertia location helper.
     *
     * @param  string  url
     * @return \Symfony\Component\HttpFoundation\Response
     */
    function inertia_location($url)
    {
        $instance = \Inertia\Inertia::getFacadeRoot();

        return $instance->location($url);
    }
}

if (! function_exists('is_inertia')) {
    /**
     * Test if the current request is an Inertia request
     * (Replacement for the Laravel Adapter's Request::inertia macro)
     *
     * @param  \Concrete\Core\Http\Request
     * @return bool
     */
    function is_inertia($req)
    {
        return ($req->headers->get('X-Inertia') == true);
    }
}

if (! function_exists('inertia_route')) {
    /**
     * Replacement for the Laravel implementation's Router::inertia macro
     * @param string      $uri        The URI slug to trigger this route
     * @param string      $component  The name of the component to render on the frontend
     * @param array|null  $props      A KV-array of props to pass to the component
     * @param Router      $router     The router instance passed from the routes file
     */
    function inertia_route(string $uri, string $component, array $props = [], \Concrete\Core\Routing\Router $router): \Concrete\Core\Routing\RouteBuilder 
    {
        $route = new \Concrete\Core\Routing\Route($uri);
        $route->setMethods(['GET', 'HEAD']);
        $route->setAction(function () use ($component, $props) {
            return \Inertia\Inertia::render($component, $props);
        });

        return new \Concrete\Core\Routing\RouteBuilder($router, $route);
    }
}
