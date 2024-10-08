<?php
namespace Concrete\Package\InertiaCcmsAdapter\Theme\Inertia;

use Concrete\Core\Page\Theme\Theme;

class PageTheme extends Theme
{
    protected $pThemeGridFrameworkHandle = 'bootstrap5';
    
    public function getThemeName()
    {
        return t('Inertia.js Theme');
    }
    
    public function getThemeDescription()
    {
        return t('Foundation for Inertia.js frontend display.');
    }
}