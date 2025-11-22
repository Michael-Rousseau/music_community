<?php
namespace Core;

class Router {
    private $routes = ['GET' => [], 'POST' => []];

    public function get($pattern, $callback) {
        $this->routes['GET'][$pattern] = $callback;
    }

    public function post($pattern, $callback) {
        $this->routes['POST'][$pattern] = $callback;
    }

    public function dispatch($uri, $method) {
        foreach ($this->routes[$method] as $pattern => $callback) {
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);

                // NEW: handle closures directly
                if (is_callable($callback)) {
                    return call_user_func_array($callback, $matches);
                }

                // Handle "Controller@method" strings
                if (is_string($callback)) {
                    list($class, $method) = explode('@', $callback);
                    $controller = new $class();
                    return call_user_func_array([$controller, $method], $matches);
                }
            }
        }

        http_response_code(404);
        echo "404 Not Found<br>";
        echo "No URL found for $uri";
    }
}
