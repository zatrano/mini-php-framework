<?php
// Base path aware URL helpers (work in root or any subfolder)
if (!function_exists('base_path_prefix')) {
    function base_path_prefix(): string {
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $base = rtrim(str_replace('\\', '/', dirname($script)), '/');
        return ($base === '' || $base === '/') ? '' : $base;
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string {
        $prefix = base_path_prefix();
        $path = '/' . ltrim($path, '/');
        return $prefix . $path;
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string {
        return url($path);
    }
}
