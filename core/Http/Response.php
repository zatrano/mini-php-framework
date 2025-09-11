<?php
namespace Core\Http;

class Response
{
    public function json(array $data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function redirect(string $to, int $status = 302): void {
        http_response_code($status);
        header("Location: $to");
        exit;
    }
}
