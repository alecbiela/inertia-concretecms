<?php 
    defined('C5_EXECUTE') or die('Access Denied');

    /**
     * @var Concrete\Core\Application\Application $app
     * @var Concrete\Core\Routing\Router $router
     * @see src/InertiaRouter/RouteList.php
     */
    use Inertia\Inertia;
    
    $router->get('/', function() { 
            return Inertia::render('HomePage', [
                'propTest' => 'This is a prop'
            ]);
    });

