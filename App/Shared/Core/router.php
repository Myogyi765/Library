<?php

namespace App\Shared\Core;

class Router
{
    private array $routes = [];
    private $container;

    public function __construct($container = null)
    {
        $this->container = $container;
    }

    public function get(string $path, $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function put(string $path, $handler, array $middleware = []): void
    {
        $this->addRoute('PUT', $path, $handler, $middleware);
    }

    public function delete(string $path, $handler, array $middleware = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    private function addRoute(string $method, string $path, $handler, array $middleware = []): void
    {
        $path = rtrim($path, '/');
        if (empty($path)) {
            $path = '/';
        }
        if ($path[0] !== '/') {
            $path = '/' . $path;
        }
        $pattern = preg_replace('#\{[a-zA-Z0-9_]+\}#', '([^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';

        $this->routes[$method][] = [
            'path'       => $path,
            'pattern'    => $pattern,
            'handler'    => $handler,
            'middleware' => $middleware,
        ];
    }

    public function dispatch(string $uri, string $method = 'GET'): void
    {
        $uri = parse_url($uri, PHP_URL_PATH) ?: '/';
        if ($uri !== '/' && $uri[0] !== '/') {
            $uri = '/' . $uri;
        }

        $basePath = defined('BASE_URL') ? parse_url(BASE_URL, PHP_URL_PATH) : '';
        $basePath = '/' . trim($basePath, '/');

        if (!empty($basePath) && strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }

        if (strpos($uri, '/index.php') === 0) {
            $uri = substr($uri, strlen('/index.php'));
        }

        $uri = rtrim($uri, '/');
        if (empty($uri)) {
            $uri = '/';
        }

        error_log("🔄 Router dispatching: {$method} {$uri} (basePath={$basePath})");

        $matchedRoute = null;
        $params = [];

        foreach ($this->routes[$method] ?? [] as $route) {
            if (preg_match($route['pattern'], $uri, $matches)) {
                array_shift($matches);
                $params = $matches;
                $matchedRoute = $route;
                break;
            }
        }

        if ($matchedRoute === null) {
            error_log("❌ Route not found: {$method} {$uri}");
            http_response_code(404);
            echo '404 Not Found - Route: ' . htmlspecialchars($uri);
            return;
        }

        $middlewareList = $matchedRoute['middleware'] ?? [];
        if (is_array($middlewareList)) {
            foreach ($middlewareList as $middleware) {
                if (is_callable($middleware)) {
                    $result = $middleware($this->container);
                    if ($result === false) {
                        return;
                    }
                    continue;
                }

                if (is_string($middleware) && class_exists($middleware)) {
                    $instance = ($this->container && $this->container->has($middleware))
                        ? $this->container->get($middleware)
                        : new $middleware();

                    if ($instance instanceof \App\Shared\Core\Middleware\MiddlewareInterface) {
                        if (!$instance->handle()) {
                            return;
                        }
                    } else {
                        error_log("❌ Middleware must implement MiddlewareInterface: {$middleware}");
                        http_response_code(500);
                        echo 'Invalid middleware.';
                        return;
                    }
                    continue;
                }

                error_log("❌ Invalid middleware type: " . gettype($middleware));
                http_response_code(500);
                echo 'Invalid middleware.';
                return;
            }
        }

        $handler = $matchedRoute['handler'];

        if (is_callable($handler)) {
            $handler(...$params);
            return;
        }

        if (is_array($handler) && count($handler) === 2) {
            [$controller, $methodName] = $handler;

            if (!is_string($controller)) {
                error_log("❌ Controller must be a string class name");
                http_response_code(500);
                echo 'Invalid controller definition.';
                return;
            }

            if (!class_exists($controller)) {
                error_log("❌ Controller class not found: {$controller}");
                http_response_code(500);
                echo 'Controller class not found.';
                return;
            }

            if (!method_exists($controller, $methodName)) {
                error_log("❌ Method not found: {$controller}::{$methodName}");
                http_response_code(500);
                echo 'Method not found.';
                return;
            }

            try {
                if ($this->container && $this->container->has($controller)) {
                    $instance = $this->container->get($controller);
                } else {
                    $reflection = new \ReflectionClass($controller);
                    $constructor = $reflection->getConstructor();
                    
                    if ($constructor && $constructor->getNumberOfRequiredParameters() > 0) {
                        throw new \RuntimeException(
                            "Controller '{$controller}' has required constructor dependencies but is NOT registered in the container. " .
                            "Please add it to config/bootstrap/container.php."
                        );
                    }
                    $instance = $reflection->newInstance();
                }
            } catch (\Exception $e) {
                error_log("❌ Failed to instantiate controller: {$controller} - " . $e->getMessage());
                http_response_code(500);
                echo 'Controller instantiation failed: ' . $e->getMessage();
                return;
            }

            $instance->$methodName(...$params);
            return;
        }

        error_log("❌ Invalid route handler for: {$method} {$uri}");
        http_response_code(500);
        echo 'Invalid route handler.';
    }
}