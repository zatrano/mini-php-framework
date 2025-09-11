<?php
declare(strict_types=1);
ini_set('display_errors', '1');
error_reporting(E_ALL);
session_start();

// Include helpers first so views can use url()/asset()
require_once __DIR__ . '/core/Support/helpers.php';

// Safer autoloader (Core\* => /core/*, App\* => /app/*)
spl_autoload_register(function ($class) {
    $class = ltrim($class, '\\');
    $classPath = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    $base = __DIR__;

    $direct = $base . '/' . $classPath;
    if (is_file($direct)) { require_once $direct; return; }

    if (strpos($class, 'Core\\') === 0) {
        $file = $base . '/core/' . substr($classPath, strlen('Core/'));
        if (is_file($file)) { require_once $file; return; }
    }

    if (strpos($class, 'App\\') === 0) {
        $file = $base . '/app/' . substr($classPath, strlen('App/'));
        if (is_file($file)) { require_once $file; return; }
    }
});

date_default_timezone_set('Europe/Istanbul');

$config = [
    'app'  => include __DIR__ . '/config/app.php',
    'mail' => include __DIR__ . '/config/mail.php',
];

$request  = new \Core\Http\Request();
$response = new \Core\Http\Response();
$router   = new \Core\Routing\Router($request, $response);

$kernel = new \App\Http\Middleware\Kernel();
$kernel->register($router);

require __DIR__ . '/routes/web.php';

$router->dispatch();
