<?php
namespace Core\Routing;

use Core\Http\Request;
use Core\Http\Response;

class Router
{
    private $routes = [];
    private $req;
    private $res;

    public function __construct(Request $req, Response $res)
    {
        $this->req = $req;
        $this->res = $res;
    }

    public function get($pattern, $action)
    {
        return $this->add('GET', $pattern, $action);
    }

    public function post($pattern, $action)
    {
        return $this->add('POST', $pattern, $action);
    }

    private function add($method, $pattern, $action)
    {
        $route = new Route($method, $pattern, $action);
        $this->routes[] = $route;
        return $route;
    }

    public function dispatch()
    {
        $reqMethod = $this->req->method();
        $reqPath   = $this->normalize($this->req->path());

        foreach ($this->routes as $route) {
            if ($route->method !== $reqMethod) continue;

            $params = array();
            $regex  = $this->toRegex($route->pattern, $params);

            if (preg_match($regex, $reqPath, $matches)) {
                $args = array();
                foreach ($params as $i => $name) {
                    $args[$name] = isset($matches[$i+1]) ? $matches[$i+1] : null;
                }

                // Build middleware pipeline
                $handler = function() use ($route, $args) {
                    if (is_array($route->action)) {
                        $class  = $route->action[0];
                        $method = $route->action[1];
                        $instance = new $class;
                        return call_user_func_array(array($instance, $method), array_values($args));
                    }
                    return call_user_func_array($route->action, array_values($args));
                };

                $pipeline = array_reverse($route->middleware);
                $next = $handler;
                foreach ($pipeline as $mwClass) {
                    $mw = new $mwClass();
                    $prevNext = $next;
                    $next = function() use ($mw, $prevNext) {
                        return $mw->handle($this->req, $this->res, $prevNext);
                    };
                }

                $result = $next();
                if (is_string($result)) echo $result;
                return;
            }
        }

        http_response_code(404);
        echo "404 Not Found";
    }

    private function normalize($path)
    {
        $path = '/' . ltrim($path, '/');
        return rtrim($path, '/') ?: '/';
    }

    // Convert /post/{id:\d+} or /hello/{name} to regex
    private function toRegex($pattern, &$params)
    {
        $pattern = $this->normalize($pattern);
        $params = array();

        $regex = preg_replace_callback('#\{(\w+)(?::([^}]+))?\}#', function($m) use (&$params){
            $params[] = $m[1];
            $r = isset($m[2]) ? $m[2] : '[^/]+';
            return '(' . $r . ')';
        }, $pattern);

        return '#^' . $regex . '$#u';
    }
}
