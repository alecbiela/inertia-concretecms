<?php

namespace Inertia\Ssr;

use Exception;
use Concrete\Src\Routing\Router as Http;
use Package;

class HttpGateway implements Gateway
{
    /**
     * Dispatch the Inertia page to the Server Side Rendering engine.
     */
    public function dispatch(array $page): ?Response
    {
        $config = Package::getByHandle('inertia_ccms_adapter')->getFileConfig();

        if (! ($config->get('inertia.ssr.enabled') === true) || ! (new BundleDetector())->detect()) {
            return null;
        }

        $url = str_replace('/render', '', $config->get('inertia.ssr.url','http://127.0.0.1:13714')).'/render';

        try {
            $response = Http::post($url, $page)->throw()->json();
        } catch (Exception $e) {
            return null;
        }

        if (is_null($response)) {
            return null;
        }

        return new Response(
            implode("\n", $response['head']),
            $response['body']
        );
    }
}
