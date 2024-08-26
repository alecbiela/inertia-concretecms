<?php

namespace Inertia;

use Closure;
use Core;
use Page;
use Inertia\Support\Header;
use Concrete\Core\Utility\Service\Arrays as Arr;
use Illuminate\Support\Str;
use Concrete\Core\Http\Request;
use Concrete\Core\Support\Facade\Application as App;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;
use Concrete\Core\Http\ResponseFactory;
use Concrete\Core\Application\ApplicationAwareTrait;

class Response implements Responsable
{
    use Macroable, ApplicationAwareTrait;

    protected $component;
    protected $props;
    protected $rootView;
    protected $version;
    protected $viewData = [];

    /**
     * @param array|Arrayable $props
     */
    public function __construct(string $component, array $props, string $rootView = 'app', string $version = '')
    {
        $this->component = $component;
        $this->props = $props instanceof Arrayable ? $props->toArray() : $props;
        $this->rootView = $rootView;
        $this->version = $version;
    }

    /**
     * @param string|array $key
     *
     * @return $this
     */
    public function with($key, $value = null): self
    {
        if (is_array($key)) {
            $this->props = array_merge($this->props, $key);
        } else {
            $this->props[$key] = $value;
        }

        return $this;
    }

    /**
     * @param string|array $key
     *
     * @return $this
     */
    public function withViewData($key, $value = null): self
    {
        if (is_array($key)) {
            $this->viewData = array_merge($this->viewData, $key);
        } else {
            $this->viewData[$key] = $value;
        }

        return $this;
    }

    public function rootView(string $rootView): self
    {
        $this->rootView = $rootView;

        return $this;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function toResponse($request)
    {
        $props = $this->resolvePartialProps($request, $this->props);
        $props = $this->resolveAlwaysProps($props);
        $props = $this->evaluateProps($props, $request);

        $page = [
            'component' => $this->component,
            'props' => $props,
            'url' => Str::start(Str::after($request->getPath(), $request->getSchemeAndHttpHost()), '/'),
            'version' => $this->version,
        ];

        /**
         * Use the Concrete CMS Response Factory to build and send a response
         */
        $rf = Core::make(ResponseFactory::class);

        // If Inertia, send a JSON response with the data
        if ($request->headers->get(Header::INERTIA)) {
            return $rf->json($page, 200, [Header::INERTIA => 'true']);
        }

        // If not Inertia (e.g. first-time page load) send a CMS page response
        $request->request->add($this->viewData + ['page' => $page]);
        $request->request->add(['rootView'=>$this->rootView]);
        $c = Page::getByPath($request->getPath());
        return $rf->collection($c);
    }

    /**
     * Resolve the `only` and `except` partial request props.
     */
    public function resolvePartialProps(Request $request, array $props): array
    {
        $isPartial = $request->headers->get(Header::PARTIAL_COMPONENT) === $this->component;

        if (! $isPartial) {
            return array_filter($props, static function ($prop) {
                return ! ($prop instanceof LazyProp);
            });
        }

        $only = array_filter(explode(',', $request->headers->get(Header::PARTIAL_ONLY, '')));
        $except = array_filter(explode(',', $request->headers->get(Header::PARTIAL_EXCEPT, '')));

        $props = $only ? Arr::only($props, $only) : $props;

        if ($except) {
            Arr::forget($props, $except);
        }

        return $props;
    }

    /**
     * Resolve `always` properties that should always be included on all visits,
     * regardless of "only" or "except" requests.
     */
    public function resolveAlwaysProps(array $props): array
    {
        $always = array_filter($this->props, static function ($prop) {
            return $prop instanceof AlwaysProp;
        });

        return array_merge($always, $props);
    }

    /**
     * Resolve all necessary class instances in the given props.
     */
    public function evaluateProps(array $props, Request $request, bool $unpackDotProps = true): array
    {
        foreach ($props as $key => $value) {

            switch(true){
                case (
                    $value instanceof Closure ||
                    $value instanceof LazyProp ||
                    $value instanceof AlwaysProp 
                    ):
                    $value = App::call($value);
                    break;
                case ($value instanceof PromiseInterface):
                    $value = $value->wait();
                    break;
                case ($value instanceof Arrayable):
                    $value = $value->toArray();
                    break;
                // no "default:" as the default is for $value to remain unchanged
            }

            // if $value is now an array of more props, recursively evaluate them
            if (is_array($value)) {
                $value = $this->evaluateProps($value, $request, false);
            }

            if ($unpackDotProps && str_contains($key, '.')) {
                Arr::set($props, $key, $value);
                unset($props[$key]);
            } else {
                $props[$key] = $value;
            }
        }

        return $props;
    }
}
