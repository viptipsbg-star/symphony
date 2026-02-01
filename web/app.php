<?php

use Symfony\Component\ClassLoader\ApcClassLoader;
use Symfony\Component\HttpFoundation\Request;

if (file_exists(__DIR__ . '/../app/bootstrap.php.cache')) {
    $loader = require_once __DIR__ . '/../app/bootstrap.php.cache';
} else {
    // If we don't use bootstrap.php.cache, we need to load autoload.php manually so we get the loader
    /** @var \Composer\Autoload\ClassLoader $loader */
    $loader = require __DIR__ . '/../app/autoload.php';
}

// Enable APC for autoloading to improve performance.
// You should change the ApcClassLoader first argument to a unique prefix
// in order to prevent cache key conflicts with other applications
// also using APC.
/*
$apcLoader = new ApcClassLoader(sha1(__FILE__), $loader);
$loader->unregister();
$apcLoader->register(true);
*/

require_once __DIR__ . '/../app/AppKernel.php';
//require_once __DIR__.'/../app/AppCache.php';

if ($_SERVER['REMOTE_ADDR'] == '127.0.0.1') {
    $kernel = new AppKernel('dev', true);
} else {
    $kernel = new AppKernel('prod', false);
}

$kernel->loadClassCache();
//$kernel = new AppCache($kernel);

// When using the HttpCache, you need to call the method in your front controller instead of relying on the configuration parameter
//Request::enableHttpMethodParameterOverride();
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
