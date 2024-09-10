<?php 
    /**
     * Auth route list - for defining PAGE routes that require authentication
     * Controllers are not namespaced, and will require full controller paths
     * @var Concrete\Core\Application\Application $app    - The Application Instance
     * @var Concrete\Core\Routing\Router          $router - The Router Instance
     */
    defined('C5_EXECUTE') or die('Access Denied');

    use Inertia\Inertia;

    $router->get('/testauth', function(){
        return Inertia::render('HomePage', [
            'propTest' => 'Authentication is good'
        ]);
    });