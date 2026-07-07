<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, callable|array $handler): Route
    {
        $route = new Route($path, $handler);
        $this->routes['GET'][] = $route;
        return $route;
    }

    public function post(string $path, callable|array $handler): Route
    {
        $route = new Route($path, $handler);
        $this->routes['POST'][] = $route;
        return $route;
    }

    public function put(string $path, callable|array $handler): Route
    {
        $route = new Route($path, $handler);
        $this->routes['PUT'][] = $route;
        return $route;
    }

    public function delete(string $path, callable|array $handler): Route
    {
        $route = new Route($path, $handler);
        $this->routes['DELETE'][] = $route;
        return $route;
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';

        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        $routes = $this->routes[$method] ?? [];

        foreach ($routes as $route) {
            $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $route->path);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                $handler = $route->handler;
                $middleware = $route->middleware;

                foreach ($middleware as $mw) {
                    if (is_string($mw)) {
                        $parts = explode(':', $mw, 2);
                        $class = $parts[0];
                        $args = isset($parts[1]) ? explode(',', $parts[1]) : [];

                        if (class_exists($class)) {
                            $instance = new $class();
                            $result = $instance->handle($args);
                            if ($result === false) {
                                return;
                            }
                        }
                    }
                }

                if (is_callable($handler)) {
                    echo call_user_func($handler, $params);
                }

                return;
            }
        }

        http_response_code(404);
        echo View::render('errors/404', [], 'layouts/app');
    }
}

class Route
{
    public string $path;
    public callable|array $handler;
    public array $middleware = [];

    public function __construct(string $path, callable|array $handler)
    {
        $this->path = $path;
        $this->handler = $handler;
    }

    public function middleware(string ...$middleware): self
    {
        $this->middleware = $middleware;
        return $this;
    }
}
