<?php
namespace InertiaRouter;

defined('C5_EXECUTE') or die('Access Denied');

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Concrete\Core\Http\Middleware\MiddlewareInterface;
use Concrete\Core\Http\Middleware\DelegateInterface;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\User\User;
use Concrete\Core\Http\ResponseFactory;
use Concrete\Core\Package\Package;

class InertiaAuthMiddleware implements MiddlewareInterface {

    /**
     * Handle the incoming request.
     *
     * @return Response
     */
    public function process(Request $request, DelegateInterface $frame)
    {
        $app = Application::getFacadeApplication();
        $u = $app->make(User::class);
        $pkg = Package::getByHandle('inertia_ccms_adapter');
        $authGroup = $pkg->getFileConfig()->get('inertia.user_settings.auth_user_group');

        if($u->isRegistered()){
            // If custom group is NULL, just being registered is enough
            if(!isset($authGroup)) return $frame->next($request);

            // Find out if the user belongs to the custom auth group
            $groups = $u->getUserGroupObjects();
            foreach($groups as $g){
                $gName = $g->getGroupName();
                if($authGroup === $gName) return $frame->next($request);
            }
        }

        $rf = $app->make(ResponseFactory::class);
        return $rf->forbidden($request->getPath());
    }
}