<?php
/**
 * User: joachimdorr
 * Date: 10.06.20
 * Time: 15:49
 */

namespace BSC\Listener;


use BSC\Api\Middleware;
use BSC\config;
use OAuth2\Response;
use OAuth2\ResponseInterface;
use rex_ycom_group;

class PermissionCheckListener
{
    public static function executeGroupListManipulationAction(\rex_extension_point $ep): array
    {
        $group = $ep->getSubject();
        return $group;
    }

    public static function executeMandantPermissionCheckAction(\rex_extension_point $ep): ResponseInterface|array
    {
        $subject = $ep->getSubject();
        if (isset($subject['group']['key']) && $subject['group']['key'] !== config::get('mandant.key')) {
            return new Response(array('error' => 'account_mandant_permission_denied', 'error_description' => 'Account mandant permission denied'), 401);
        }
        return $subject;
    }

    public static function executeScopePermissionCheckAction(\rex_extension_point $ep): ResponseInterface|array
    {
        $subject = $ep->getSubject();
        dump($subject);
        dump(rex_ycom_group::getGroups());
        if(isset($subject['requestConfig']['tags']) && is_array($subject['requestConfig']['tags'])) {
            foreach ($subject['requestConfig']['tags'] as $tag) {
                dump($tag);
            }
        }


//        foreach ($subject['security'] as $value) {
//            if (isset($value[Middleware::oAuthSecurityDefinitionKey])) {
//                $scope = $value[Middleware::oAuthSecurityDefinitionKey];
//            }
//            dump($value[Middleware::oAuthSecurityDefinitionKey]);
////            if (is_array($value)) {
////                if ($key === "oAuth2") {
////                    dump($key);
////                }
////            }
//        }



        $path = str_replace('/', '_', $subject['path']);
        if (str_starts_with($path, "_")) $path = substr($path, 1);

//        dump($path);
//        dump('T');
        die;

        if (isset($subject['scope']) && is_array($subject['scope'])) {
            foreach ($subject['scope'] as $item) {
                if (isset($subject['group']['key']) && $subject['group']['key'] !== $path . '_' . $item) {
                    return new Response(array('error' => 'account_mandant_permission_denied', 'error_description' => 'Account mandant permission denied'), 401);
                }
            }
        }

        dump($subject);die;
//        if (isset($subject['group']['key']) && !$subject['group']['key'] == config::get('mandant.key')) {
//            new Response(array('error' => 'account_mandant_permission_denied', 'error_description' => 'Account mandant permission denied'), 401);
//        }
        return $subject;
    }
}