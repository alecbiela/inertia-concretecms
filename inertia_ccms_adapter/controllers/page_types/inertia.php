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
        // Set the $page variable used in the template
        $page = $this->request->request->get('page');
        $rootView = $this->request->request->get('rootView');
        if(!isset($page)) $page = array();
        if(!isset($rootView)) $rootView = 'app';
        $this->set('page', $page);
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