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
        // Handle BASE_URL stripping
        if (defined('BASE_URL') && BASE_URL !== '/') {
            if (strpos($uri, BASE_URL) === 0) {
                $uri = substr($uri, strlen(BASE_URL));
            }
        }

        $uri = rtrim($uri, '/');

        foreach ($this->routes[$method] as $pattern => $callback) {
            $pattern = '#^' . $pattern . '$#';
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);

                if (is_string($callback)) {
                    list($controller, $action) = explode('@', $callback);
                    
                    global $pdo; 
                    if(class_exists($controller)) {
                        $controllerInstance = new $controller($pdo);
                        return call_user_func_array([$controllerInstance, $action], $matches);
                    } else {
                        die("Controller class $controller not found.");
                    }
                    
                } elseif (is_callable($callback)) {
                    return call_user_func_array($callback, $matches);
                }
            }
        }

        http_response_code(404);
        echo "404 Not Found<br>";
        echo "The requested URL was not found on this server.";
    }
}
