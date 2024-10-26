<?php
/** @var rex_addon $this */

use BSC\base;
use BSC\config;
use BSC\listener;

// set mandant key to config
if (is_null(config::get('mandant.key'))) {
    $hostParts = array_filter(explode(".", rex_yrewrite::getHost()));
    config::set('mandant.key', (isset($hostParts[0]) && $hostParts[0] !== 'www') ? $hostParts[0] : 'chekov');
}

// add the mandant dataset-array to the config base
$mandant = BSC\Repository\MandantRepository::getMandantByKey(base::get('mandant.key'));
if ($mandant instanceof rex_yform_manager_dataset) {
    // add mandant user and article to base
    config::set('mandant', $mandant);
}

// composer autoloader
$loader = require_once $this->getPath('/vendor/autoload.php');
config::set('composer.autoload', $loader);

// first load listener definitions
config::loadConfig(['../../src/addons/components/resources/*.yml']);

// register event listener
listener::registerListener(config::get('resources.service.listener'));