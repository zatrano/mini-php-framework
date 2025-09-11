<?php
namespace Core\View;

class View
{
    public static string $basePath = __DIR__ . '/../../resources/views';

    public static function render(string $view, array $data = []): void
    {
        $layout = self::$basePath . '/layout.php';
        if (!file_exists($layout)) { http_response_code(500); echo "Layout missing"; return; }
        extract($data, EXTR_SKIP);
        $content = self::renderPartial($view, $data, true);
        include $layout;
    }

    public static function renderPartial(string $view, array $data = [], bool $return=false): ?string
    {
        $file = self::$basePath . '/' . $view . '.php';
        if (!file_exists($file)) { return "View not found: $view"; }
        extract($data, EXTR_SKIP);
        ob_start();
        include $file;
        $out = ob_get_clean();
        return $return ? $out : print $out;
    }

    // Minimal blade-like: {{ var }} and @include('partial')
    public static function blade(string $template, array $data = []): string
    {
        // Replace {{ var }}
        $tpl = preg_replace_callback('/\{\{\s*(.+?)\s*\}\}/', function($m) use ($data){
            $key = trim($m[1]);
            return htmlspecialchars($data[$key] ?? '', ENT_QUOTES, 'UTF-8');
        }, $template);

        // Handle @include('partial')
        $tpl = preg_replace_callback("/@include\('(.+?)'\)/", function($m) use ($data){
            $file = self::$basePath . '/' . $m[1] . '.php';
            if (!file_exists($file)) return '';
            ob_start();
            extract($data, EXTR_SKIP);
            include $file;
            return ob_get_clean();
        }, $tpl);

        return $tpl;
    }

    public static function csrf_field(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(16));
        }
        $token = $_SESSION['_csrf'];
        return '<input type="hidden" name="_csrf" value="'.htmlspecialchars($token, ENT_QUOTES, 'UTF-8').'">';
    }

    public static function csrf_token(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(16));
        }
        return $_SESSION['_csrf'];
    }
}
