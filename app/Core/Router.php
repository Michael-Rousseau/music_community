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
                array_shift($matches); // remove full match

                if (is_string($callback)) {
                    list($controller, $action) = explode('@', $callback);
                    $controller = new $controller();
                    return call_user_func_array([$controller, $action], $matches);
                }
            }
        }
        echo "No url found for uri $uri";
        http_response_code(404);
        echo "404 Not Found";
    }
}
