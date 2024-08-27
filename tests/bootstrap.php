<?php
/**
 * Prepare the ConcreteCMS environment for Inertia Unit Testing
 * Derived from ConcreteCMS's own test suite bootstrapper
 * @see https://github.com/concretecms/concretecms/blob/9.3.x/tests/bootstrap.php
 */

use Concrete\Core\Http\Request;
use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Error\Notice;
use Whoops\Handler\PlainTextHandler;
use Whoops\Run;

// Define test constants
putenv('CONCRETE5_ENV=ccm_test');
define('DIR_TESTS', str_replace(DIRECTORY_SEPARATOR, '/', __DIR__));
define('DIR_CONFIG_SITE', DIR_TESTS . '/../application/config');
define('DIR_BASE', dirname(DIR_TESTS).'/..');
define('BASE_URL', 'http://www.dummyco.com/path/to/server');

// Fix for phpstorm + tests run in separate processes
if (!defined('PHPUNIT_COMPOSER_INSTALL')) {
    define('PHPUNIT_COMPOSER_INSTALL', DIR_TESTS . '/../concrete/vendor/autoload.php');
}

// Define concrete5 constants
require DIR_BASE . '/concrete/bootstrap/configure.php';

// Include all autoloaders.
require DIR_BASE_CORE . '/bootstrap/autoload.php';

// Define a fake request
Request::setInstance(new Request(
    [],
    [],
    [],
    [],
    [],
    ['HTTP_HOST' => 'www.requestdomain.com', 'SCRIPT_NAME' => '/path/to/server/index.php']
));

// Begin concrete5 startup.
$app = require DIR_BASE_CORE . '/bootstrap/start.php';
/* @var Concrete\Core\Application\Application $app */

// Unset variables, so that PHPUnit won't consider them as global variables.
unset(
    $app,
    $fs,
    $cn
);

// Autoload the test classes
$classLoader = new \Composer\Autoload\ClassLoader();
$classLoader->addPsr4("Inertia\\Tests\\", DIR_TESTS . '/Tests', true);
$classLoader->addPsr4("Inertia\\Tests\\Stubs\\", DIR_TESTS . '/Tests/Stubs', true);
$classLoader->addPsr4("Inertia\\Tests\\Testing\\", DIR_TESTS . '/Tests/Testing', true);
$classLoader->register();

