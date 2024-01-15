<?php
/**
 * @date     14.05.2020 19:36
 * @author   Peter Schulze [p.schulze@bitshifters.de]
 */

namespace BSC\Controller;

use BSC\Exception\NotFoundException;
use OAuth2\Response;
use Psr\Http\Message\ServerRequestInterface;
use btu_portal;
use stdClass;

class Structure extends AbstractController
{
    /**
     * @param ServerRequestInterface $request
     * @param array $args
     * @return Response
     * @author Peter Schulze
     */
    public static function getMilestoneStructureAction(ServerRequestInterface $request, array $args = []) {
        try {
            $milesStoneStructure = btu_portal::factory()->getArray("
                SELECT
                    *
                FROM
                    `rex_yform_field`
                WHERE
                    `table_name` = 'portal_milestones' AND
                    `type_name` != 'html' AND
                    `type_name` != 'show_if'
            --         AND `type_id` = 'value'
                ORDER BY
                    `id`
            ");

            $output = [];
            $validations = [];

            foreach($milesStoneStructure as $val) {
                if($val['type_id'] == 'validate') {
                    $validations[] = $val;
                    continue;
                }

                // exclude create & update fields & participant
                if(in_array($val['name'], ['createuser', 'createdate', 'updateuser', 'updatedate', 'participant'])) {
                    continue;
                }

                // determine category
                $typeParts = explode("_", $val['name']);
                $scope = array_search($typeParts[0], btu_portal::$fieldDependencies);
                $scope = ($scope === false ? ($typeParts[0] == 'else' ? 'else' : 'GENERAL') : btu_portal::$fieldDependencies[$scope]);

                // exclude ID fields
                if($scope != 'GENERAL' && count($typeParts) > 1 && $typeParts[1] == 'id') {
                    continue;
                }

                if(!isset($output[$scope])) {
                    $output[$scope] = [];
                }

                if(!isset($output[$scope][$val['name']])) {
                    $output[$scope][$val['name']] = [];
                }

                // type
                $output[$scope][$val['name']]['type'] = $val['db_type'];
                // label
                $output[$scope][$val['name']]['formLabel'] = $val['label'];
                // required
                $output[$scope][$val['name']]['required'] = false;
                $output[$scope][$val['name']]['placeholder'] = rex_getString("app_general_placeholder_list_notrequired");

                switch($val['type_name']) {
                    case 'choice':
                        $output[$scope][$val['name']]['multiple'] = ($val['multiple'] === '1');
                        $output[$scope][$val['name']]['placeholder'] = $val['placeholder'];
                        $output[$scope][$val['name']]['values'] = explode(",", $val['choices']);
                        break;

                    case 'be_manager_relation':
                    case 'be_manager_relation_id':
                        $output[$scope][$val['name']]['values'] = [];
                        $where = '';

                        if($val['name'] == 'type') {
                            $where = ' WHERE hidden = 0 ORDER BY priority ASC';
                        }

                        $entries = btu_portal::factory()->getArray("
                            SELECT * FROM {$val['table']} $where
                        ");

                        if($val['name'] == 'type') {
                            $output[$scope][$val['name']]['values'] = $entries;
                        } else {
                            foreach ($entries as $entry) {
                                $output[$scope][$val['name']]['values'][(int)$entry['id']] = '';
                                $names = explode(",", $val['field']);

                                foreach ($names as $name) {
                                    $output[$scope][$val['name']]['values'][(int)$entry['id']] .= (isset($entry[$name]) ? $entry[$name] : $name);
                                }
                            }
                        }

                        break;
                }
            }
            
//            var_dump($output);
//            print_r($validations);
//            die();
            
            foreach($validations as $val) {
                if($val['type_name'] != "empty" && $val['type_name'] != "value_dependency") {
                    continue;
                }
    
                $typeParts = explode("_", $val['name']);
                $scope = array_search($typeParts[0], btu_portal::$fieldDependencies);
                $scope = ($scope === false ? ($typeParts[0] == 'else' ? 'else' : 'GENERAL') : btu_portal::$fieldDependencies[$scope]);
    
                if(!isset($output[$scope][$val['name']])) {
                    continue;
                }
                
                if(!$output[$scope][$val['name']]['required']) {
                    $output[$scope][$val['name']]['required'] = false;
                }
    
                //$validation = new stdClass();
                //$validation->type = $val['type_name'];
                //$validation->message = $val['message'];
                $output[$scope][$val['name']]['required'] = true;
            }
            
            // fix placeholders
            foreach($output as &$scope) {
                foreach($scope as &$field) {
                    // fix placeholder
                    $placeholder = "app_general_placeholder";
    
                    if(isset($field['values']) && is_array($field['values'])) {
                        $placeholder .= '_list';
    
                        if(!$field['required']) {
                            $placeholder .= '_notrequired';
                        }
                    } else {
                        if(!$field['required']) {
                            $placeholder .= '_notrequired';
                        } else {
                            $placeholder .= '_textinput';
                        }
                    }
    
                    $field['placeholder'] = rex_getString($placeholder);
                }
            }
            
            // fix status_from
            $output['GENERAL']['status_from']['required'] = true;
            $output['GENERAL']['status_from']['placeholder'] = rex_getString("app_general_placeholder");
            
            // append else types to else branch
            $elseGroupsRaw = btu_portal::factory()->getArray("SELECT * FROM rex_ycom_group WHERE extended_list = 1 ORDER BY priority ASC");
            $elseGroups = [
                "type"          => "int",
                "formLabel"     => "Art",
                "required"      => true,
                "multiple"      => false,
                "placeholder"   => rex_getString("app_general_placeholder_list"),
                "values"        => []
            ];
            
            foreach($elseGroupsRaw as $group) {
                $elseGroups['values'][] = ['value' => (int)$group['id'], 'label' => $group['name']];
            }
    
            $output['else'] = ['type' => $elseGroups] + $output['else'];

            return self::response($output, 200);
        } catch (\Exception $e) {
            //self::getLogger()::logException($e);
            return new Response(array('error' => 'internal_error', 'error_description' => $e->getMessage()), 500);
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @param array $args
     * @return Response
     * @author Peter Schulze
     */
    public static function dictionaryGetAction(ServerRequestInterface $request, array $args = []) {
        try {
            echo json_encode(rex_getStringObject($request->getQueryParams()['filter']));
        } catch (\Exception $e) {
            //self::getLogger()::logException($e);
            return new Response(array('error' => 'internal_error', 'error_description' => $e->getMessage()), 500);
        }
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param array $args
     * @return \Laminas\Diactoros\Response\EmptyResponse|\Laminas\Diactoros\Response\JsonResponse|\Laminas\Diactoros\Response\TextResponse|\OAuth2\Response
     * @author Joachim Doerr
     */
    public static function getEventMainCategoriesAction(ServerRequestInterface $request, array $args = [])
    {
        try {
            $categories = btu_portal::getEventMainCategories();
            return self::response($categories, 200);
            
        } catch (NotFoundException $e) {
            //self::getLogger()::logException($e);
            return self::response(['error' => $e->getKey(), 'error_description' => $e->getMessage()], 401);
        } catch (\Exception $e) {
            //self::getLogger()::logException($e);
            return self::response(['error' => 'internal_error', 'error_description' => $e->getMessage()], 500);
        }
    }
}