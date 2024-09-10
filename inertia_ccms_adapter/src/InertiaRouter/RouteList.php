<?php
/**
 * RouteList class - Holds a list of our routes and route groups
 * The uri syntax is akin to Laravel routing with wildcard/optional route vars
 * @see https://documentation.concretecms.org/developers/framework/routing/introduction
 */
namespace InertiaRouter;

defined('C5_EXECUTE') or die('Access Denied');

use Concrete\Core\Routing\RouteListInterface;
use Concrete\Core\Routing\Router;
use InertiaRouter\InertiaAuthMiddleware;
use Concrete\Core\Http\ResponseFactory;
use Concrete\Core\Support\Facade\Application;

class RouteList implements RouteListInterface
{

    /**
     * Registers API routes with the ConcreteCMS router
     * @param Router $router The CMS router instance to attach routes to
     * @return void
     */
    public function loadRoutes(Router $router): void
    {
        // Route groups are hierarchical, so we have the one "big" group with smaller groups under it
        $allRoutes = $router->buildGroup();

        $allRoutes->buildGroup()
        //->addMiddleware(MyMiddlewareClass::class)
        ->routes('web.php', 'inertia_ccms_adapter');

        $allRoutes->buildGroup()
        ->addMiddleware(InertiaAuthMiddleware::class)
        ->routes('auth.php', 'inertia_ccms_adapter');

        $allRoutes->buildGroup()
        ->setPrefix('/api')
        ->routes('api.php', 'inertia_ccms_adapter');
    }
}