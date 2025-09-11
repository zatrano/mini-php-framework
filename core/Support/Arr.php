<?php
namespace Core\Support;
final class Arr {
    public static function get(array $a, string $k, $d=null) { return $a[$k] ?? $d; }
}
