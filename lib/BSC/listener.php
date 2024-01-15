<?php

namespace BSC;


use rex;
use rex_autoload;
use rex_extension;
use rex_extension_point;
use function PHPUnit\Framework\callback;

class listener
{
    public static function listen($name, callable|array $extension, $priority = rex_extension::NORMAL, array $params = [], bool $autoloadReload = false): void
    {
        rex_extension::register($name, function (rex_extension_point $ep) use ($extension, $autoloadReload) {
            if ($autoloadReload) {
                rex_autoload::reload(true);
            }
            if (is_array($extension) && !empty($extension['class']) && !empty($extension['method'])) {
                $class = $extension['class'];
                $method = $extension['method'];
                return $class::$method($ep);
            }
            return call_user_func($extension, $ep);
        }, $priority, $params);
    }

    public static function registerListener(array $listener): void
    {
        if (sizeof($listener) > 0) {
            foreach ($listener as $item) {
                if (!isset($item['tags']) || !is_array($item['tags']) || sizeof($item['tags']) <= 0) continue; // continue if no tags exist
                foreach ($item['tags'] as $tag) {
                    // validate tag
                    if (!isset($tag['name']) || !isset($item['class']) || !isset($tag['method'])) continue;
                    // set optional priority from tag
                    $priority = (isset($tag['priority'])) ? $tag['priority'] : rex_extension::NORMAL;
                    // get default reload setup
                    $autoloadReload = (!rex::isBackend() && base::config('bsc.api.developmentMode')) ? base::config('bsc.api.developmentMode') : false;
                    // get from tag definition
                    if (isset($tag['reload'])) $autoloadReload = (bool) $tag['reload'];
                    // execute listen
                    self::listen($tag['name'],['class' => $item['class'], 'method' => $tag['method']], $priority, [], (bool) $autoloadReload);
                }
            }
        }
    }
}