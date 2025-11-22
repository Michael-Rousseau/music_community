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
        // -------------------------
        // Handle BASE_URL automatically
        // -------------------------
        if (defined('BASE_URL') && BASE_URL !== '/') {
            if (strpos($uri, BASE_URL) === 0) {
                $uri = substr($uri, strlen(BASE_URL));
            }
        }

        // Remove trailing slash
        $uri = rtrim($uri, '/');

        foreach ($this->routes[$method] as $pattern => $callback) {
            $pattern = '#^' . $pattern . '$#';
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // remove full match

                if (is_string($callback)) {
                    list($controller, $action) = explode('@', $callback);
                    $controller = new $controller();
                    return call_user_func_array([$controller, $action], $matches);
                } elseif (is_callable($callback)) {
                    return call_user_func_array($callback, $matches);
                }
            }
        }

        http_response_code(404);
        echo "404 Not Found<br>";
        echo "No URL found for URI $uri";
    }
}
