<?php

namespace Inertia\Tests\Stubs;

use LogicException;
use Inertia\Inertia;
use Inertia\Middleware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ExampleMiddleware extends Middleware
{
    protected $version;

    /**
     * @var array|mixed
     */
    protected $shared = [];

    public function __construct($version = null, $shared = [])
    {
        $this->version = $version;
        $this->shared = $shared;
    }

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return $this->version;
    }

    /**
     * Merges the existing shared props with the shared props defined in the test class
     * Need to do it this way since the parent middleware is already being called, and calling
     * parent::share() again will wipe out the error bag (since the flash bag was already emptied on the first go around)
     */
    public function share(Request $request): array
    {
        return array_merge(Inertia::getShared(), $this->shared);
    }

    /**
     * By overriding the "onEmptyResponse" middleware method, we can change the behavior of the
     * middleware on an empty response.
     * The default behavior is to redirect the user "back" one level, but the test will verify
     * that we can override it.
     */
    public function onEmptyResponse(Request $request, Response $response): Response
    {
        throw new LogicException('An empty Inertia response was returned.');
    }
}
