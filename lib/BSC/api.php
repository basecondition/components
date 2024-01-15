<?php

namespace BSC;

use BSC\Api\Router;
use BSC\Trait\Provider;
use JetBrains\PhpStorm\NoReturn;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\TextResponse;
use OAuth2\Response;
use rex_response;

/**
 * @description für oauth zwigend in .htaccess auth rewriteRule einfügen:
 * # API Autheader
 * RewriteCond %{HTTP:Authorization} ^(.*)
 * RewriteRule .* - [e=HTTP_AUTHORIZATION:%1]
 */
class api
{
    use Provider;

    private static ?Router $router = null;

    public static array $status = [
        200 => '200 OK',
        201 => '201 Created',
        204 => '204 No Content',
        304 => '304 Not Modified',
        400 => '400 Bad Request',
        401 => '401 Unauthorized',
        403 => '403 Forbidden',
        404 => '404 Not Found',
        405 => '405 Method Not Allowed',
        500 => '500 Internal Server Error',
    ];

    public static function handleRoutes(): ?bool
    {
        if (!empty(base::config('resources.openapi.basePath')) && !str_starts_with(base::config('bsc.api.uri'), base::config('resources.openapi.basePath'))) return false;
        if (base::config('bsc.api.uri') == '/') return false;
        if (!self::$router instanceof Router) self::$router = new Router();
        self::sendResponse(self::$router->handleRouting());
    }

    #[NoReturn]
    protected static function sendResponse($response): void
    {
        if ($response instanceof Response) {
            $response->send();
            exit;
        }
        if ($response instanceof \Laminas\Diactoros\Response) {
            rex_response::setStatus(self::$status[$response->getStatusCode()]);
            if ($response instanceof JsonResponse || $response instanceof TextResponse)
                rex_response::sendContent($response->getBody()->getContents(), 'application/json');
            if ($response instanceof EmptyResponse)
                rex_response::sendContent('', 'application/json');
        }
        exit;
    }
}