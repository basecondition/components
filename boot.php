<?php
/**
 * ┓            ┓• •
 * ┣┓┏┓┏┏┓┏┏┓┏┓┏┫┓╋┓┏┓┏┓
 * ┗┛┗┻┛┗ ┗┗┛┛┗┗┻┗┗┗┗┛┛┗━━
 * @package basecondition components
 * @author Joachim Doerr
 * @copyright (C) hello@basecondition.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/** @var rex_addon $this */

use BSC\base;
use BSC\config;
use BSC\listener;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

$uri = $_SERVER['REQUEST_URI'];
// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) $uri = substr($uri, 0, $pos);

// TODO docs
// set first basic api config parameters
$db = rex::getDbConfig(1);
config::set('bsc', [
    'api' => [
        // wenn ein codegen neu gelaufen ist, oder resourcen upgedated wurden z.b. composer etc.
        // dann am besten kurzzeitig auf true sofern es probleme gibt
        'developmentMode' => false,
        'uri' => rawurldecode($uri),
        'doctrine' => [
            'isDevMode' => true,
            'entitiesPath' => $this->getPath('lib/BSC/Entity/'),
            'repositoriesNamespace' => 'BSC\Repository',
            'db' => [
                'driver'   => 'pdo_mysql',
                'host'     => $db->host,
                'user'     => $db->login,
                'password' => $db->password,
                'dbname'   => $db->name,
            ]
        ],
    ],
]);

$doctrineConfig = Setup::createYAMLMetadataConfiguration(
    [config::get('bsc.api.doctrine.entitiesPath')],
    config::get('bsc.api.doctrine.isDevMode')
);

$entityManager = EntityManager::create(config::get('bsc.api.doctrine.db'), $doctrineConfig);


// set mandant key to config
if (is_null(config::get('mandant.key'))) {
    $hostParts = array_filter(explode(".", rex_yrewrite::getHost()));
    // TODO default fallback mandantKey in die rex addon config packen
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

// prevent OPTION CORS error when receiving jQuery AJAX response having sent Authorization header (token)
//header('Access-Control-Allow-Origin: *');
//header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, POST, DELETE, OPTIONS');
//header('Access-Control-Allow-Headers: Content-Type');

if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    //header("Access-Control-Allow-Origin: *");
    header('Access-Control-Allow-Credentials: true');
    header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, POST, DELETE, OPTIONS");
}
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
        header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, POST, DELETE, OPTIONS");
    }
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    }
    exit(0);
}

base::set('table', [
    'locations'         => 'portal_locations',
    'milestones'        => 'portal_milestones',
    'permission'        => 'rex_bsc_permission',
    'group_permission'  => 'rex_bsc_group_permission',
    'address'           => 'rex_bsc_address',
    'user_address'      => 'rex_bsc_user_address',
    'user_avatar'       => 'rex_bsc_user_avatar',
    'ycom_group'        => 'rex_ycom_group',
    'ycom_user'         => 'rex_ycom_user',
    'mandant'           => 'rex_bsc_mandant',
    'meta_fields'       => 'rex_metainfo_field',
    'auth_user'         => 'oauth_users',
    'token'             => 'oauth_access_tokens',
    'refresh_token'     => 'oauth_refresh_tokens',
]);

// first load listener definitions
config::loadConfig(['../../src/addons/components/resources/*.yml']);

// register event listener
listener::registerListener(config::get('resources.service.listener'));


/*****
 * EXPERIMENT AREA
 */

$tables = base::get('table');


//$sql = rex_sql::factory();
//$result = $sql->getArray("select concat(es.id, eb.id) as id, concat(eb.name, ', ', es.name) as name from {$tables['stations']} es left join {$tables['locations']} eb on es.`location` = eb.id");
//$result = array_filter(array_merge($result,
//    array(['id' => 2, 'name' => 'Global (für alle Gruppen)']),
//    $sql->getArray("select concat(id, 0) as id, name from {$tables['locations']}")
//));
//
//if (is_array($result) && sizeof($result) > 0) {
//    foreach ($result as $item) {
//        $sql = rex_sql::factory();
//        $sql->setQuery("insert into {$tables['group']} (id, name) values (\"{$item['id']}\", \"{$item['name']}\") on duplicate key update name=\"{$item['name']}\"");
//    }
//}


// erstelle rolle und rechte anhand von openapi
if (config::get('bsc.api.developmentMode')) {
    /** @var array $tags */
    $tags = config::get('resources.openapi.tags');
    if (sizeof($tags) > 0) {
        foreach ($tags as $tag) {
            $permission = BSC\Repository\YComGroupRepository::findPermissionByName($tag['name']);
            if (is_null($permission)) {
                // create group
                BSC\Repository\YComGroupRepository::insertPermission(['name' => $tag['name']]);
            }
        }
    }
    // füge pro mandant eine gruppe hinzu -> für mandanten spezifisches zugriffsrecht
    /** @var rex_yform_manager_dataset $mandant */
    foreach (BSC\Repository\MandantRepository::getAllMandants() as $mandant) {
        $mandantGroup = BSC\Repository\YComGroupRepository::findGroupByMandantKey($mandant->getValue('key'));
        if (is_null($mandantGroup)) {
            // create group
            BSC\Repository\YComGroupRepository::insertGroup(['name' => $mandant->getValue('key')]);
        }
    }
}


// TODO YFORM REST iregndwie noch integrieren...
$route = new \rex_yform_rest_route(
    [
        'path' => '/v1/user/',
        'auth' => '\rex_yform_rest_auth_token::checkToken',
        'type' => \rex_ycom_user::class,
        'query' => \rex_ycom_user::query(),
        'get' => [
            'fields' => [
                'rex_ycom_user' => [
                    'id',
                    'login',
                    'email',
                    'name'
                ],
                'rex_ycom_group' => [
                    'id',
                    'name'
                ]
            ]
        ],
        'post' => [
            'fields' => [
                'rex_ycom_user' => [
                    'login',
                    'email',
                    'ycom_groups'
                ]
            ]
        ],
        'delete' => [
            'fields' => [
                'rex_ycom_user' => [
                    'id',
                    'login'
                ]
            ]
        ]
    ]
);
\rex_yform_rest::addRoute($route);
