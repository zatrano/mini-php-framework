<?php
namespace App\Http\Middleware;

use Core\Http\Request;
use Core\Http\Response;
use Core\View\View;

class VerifyCsrfToken
{
    public function handle(Request $req, Response $res, callable $next)
    {
        $token = $_POST['_csrf'] ?? '';
        $valid = isset($_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'], $token);

        if (!$valid) {
            http_response_code(419);
            echo "CSRF token mismatch.";
            return null;
        }
        return $next();
    }
}
