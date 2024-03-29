<?php declare(strict_types=1);
/**
 * Created 2021-11-27
 * Author Dmitry Kushneriov
 */

namespace App\Api;
use ApiPlatform\Core\Exception\RuntimeException;
use Symfony\Component\Routing\RouterInterface;

class Router
{
    public function __construct(
        private RouterInterface $router
    ) {}

    /**
     * Convert route name like "api_users_get_collection" or ["api_users_get_item", "id" => "<user_id>"] or  ["api_users_get_item", "<user_id>"]
     * To array like ["GET", "/api/users"] or ["GET", "/api/users/<user_id>"]
     *
     * @param string|array $route
     *
     * @throws RuntimeException
     *
     * @return array<string>
     */
    public function createRoute(string|array $route): array
    {
        if (is_array($route)) {
            $name = array_shift($route);
            $params = $route;
        } else {
            $name = $route;
            $params = [];
        }

        $route = $this->router->getRouteCollection()->get($name);
        if ($route === null) {
            throw new RuntimeException("Invalid route name \"{$name}\"");
        }
        $identifiers = $route->getDefault("_api_identifiers");
        $routePrams = [];

        while (!empty($params)) {
            $param = array_key_first($params);
            $value = array_shift($params);
            if ($param === 0) {
                $param = array_shift($identifiers);
            }
            $routePrams[$param] = $value;
        }

        $methods = $route->getMethods();
        $uri = $this->router->generate($name, $routePrams);

        return [array_shift($methods), $uri];
    }
}