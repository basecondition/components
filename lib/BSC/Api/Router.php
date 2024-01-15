<?php

namespace BSC\Api;

use BSC\base;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\ServerRequest;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use OAuth2\Response;
use function FastRoute\simpleDispatcher;


class Router
{
    private Dispatcher $router;
    public ServerRequest $request;

    public function __construct()
    {
        $this->request = ServerRequestFactory::fromGlobals(
            $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES
        );
        $this->setRouteDispatcher();
    }

    private function setRouteDispatcher(): void
    {
        $this->router = simpleDispatcher(function (RouteCollector $router) {
            $openApi = base::config('resources.routing');
            if (is_array($openApi) && sizeof($openApi) > 0) {
                foreach ($openApi as $route) {
                    $controller = explode(':', $route['defaults']['_controller']);
                    if (isset($controller[0]) && isset($controller[1])) {
                        $controllerClass = str_replace(['.', 'bsc\\'], ['\\', 'BSC\\'], implode('\\', array_map('ucwords', explode('.', $controller[0]))));
                        $router->addRoute($route['methods'][0], base::config('resources.openapi.basePath') . $route['path'], "$controllerClass::$controller[1]");
                    }
                }
            }
        });
    }

    public function handleRouting(): mixed
    {
        $routeInfo = Middleware::process($this->request, $this->router);

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND: // 404 Not Found
                return new Response([], 404);
            case Dispatcher::METHOD_NOT_ALLOWED: // 405 Method Not Allowed
                return new Response([], 405);
            case Dispatcher::FOUND: // 200 Success
                $handler = $routeInfo[1];
                return $handler($this->request, ['route_parameter' => $routeInfo[2], 'route_info' => $routeInfo]);
            case 3:
                if (isset($routeInfo['response']) && $routeInfo['response'] instanceof Response) return $routeInfo['response'];
                return new Response([], 401);
        }

        return new Response([], 204);
    }
}