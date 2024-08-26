<?php

namespace Inertia;

use Closure;
use Inertia\Support\Header;
use Concrete\Core\Routing\Redirect;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Concrete\Core\Http\Middleware\MiddlewareInterface;
use Concrete\Core\Http\Middleware\DelegateInterface;

class Middleware implements MiddlewareInterface
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     *
     * @return string|null
     */
    public function version(Request $request)
    {

        if (file_exists($manifest = $_SERVER['DOCUMENT_ROOT'].'/packages/inertia_ccms_adapter/themes/inertia/js/mix-manifest.json')) {
            return md5_file($manifest);
        }

        // TODO: Rework this for Vite-based applications (check all frontend package paths for vue3, svelte, react)
        if (file_exists($manifest = public_path('build/manifest.json'))) {
            return md5_file($manifest);
        }

        return null;
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array
     */
    public function share(Request $request)
    {
        return [
            'errors' => Inertia::always($this->resolveValidationErrors($request)),
        ];
    }

    /**
     * Sets the root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @return string
     */
    public function rootView(Request $request)
    {
        return $this->rootView;
    }

    /**
     * Handle the incoming request.
     *
     * @return Response
     */
    public function process(Request $request, DelegateInterface $frame)
    {
        Inertia::version(function () use ($request) {
            return $this->version($request);
        });

        Inertia::share($this->share($request));
        Inertia::setRootView($this->rootView($request));

        $response = $frame->next($request);

        // If we're passed an Inertia\Response, convert it to a Symfony Response
        $response = ($response instanceof \Inertia\Response === true) ? $response->toResponse($request) : $response;
        $response->headers->set('Vary', Header::INERTIA);

        if (! $request->headers->get(Header::INERTIA)) {
            return $response;
        }

        if ($request->method() === 'GET' && $request->headers->get(Header::VERSION, '') !== Inertia::getVersion()) {
            $response = $this->onVersionChange($request, $response);
        }

        if ($response->isOk() && empty($response->getContent())) {
            $response = $this->onEmptyResponse($request, $response);
        }

        if ($response->getStatusCode() === 302 && in_array($request->method(), ['PUT', 'PATCH', 'DELETE'])) {
            $response->setStatusCode(303);
        }

        return $response;
    }

    /**
     * Determines what to do when an Inertia action returned with no response.
     * By default, we'll redirect the user back to where they came from.
     */
    public function onEmptyResponse(Request $request, Response $response): Response
    {
        return Redirect::back();
    }

    /**
     * Determines what to do when the Inertia asset version has changed.
     * By default, we'll initiate a client-side location visit to force an update.
     */
    public function onVersionChange(Request $request, Response $response): Response
    {
        if ($request->hasSession()) {
            $request->session()->reflash();
        }

        return Inertia::location($request->fullUrl());
    }

    /**
     * Resolves and prepares validation errors in such
     * a way that they are easier to use client-side.
     *
     * @return object
     */
    public function resolveValidationErrors(Request $request)
    {
        if (! $request->hasSession() || ! $request->session()->has('errors')) {
            return (object) [];
        }

        return (object) collect($request->session()->get('errors')->getBags())->map(function ($bag) {
            return (object) collect($bag->messages())->map(function ($errors) {
                return $errors[0];
            })->toArray();
        })->pipe(function ($bags) use ($request) {
            if ($bags->has('default') && $request->headers->get(Header::ERROR_BAG)) {
                return [$request->headers->get(Header::ERROR_BAG) => $bags->get('default')];
            }

            if ($bags->has('default')) {
                return $bags->get('default');
            }

            return $bags->toArray();
        });
    }
}
