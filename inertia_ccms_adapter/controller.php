<?php
/**
 * @package Inertia CCMS Adapter
 * @author Alec Bielanos
 * @license Apache-2.0
 */
namespace Concrete\Package\InertiaCcmsAdapter;

defined('C5_EXECUTE') or die('Access Denied.');

// Aliases are defined in concrete/config/app.php
use Asset;
use AssetList;
use BlockType;
use Package;
use SinglePage;
use InertiaRouter\RouteList;
use InertiaConcrete\Middleware as InertiaMiddleware;
use InertiaConcrete\ServiceProvider as InertiaServiceProvider;
use Concrete\Core\Http\ServerInterface;

class Controller extends Package
{
    protected $pkgHandle = 'inertia_ccms_adapter';
    protected $appVersionRequired = '9.0.0';
    protected $phpVersionRequired = '7.4.0';
    protected $pkgVersion = '0.0.1';

    public function getPackageDescription()
    {
        return t('An adapter for Inertia.js to the ConcreteCMS backend. (Unofficial)');
    }

    public function getPackageName()
    {
        return t('Inertia.js Adapter');
    }

    protected $pkgAutoloaderRegistries = [
        'src/InertiaRouter' => 'InertiaRouter',
        'src/InertiaConcrete' => 'InertiaConcrete'
    ];

    private function installOrUpgrade($pkg = null){
        if(is_null($pkg)) $pkg = Package::getByHandle($this->pkgHandle);
    }

    /**
     * Runs whenever the package is installed to a site for the first time
     */
    public function install()
    {
        if (version_compare(phpversion(), $this->phpVersionRequired, '<')) {
            throw new \Exception(t('This package requires a minimum PHP version of '.$this->phpVersionRequired.' to run correctly.'));
        }
        $pkg = parent::install();
        $this->installOrUpgrade($pkg);
    }

    /**
     * Runs when the package is updated to a new version through the CMS
     */
    public function upgrade(){
        parent::upgrade();
        $this->installOrUpgrade();
    }

    /**
     * Runs when this package is uninstalled from the CMS
     */
    public function uninstall(){
        parent::uninstall();
    }

    /**
     * Code to bootstrap onto the application startup routine
     * before any blocks, pages, etc. are loaded.
     */
    public function on_start(){
        // Load up our routes from InertiaRouter
        $router = $this->app->make('router');
        $list = new RouteList();
        $list->loadRoutes($router);

        // Add the Inertia middleware to the middleware chain
        $server = $this->app->make(ServerInterface::class);
        $server->addMiddleware($this->app->make(InertiaMiddleware::class));

        // Register the Inertia service provider
        $list = $this->app->make('Concrete\Core\Foundation\Service\ProviderList');
        $list->registerProvider(InertiaServiceProvider::class);
    }
}