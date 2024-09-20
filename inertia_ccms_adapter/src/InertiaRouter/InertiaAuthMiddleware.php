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
     * @throws \InvalidArgumentException
     * @return Response
     */
    public function process(Request $request, DelegateInterface $frame)
    {
        $app = Application::getFacadeApplication();
        $u = $app->make(User::class);
        $pkg = Package::getByHandle('inertia_ccms_adapter');
        $gName = $pkg->getFileConfig()->get('inertia.user_settings.auth_user_group');

        // Make sure the user group in the config is a valid user group in the CMS
        if(isset($gName)) {
            $repository = $app->make(GroupRepository::class);
            $group = $repository->getGroupByName($gName);
            if(!is_object($group)){
                throw new \InvalidArgumentException('Auth group specified in Inertia config is not a valid CMS User Group.');
            }

            if($u->inGroup($group)){
                return $frame->next($request);
            }

        } else if($u->isRegistered()) {
            // No auth group specified, so just being registered is enough
            return $frame->next($request);
        }

        // No auth checks passed, so return forbidden
        $rf = $app->make(ResponseFactory::class);
        return $rf->forbidden($request->getPath());
    }
}