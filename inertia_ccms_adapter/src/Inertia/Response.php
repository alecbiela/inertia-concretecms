<?php

namespace Inertia;

use Closure;
use Core;
use Page;
use Inertia\Support\Header;
use Concrete\Core\Utility\Service\Arrays as ArraysService;
use Illuminate\Support\Str;
use Concrete\Core\Http\Request;
use Concrete\Core\Support\Facade\Application as App;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;
use Concrete\Core\Http\ResponseFactory;

class Response implements Responsable
{
    use Macroable;

    protected $component;
    protected $props;
    protected $rootView;
    protected $version;
    protected $viewData = [];
    protected $app;

    /**
     * @param array|Arrayable $props
     */
    public function __construct(string $component, array $props, string $rootView = 'app', string $version = '')
    {
        $this->component = $component;
        $this->props = $props instanceof Arrayable ? $props->toArray() : $props;
        $this->rootView = $rootView;
        $this->version = $version;
        $this->app = App::getFacadeApplication();
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

        // Try replacing getPath with getRequestUri() to accommodate for subpath
        $page = [
            'component' => $this->component,
            'props' => $props,
            'url' => Str::start(Str::after($request->getRequestUri(), $request->getSchemeAndHttpHost()), '/'),
            'version' => $this->version,
        ];

        /**
         * Use the Concrete CMS Response Factory to build and send a response
         */
        $rf = \Core::make(ResponseFactory::class);

        // If Inertia, send a JSON response with the data
        if ($request->headers->get(Header::INERTIA)) {
            return $rf->json($page, 200, [Header::INERTIA => 'true']);
        }

        // If not Inertia (e.g. first-time page load) send a CMS page response
        $request->request->add($this->viewData + ['page' => $page]);
        $request->request->add(['rootView'=>$this->rootView]);

        $c = Page::getByPath($request->getPath());
        if(is_null($c->getCollectionID())) $c = Page::getByID(Page::getHomePageID());
        $controller = $c->getPageController();
        $request->setCurrentPage($c);

        return $rf->controller($controller);
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

        if($only){
            // Functionally equivalent to Arr::only($props, $only)
            // Taken from Laravel\Framework\Illuminate\Collections\Arr (MIT License)
            // https://github.com/translation/laravel/blob/master/LICENSE
            $props = array_intersect_key($props, array_flip((array) $only));
        }

        if ($except) {
            // Functionally equivalent to Arr::forget($props, $except)
            // Taken from Laravel\Framework\Illuminate\Collections\Arr (MIT License)
            // https://github.com/translation/laravel/blob/master/LICENSE
            $original = &$props;
            $keys = (array) $except;

            foreach ($keys as $key) {
                // if the exact key exists in the top-level, remove it
                //if (static::exists($props, $key)) {
                if(array_key_exists($key, $props)) {
                    unset($props[$key]);
                    continue;
                }

                $parts = explode('.', $key);

                // clean up before each pass
                $props = &$original;

                while (count($parts) > 1) {
                    $part = array_shift($parts);
                    
                    if (isset($props[$part]) && is_array($props[$part]) || $props[$part] instanceof ArrayAccess) {
                        $props = &$props[$part];
                    } else {
                        continue 2;
                    }
                }

                unset($props[array_shift($parts)]);
            }
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
                // Functionally equivalent to Arr::set($props, $key, $value)
                $array = &$props;
                $subKey = $key;

                if (is_null($subKey)) {
                    return $array = $value;
                }

                $keys = explode('.', $subKey);

                foreach ($keys as $i => $subKey) {
                    if (count($keys) === 1) {
                        break;
                    }

                    unset($keys[$i]);

                    // If the key doesn't exist at this depth, we will just create an empty array
                    // to hold the next value, allowing us to create the arrays to hold final
                    // values at the correct depth. Then we'll keep digging into the array.
                    if (! isset($array[$subKey]) || ! is_array($array[$subKey])) {
                        $array[$subKey] = [];
                    }

                    $array = &$array[$subKey];
                }

                $array[array_shift($keys)] = $value;            

                unset($props[$key]);
            } else {
                $props[$key] = $value;
            }
        }

        return $props;
    }
}
