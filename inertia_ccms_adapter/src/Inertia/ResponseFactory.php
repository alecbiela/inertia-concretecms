<?php

namespace Inertia;

use Closure;
use Concrete\Core\Utility\Service\Arrays as Arr;
use Inertia\Support\Header;
use Concrete\Core\Support\Facade\Application as App;
use Concrete\Core\Http\Request;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Contracts\Support\Arrayable;

use Concrete\Core\Routing\Redirect;
use Concrete\Core\Routing\RedirectResponse;
use Concrete\Core\Http\ResponseFactory as BaseResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ResponseFactory
{
    use Macroable;

    /** @var string */
    protected $rootView = 'app';

    /** @var array */
    protected $sharedProps = [];

    /** @var Closure|string|null */
    protected $version;

    public function setRootView(string $name): void
    {
        $this->rootView = $name;
    }

    /**
     * @param string|array|Arrayable $key
     */
    public function share($key, $value = null): void
    {
        // Arr is non-static in Concrete CMS
        $ah = new Arr();

        if (is_array($key)) {
            $this->sharedProps = array_merge($this->sharedProps, $key);
        } elseif ($key instanceof Arrayable) {
            $this->sharedProps = array_merge($this->sharedProps, $key->toArray());
        } else {
            $this->sharedProps = $ah->set($this->sharedProps, $key, $value);
        }
    }

    public function getShared(string $key = null, $default = null)
    {
        // Arr is non-static in Concrete CMS
        $ah = new Arr();

        if ($key) {
            return $ah->get($this->sharedProps, $key, $default);
        }

        return $this->sharedProps;
    }

    public function flushShared(): void
    {
        $this->sharedProps = [];
    }

    /**
     * @param Closure|string|null $version
     */
    public function version($version): void
    {
        $this->version = $version;
    }

    public function getVersion(): string
    {
        $version = $this->version instanceof Closure
            ? App::call($this->version)
            : $this->version;

        return (string) $version;
    }

    public function lazy(callable $callback): LazyProp
    {
        return new LazyProp($callback);
    }

    public function always($value): AlwaysProp
    {
        return new AlwaysProp($value);
    }

    /**
     * @param array|Arrayable $props
     */
    public function render(string $component, $props = []): Response
    {
        if ($props instanceof Arrayable) {
            $props = $props->toArray();
        }

        return new Response(
            $component,
            array_merge($this->sharedProps, $props),
            $this->rootView,
            $this->getVersion()
        );
    }

    /**
     * @param string|RedirectResponse $url
     */
    public function location($url): SymfonyResponse
    {
        $app = App::getFacadeApplication();
        $req = Request::getInstance();

        if ((bool)$req->headers->get(Header::INERTIA) === true) {
            return $app->make(BaseResponse::class)->create('', 409, [Header::LOCATION => $url instanceof RedirectResponse ? $url->getTargetUrl() : $url]);
        }

        return $url instanceof RedirectResponse ? $url : Redirect::url($url);
    }
}
