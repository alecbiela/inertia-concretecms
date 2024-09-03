<?php
/**
 * The controller for the page loaded on first visit (before Inertia gets going)
 */
namespace Concrete\Package\InertiaCcmsAdapter\Controller\PageType;

use Concrete\Core\Page\Controller\PageTypeController;

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
     * Overriding this prevents redirecting via 301 or 404 errors
     * when using the home page with an alternate page path (such as a component route)
     * @see Concrete\Core\Page\Controller\PageController
     */
    public function validateRequest()
    {
        if (isset($this->requestValidated)) {
            return $this->requestValidated;
        }

        $valid = true;
        $this->requestValidated = $valid;

        return $valid;
    }
}
?>