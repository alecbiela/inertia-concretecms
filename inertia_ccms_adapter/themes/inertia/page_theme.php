<?php
namespace Concrete\Package\InertiaCcmsAdapter\Theme\Inertia;

use Concrete\Core\Page\Theme\Theme;

class PageTheme extends Theme
{

 public function getThemeName()
 {
     return t('Inertia.js Theme');
 }

 public function getThemeDescription()
 {
     return t('Adapter theme for an Inertia frontend (Vue, Svelte, React, etc.)');
 }
}