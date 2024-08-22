<?php
namespace Concrete\Package\InertiaCcmsAdapter\Controller\PageType;

use Concrete\Core\Page\Controller\PageTypeController;

defined('C5_EXECUTE') or die('Access Denied');

class Inertia extends PageTypeController {
    
    public function view()
    {
        // Set the $page variable used in the template
        $page = $this->request->request->get('page');
        if(!isset($page)) $page = array();
        $this->set('page', $page);
    }
}
?>