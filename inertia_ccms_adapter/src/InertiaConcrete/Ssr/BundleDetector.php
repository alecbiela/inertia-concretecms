<?php

namespace InertiaConcrete\Ssr;

use Package;

class BundleDetector
{
    /**
     * Checks to see if an SSR bundle exists
     * Will check the following locations (in order) for a bundle:
     * 1. Any custom path supplied to the config value "inertia.ssr.bundle"
     * 2. The bootstrap/ssr folder (relative to package root) for a module JS file
     * 3. The bootstrap/ssr folder (relative to package root) for a standard JS file
     * 4. A ssr.js file inside 
     * If 
     */
    public function detect(): bool
    {
        $pkg = Package::getByHandle('inertia_ccms_adapter');
        $config = $pkg->getFileConfig();
        $packagePath = $pkg->getRelativePath();
        $locations = array(
            $config->get('inertia.ssr.bundle'),
            $packagePath.'/bootstrap/ssr/ssr.mjs',
            $packagePath.'/bootstrap/ssr/ssr.js',
            $packagePath.'/themes/inertia/js/ssr.js'
        );
        foreach($locations as $loc){
            if(file_exists($loc)) return true;
        }
        return false;
    }
}
