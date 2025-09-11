<?php
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MailController;
use App\Http\Middleware\VerifyCsrfToken;

/** @var \Core\Routing\Router $router */

$router->get('/', [HomeController::class, 'index']);

// Parametreli rotalar örneği
$router->get('/hello/{name}', [HomeController::class, 'hello']);
$router->get('/post/{id:\d+}', [HomeController::class, 'showPost']);

// İletişim formu
$router->get('/contact', [MailController::class, 'form']);
$router->post('/contact', [MailController::class, 'send'])->middleware([VerifyCsrfToken::class]);

// Basit JSON örneği
$router->get('/api/ping', function($req, $res) {
    $res->json(['pong' => time()]);
});
