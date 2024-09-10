<?php
/**
 * The controller for the page loaded on first visit (before Inertia gets going)
 */
namespace Concrete\Package\InertiaCcmsAdapter\Controller\PageType;

use Concrete\Core\Page\Controller\PageTypeController;
use Concrete\Core\Http\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

defined('C5_EXECUTE') or die('Access Denied');

class Inertia extends PageTypeController {
    
    public function view()
    {
        // View Params are set in Response.php - Array of viewData + page params
        $vp = $this->request->request->get('viewParams');
        if(!isset($vp)) $vp = array();
        foreach($vp as $key => $val) {
            $this->set($key, $val);
        }

        $rootView = $this->request->request->get('rootView');
        if(!isset($rootView)) $rootView = 'app';
        $this->set('rootView', $rootView);
    }

    /**
     * When Concrete can't find a page, it tries to see if the page is really
     * the homepage with some extra action command tagged onto it by running this method.
     * 
     * We need to validate whether this request is to a page defined in our
     * custom routes (routes/*.php files). 
     * If so, Concrete can run this route as an entry point into the Inertia app.
     * If it's not, we tell Concrete "No, this should really be a 404 page."
     * 
     * @return true|false - true if the request maps to a real route, false if 404
     */
    public function validateRequest()
    {
        if (isset($this->requestValidated)) {
            return $this->requestValidated;
        }
        $valid = true;

        $request = Request::getInstance();
        $router = $this->app->make('router');
        try {
            $route = $router->matchRoute($request)->getRoute();
        } catch(ResourceNotFoundException $e){
            // 404
            $valid = false;
        }

        $this->requestValidated = $valid;
        return $valid;
    }
}
?>