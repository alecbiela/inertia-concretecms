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
use Page;
use PageTheme;
use PageType;
use PageTemplate;
use SinglePage;
use Concrete\Core\Page\Theme\Theme;
use Concrete\Core\Site\Service as SiteService;
use Inertia\ServiceProvider as InertiaServiceProvider;

class Controller extends Package
{
    protected $pkgHandle = 'inertia_ccms_adapter';
    protected $appVersionRequired = '9.0.0';
    protected $phpVersionRequired = '7.4.0';
    protected $pkgVersion = '0.1.0';

    public function getPackageDescription()
    {
        return t('An adapter for Inertia.js to the Concrete CMS backend. (Unofficial)');
    }

    public function getPackageName()
    {
        return t('Inertia.js');
    }

    protected $pkgAutoloaderRegistries = [
        'src/InertiaRouter' => 'InertiaRouter',
        'src/Inertia' => 'Inertia'
    ];

    private function installOrUpgrade($pkg = null){
        if(is_null($pkg)) $pkg = Package::getByHandle($this->pkgHandle);

        // Install page theme if not installed
        $theme = Theme::getByHandle('inertia');
        if(!is_object($theme)){
            $theme = Theme::add('inertia',$pkg);
        }

        // Install the page type
        $pageType = PageType::getByHandle('inertia');
        if (!is_object($pageType)) {
            $pageType = PageType::add(array(
                'handle' => 'inertia',
                'name' => 'Inertia'
            ), $pkg);
        }

        // Install the page templates if not installed
        $itpl = PageTemplate::getByHandle('inertia');
        if(!is_object($itpl)){
            $itpl = PageTemplate::add('inertia', 'Inertia', FILENAME_PAGE_TEMPLATE_DEFAULT_ICON, 'inertia_ccms_adapter');
        }
        $stpl = PageTemplate::getByHandle('standard');
        if(!is_object($stpl)){
            $stpl = PageTemplate::add('standard', 'Standard', FILENAME_PAGE_TEMPLATE_DEFAULT_ICON, 'inertia_ccms_adapter');
        }

        // Set the home page's type/template to the new Inertia ones
        $hp = Page::getByID(Page::getHomePageID());
        $hp->setPageType($pageType);
        $hp->update([
            'cCacheFullPageContent'=> 0,
            'pTemplateID' => $itpl->getPageTemplateID()
        ]);

        // Set this theme as active
        $site = $this->app->make(SiteService::class)->getDefault();
        $theme->applyToSite($site);

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
        // Register the Inertia service provider
        $list = $this->app->make('Concrete\Core\Foundation\Service\ProviderList');
        $list->registerProvider(InertiaServiceProvider::class);
    }
}