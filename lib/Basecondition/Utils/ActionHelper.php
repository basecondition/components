<?php
/**
 * @package components
 * @author Joachim Doerr
 * @copyright (C) hello@basecondition.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Basecondition\Utils;


use rex;
use rex_extension;
use rex_extension_point;
use rex_form;
use rex_request;
use rex_sql;

class ActionHelper
{
    /**
     * @author Joachim Doerr
     * @param string $extensionPoint
     */
    public static function registerDefaultActions($extensionPoint = 'BSC_FUNC_ACTION')
    {
        rex_extension::register($extensionPoint, function (rex_extension_point $params) {
            return self::executeDefaultActions($params);
        });
    }

    /**
     * @param rex_extension_point $params
     * @return array|mixed
     * @author Joachim Doerr
     * @throws \rex_sql_exception
     */
    public static function executeDefaultActions(rex_extension_point $params)
    {
        $parameter = $params->getSubject();
        $table = rex::getTablePrefix() . $parameter['addon']->getName() . '_' . $parameter['search_file'];

        if (is_array($parameter)
            && isset($parameter['func'])
            && isset($parameter['base_path'])
        ) {
            $func = $parameter['func'];

            switch ($func) {
                case 'delete':
                    $parameter['action_status'] = self::deleteData($table, $parameter['id']);
                    $parameter['func'] = '';
                    break;
                case 'status':
                    $parameter['action_status'] = self::statusData($table, $parameter['id']);
                    $parameter['func'] = '';
                    break;
                case 'clone':
                    $parameter['action_status'] = self::cloneData($table, $parameter['id']);
                    $parameter['func'] = '';
                    break;
            }

            if (isset($parameter['action_status']) && !$parameter['action_status']) {
                $parameter[$parameter['search_file'] . '_msg'] = \rex_i18n::msg($parameter['addon']->getName() . '/' . $parameter['search_file'] . '/' . $func . '_fail');

                if ($parameter['show_msg']) {
                    echo \rex_view::warning($parameter[$parameter['search_file'] . '_msg']);
                }
            }
            if (isset($parameter['action_status']) && $parameter['action_status']) {
                $parameter[$parameter['search_file'] . '_msg'] = \rex_i18n::msg($parameter['addon']->getName() . '/' . $parameter['search_file'] . '/' . $func . '_success');

                if ($parameter['show_msg']) {
                    echo \rex_view::info($parameter[$parameter['search_file'] . '_msg']);
                }
            }

        }

        return $parameter;
    }

    /**
     * togglet bool data column
     * @param $table
     * @param $id
     * @param null $column
     * @return boolean
     * @author Joachim Doerr
     * @throws \rex_sql_exception
     */
    public static function toggleBoolData($table, $id, $column = NULL)
    {
        if (!is_null($column)) {
            $sql = rex_sql::factory();
            $sql->setQuery("UPDATE $table SET $column=ABS(1-$column) WHERE id=$id");
            return true;
        } else {
            return false;
        }
    }

    /**
     * clone data
     * @param $table
     * @param $id
     * @return boolean
     * @author Joachim Doerr
     * @throws \rex_sql_exception
     */
    static public function cloneData($table, $id)
    {
        $sql = rex_sql::factory();
        $fields = $sql->getArray('DESCRIBE `' . $table . '`');
        if (is_array($fields) && count($fields) > 0) {
            foreach ($fields as $field) {
                if ($field['Key'] != 'PRI' && $field['Field'] != 'status') {
                    $queryFields[] = $field['Field'];
                }
            }
        }
        $sql->setQuery('INSERT INTO ' . $table . ' (`' . implode('`, `', $queryFields) . '`) SELECT `' . implode('`, `', $queryFields) . '` FROM ' . $table . ' WHERE id =' . $id);
        return true;
    }

    /**
     * delete data
     * @param $table
     * @param $id
     * @return boolean
     * @author Joachim Doerr
     * @throws \rex_sql_exception
     */
    static public function deleteData($table, $id)
    {
        $sql = rex_sql::factory();
        $sql->setQuery("DELETE FROM $table WHERE id=$id");
        return true;
    }

    /**
     * delete data
     * @param $table
     * @param $id
     * @return boolean
     * @author Joachim Doerr
     * @throws \rex_sql_exception
     */
    static public function statusData($table, $id)
    {
        self::toggleBoolData($table, $id, 'status');
        return true;
    }

    /**
     * @param rex_extension_point $params
     * @author Joachim Doerr
     * @return string
     *
     * rex_extension::register('REX_FORM_SAVED', function (rex_extension_point $params) {
     *      ActionHelper::postSaveStatusChange($params);
     * });
     * @throws \rex_sql_exception
     */
    public static function postSaveStatusChange(rex_extension_point $params)
    {
        $param = $params->getParams();
        /** @var rex_form $form */
        $form = $param['form'];
        $post = rex_request::post($form->getName());
        $status = (array_key_exists('status', $post) && array_key_exists(1, $post['status']) && $post['status'][1] == 1) ? 1 : 0;

        // set status
        $sql = rex_sql::factory();
        $sql->setQuery("UPDATE ".$form->getTableName()." SET status = $status WHERE " . $form->getWhereCondition());
        return '';
    }

}