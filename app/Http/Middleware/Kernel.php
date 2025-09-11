<?php
namespace App\Http\Middleware;

use Core\Routing\Router;

class Kernel
{
    public function register(Router $router): void
    {
        // Buraya global middleware ekleyebilirsin.
        // Örnek: RateLimiter, MaintenanceMode vs.
        // Route bazlı middleware zaten routes/web.php'de gösterildi.
    }
}
