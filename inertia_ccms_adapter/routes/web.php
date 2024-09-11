<?php 
    /**
     * Web route list - similar to Laravel's web.php default route file
     * Controllers are not namespaced, and will require full controller paths
     * @var Concrete\Core\Application\Application $app    - The Application Instance
     * @var Concrete\Core\Routing\Router          $router - The Router Instance
     */
    defined('C5_EXECUTE') or die('Access Denied');

    use Inertia\Inertia;

    // Shorthand routes
    // inertia_route('/uri/of/route', 'ComponentName', ['propName' => 'Prop Value'], $router);

    // $router->get('/', function() { 
    //     // Shorthand using helpers
    //     // return inertia('HomePage', ['propTest' => 'This is a prop']);

    //     return Inertia::render('HomePage', [
    //         'propTest' => 'This is a test prop'
    //     ]);
    // });


