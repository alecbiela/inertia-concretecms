<?php

/**
 * TODO: Transform this into a custom assertable to be attached to Responses?
 */

namespace Inertia\Testing;

use \InvalidArgumentException;

use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Assert as PHPUnit;
use PHPUnit\Framework\AssertionFailedError;
use Package;
use Concrete\Core\Support\Facade\Application;

class AssertableInertia
{
    /** @var string */
    private $component;

    /** @var string */
    private $url;

    /** @var string|null */
    private $version;

    /** @var array */
    // Overloaded props go here (dynamic props are deprecated as of PHP8.2)
    private $data = array();

    /** @var Config */
    private $config;

    /** @var Application */

    // Creates a new instance of this class from a valid inertia page response
    public static function fromResponse($response): self
    {
        try {
            $content = $response->getContent();
            PHPUnit::assertJson($content);
            $page = json_decode($content, true);

            PHPUnit::assertIsArray($page);
            PHPUnit::assertArrayHasKey('component', $page);
            PHPUnit::assertArrayHasKey('props', $page);
            PHPUnit::assertArrayHasKey('url', $page);
            PHPUnit::assertArrayHasKey('version', $page);
        } catch (AssertionFailedError $e) {
            PHPUnit::fail('Not a valid Inertia response.');
        }

        $instance = new self();
        foreach($page['props'] as $name=>$val){
            $instance->$name = $val;
        }
        $instance->component = $page['component'];
        $instance->url = $page['url'];
        $instance->version = $page['version'];

        return $instance;
    }

    public function __construct(){
        $pkg = Package::getByHandle('inertia_ccms_adapter');
        $this->config = $pkg->getFileConfig();
        $this->app = Application::getFacadeApplication();
    }

    // Sets a dynamic prop
    public function __set($name, $value){
        $this->data[$name] = $value;
    }

    // Gets a dynamic prop
    public function __get($name){
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }

        $trace = debug_backtrace();
        trigger_error(
            'Undefined property via __get(): ' . $name .
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE);
        return null;
    }

    // Checks if dynamic prop is set
    public function __isset($name){
        return isset($this->data[$name]);
    }

    // Unsets dynamic prop
    public function __unset($name){
        unset($this->data[$name]);
    }

    public function component(string $value = null, $shouldExist = null): self
    {
        PHPUnit::assertSame($value, $this->component, 'Unexpected Inertia page component.');

        if ($shouldExist || (is_null($shouldExist) && $this->config->get('inertia.testing.ensure_pages_exist') === true)) {
            $a = explode('/', $value);
            $fileName = end( $a );
            try {
                $finder = $this->app->make('inertia.testing.view-finder');
                $finder
                ->path($value)
                ->name($fileName);

                if(!$finder->hasResults()) throw new InvalidArgumentException();
            } catch (InvalidArgumentException $exception) {
                PHPUnit::fail(sprintf('Inertia page component file [%s] does not exist.', $value));
            }
        }

        return $this;
    }

    public function url(string $value): self
    {
        PHPUnit::assertSame($value, $this->url, 'Unexpected Inertia page url.');

        return $this;
    }

    public function version(string $value): self
    {
        PHPUnit::assertSame($value, $this->version, 'Unexpected Inertia asset version.');

        return $this;
    }

    public function toArray()
    {
        $arr = array(
            'component' => $this->component,
            'url' => $this->url,
            'version' => $this->version,
            'props' => []            
        );
        foreach($this->data as $name => $prop){
            $arr['props'][$name] = $prop;
        }

        return $arr;
    }
}