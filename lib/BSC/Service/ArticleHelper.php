<?php

namespace BSC\Service;


use BSC\config;
use rex;

class ArticleHelper
{
    public static function getUrl(int $articleId, int $clangId = null, array $parameter = array()): string
    {
        if (is_null($clangId)) $clangId = \rex_clang::getCurrentId();
        if (substr(rex::getServer(), strlen(rex::getServer()) - 1, strlen(rex::getServer())) == '/') {
            $url = substr(rex::getServer(), 0, strlen(rex::getServer()) - 1);
        } else {
            $url = rex::getServer();
        }
        if (!is_null($clangId)) {
            $parameter['clang'] = $clangId;
        }
        $httpParameter = http_build_query($parameter);
        if (!empty($httpParameter)) {
            $httpParameter = '?' . $httpParameter;
        }

        return $url . config::get('resources.openapi.basePath') . ( '/article/' . $articleId . $httpParameter);
    }
}