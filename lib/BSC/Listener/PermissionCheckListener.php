<?php
/**
 * User: joachimdorr
 * Date: 10.06.20
 * Time: 15:49
 */

namespace BSC\Listener;


use BSC\config;
use BSC\Repository\YComGroupRepository;
use OAuth2\Response;
use OAuth2\ResponseInterface;
use rex_yform_manager_dataset;

class PermissionCheckListener
{
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

        if (count($subject['groups']) <= 0 || !isset($subject['requestConfig']['tags'])) return new Response(array('error' => 'account_permission_unknown', 'error_description' => 'Account permission unknown'), 403);

        $permissions = YComGroupRepository::findPermissionsByGroupId(array_keys($subject['groups']));

        if(is_array($subject['requestConfig']['tags'])) {
            foreach ($subject['requestConfig']['tags'] as $tag) {

                $permissionId = [];

                /** @var rex_yform_manager_dataset $permission */
                foreach ($permissions as $permission) {
                    if($permission->getValue('name') == $tag) {
                        $permissionId[] = $permission->getId();
                    }
                }

                // gibt es überhaupt permissions?
                if (count($permissionId) <= 0) {
                    return new Response(array('error' => 'account_permission_denied', 'error_description' => 'Account permission denied'), 401);
                }

                // jetzt können wir die permission groups auslesen, da gibts dann den permission scope zur rolle
                $permissionGroups = YComGroupRepository::findPermissionGroupByPermissionId($permissionId);

                // nur wenn der scope vorhanden ist prüfen wir auf den scope
                if (count($subject['scope']) > 0) {
                    $matchingValues = [];
                    $permissionScope = [];
                    // geh durch die groups und prüfe ob sie ein passenden scope haben wenn nein response 401
                    /** @var rex_yform_manager_dataset $permissionGroup */
                    foreach ($permissionGroups as $permissionGroup) { // wir prüfen für jede permission group
                        // wir werten den permission scope des user aus
                        $data = $permissionGroup->getData();
                        $permissionScope = json_decode($data['scope']);
                        if (!is_array($permissionScope)) $permissionScope = [$permissionScope]; // continue; -> wenn kein scope vollzugriff bedeuten sollte dann continue!
                        // wir suchen nach der schnittmenge von user und request scope
                        $matchingValues = array_filter(array_merge($matchingValues, array_intersect($permissionScope, $subject['scope'])));
                    }
                    // wer admin ist braucht kein check, der bekommt min 1 match
                    if (in_array('admin', $permissionScope)) {
                        $matchingValues[] = 'admin';
                    }
                    // gab es kein match?
                    if (count($matchingValues) <= 0) {
                        return new Response(array('error' => 'account_permission_denied', 'error_description' => 'Account permission denied'), 401);
                    }
                }
            }
        }
        return $subject;
    }
}