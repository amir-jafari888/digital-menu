<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Tiny explicit router. Patterns use {name} placeholders and are anchored, so
 * "/orders/{id}" never matches "/orders/1/items".
 */
final class Router
{
    /** @var array<int, array{method:string, regex:string, keys:array<int,string>, handler:callable}> */
    private array $routes = [];

    private $fallback = null;

    public function get(string $pattern, callable $handler): self
    {
        return $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, callable $handler): self
    {
        return $this->add('POST', $pattern, $handler);
    }

    public function put(string $pattern, callable $handler): self
    {
        return $this->add('PUT', $pattern, $handler);
    }

    public function delete(string $pattern, callable $handler): self
    {
        return $this->add('DELETE', $pattern, $handler);
    }

    public function any(string $pattern, callable $handler): self
    {
        foreach (['GET', 'POST', 'PUT', 'PATCH', 'DELETE'] as $method) {
            $this->add($method, $pattern, $handler);
        }

        return $this;
    }

    public function fallback(callable $handler): self
    {
        $this->fallback = $handler;

        return $this;
    }

    public function add(string $method, string $pattern, callable $handler): self
    {
        $keys = [];
        $regex = preg_replace_callback(
            '#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#',
            static function (array $m) use (&$keys): string {
                $keys[] = $m[1];

                return '([^/]+)';
            },
            $pattern
        );

        $this->routes[] = [
            'method'  => strtoupper($method),
            'regex'   => '#^' . $regex . '$#u',
            'keys'    => $keys,
            'handler' => $handler,
        ];

        return $this;
    }

    public function dispatch(string $method, string $path): void
    {
        $method = strtoupper($method);
        $hasPath = false;

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            if (preg_match($route['regex'], $path, $matches) !== 1) {
                continue;
            }

            $hasPath = true;
            array_shift($matches);
            $params = [];
            foreach ($route['keys'] as $index => $key) {
                $params[$key] = $matches[$index] ?? '';
            }

            ($route['handler'])($params);

            return;
        }

        if ($this->fallback !== null) {
            ($this->fallback)($path, $hasPath);

            return;
        }

        Response::notFound();
    }
}
