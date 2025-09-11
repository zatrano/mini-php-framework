<?php
namespace App\Http\Controllers;

use Core\View\View;

class HomeController extends Controller
{
    public function index(): void
    {
        View::render('home', [
            'title'    => 'Mini PHP Framework',
            'subtitle' => 'Composer yok • DB yok • SMTP var • CSRF • Parametreli Router • Middleware • Mini Blade'
        ]);
    }

    public function hello(string $name): void
    {
        View::render('hello', ['name' => $name, 'title' => 'Hello']);
    }

    public function showPost(int $id): void
    {
        View::render('post', ['id' => $id, 'title' => 'Post Detay']);
    }
}
