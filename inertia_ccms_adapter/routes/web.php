<?php 
    /**
     * Web route list - similar to Laravel's api.php default route file
     * Controllers are not namespaced, and will require full controller paths
     * @var Concrete\Core\Application\Application $app    - The Application Instance
     * @var Concrete\Core\Routing\Router          $router - The Router Instance
     */
    defined('C5_EXECUTE') or die('Access Denied');

    use Inertia\Inertia;
    
    $router->get('/', function() { 
        return Inertia::render('HomePage', [
            'propTest' => 'This is a prop'
        ]);
    });

