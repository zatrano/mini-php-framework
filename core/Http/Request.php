<?php
namespace Core\Http;

class Request
{
    public function method(): string {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function path(): string {
        // Prefer rewrite-passed param (robust)
        if (isset($_GET['__url'])) {
            $p = '/' . ltrim((string)$_GET['__url'], '/');
            return rtrim($p, '/') ?: '/';
        }

        // Fallback: derive from REQUEST_URI and strip base dir
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $q = strpos($uri, '?');
        if ($q !== false) $uri = substr($uri, 0, $q);

        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $base = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
        if ($base && strpos($uri, $base) === 0) {
            $uri = substr($uri, strlen($base));
        }

        $uri = '/' . ltrim($uri, '/');
        return rtrim($uri, '/') ?: '/';
    }

    public function input(string $key, $default=null) {
        if ($key === '__url') return null; // guard
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    public function all(): array {
        $all = array_merge($_GET, $_POST);
        unset($all['__url']);
        return $all;
    }

    public function files(): array { return $_FILES ?? []; }

    public function header(string $key, $default=null) {
        $h = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        return $_SERVER[$h] ?? $default;
    }
}
