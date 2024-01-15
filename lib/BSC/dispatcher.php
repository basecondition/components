<?php

namespace BSC;

use rex_extension;
use rex_extension_point;

class dispatcher
{
    public static function dispatch($name, $subject = null, array $params = [], $readonly = false): mixed
    {
        return rex_extension::registerPoint(new rex_extension_point($name, $subject, $params, $readonly));
    }
}