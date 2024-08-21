<?php
namespace Concrete\Package\InertiaCcmsAdapter\Controller\PageType;

use Concrete\Core\Page\Controller\PageTypeController;

defined('C5_EXECUTE') or die('Access Denied');

class Inertia extends PageTypeController {
    
    public function view()
    {
        // Set the $pageSSR variable used in the template for SSR
        $this->set('pageSSR', array());
    }
}
?>