<?php
/**
 * The controller for the page loaded on first visit (before Inertia gets going)
 */
namespace Concrete\Package\InertiaCcmsAdapter\Controller\PageType;

use Concrete\Core\Page\Controller\PageTypeController;
use Concrete\Core\Http\Request;
use Inertia\Support\Header;
use Illuminate\Support\Str;
use Inertia\Inertia as InertiaRenderer;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Page;
use Concrete\Core\Http\ResponseFactory;

defined('C5_EXECUTE') or die('Access Denied');

class Inertia extends PageTypeController {
    
    public function on_start()
    {
        /**
         * This is experimental functionality to maybe incorporate Inertia with the visual CMS Sitemap someday...
         * So far, the main hurdle is getting custom props passed with each page, especially collections from the DB
         * Page props are dynamically generated from ANY page attribute that is defined on this page object
         * One of these is REQUIRED - 'inertia_component_name' - which is the component we are going to render
         */
        // $request = Request::getInstance();
        // $c = Page::getCurrentPage();
        // $rf = $this->app->make(ResponseFactory::class);
        // $props = [];
        // $attrs = $c->getSetCollectionAttributes();
        // foreach($attrs as $a) {
        //     $ak = $a->getAttributeKeyHandle();
        //     $at = $a->getAttributeTypeHandle();

        //     switch($at) {
        //         case 'file': // pass the file's path

        //             break;
        //         default: // text and text-based types, just pass it through
        //             $props[$ak] = $c->getAttribute($ak);
        //         break;
        //     }
            
        // }

        // if(isset($props['inertia_component_name']) && $props['inertia_component_name'] !== ''){

        //     // Set up the render
        //     $component = $props['inertia_component_name'];
        //     unset($props['inertia_component_name']);
        //     $response = InertiaRenderer::render($component, $props);
        //     $props = $response->resolvePartialProps($request, $props);
        //     $props = $response->resolveAlwaysProps($props);
        //     $props = $response->evaluateProps($props, $request);

        //     $page = [
        //         'component' => $component,
        //         'props' => $props,
        //         'url' => Str::start(Str::after($request->getRequestUri(), $request->getSchemeAndHttpHost()), '/'),
        //         'version' => InertiaRenderer::getVersion(),
        //     ];

        //     // If Inertia, send a JSON response with the data
        //     if ($request->headers->get(Header::INERTIA)) {
        //         return $rf->json($page, 200, [Header::INERTIA => 'true']);
        //     }

        //     // If not Inertia (e.g. first-time page load) allow response chain to continue
        //     $request->request->add(['viewParams' => $response->getViewData() + ['page' => $page]]);
        //     $request->request->add(['rootView' => $response->getRootView()]);

        // } else {

        //     // Error: No component name was specified on the CMS page, so we don't know what to render.
        //     $content = json_encode(['error' => 'A page was found, but an Inertia Component Name was not defined.']);
        //     if($request->headers->get(Header::INERTIA)){
        //         return $rf->error($content, 422, [Header::INERTIA => true]);
        //     } else {
        //         return $rf->notFound($content);
        //     }

        // }
    }

    public function view()
    {
        // Get the view parameters that were set up when rendering the Inertia Component
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
     * If it's not, we tell Concrete "No, this really should be a 404 page."
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