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


use Basecondition\View\FormView;
use DateTime;
use mblock_rex_form;
use rex;
use rex_file;
use rex_i18n;
use rex_plugin;
use rex_request;
use rex_sql;
use rex_url;

class MBlockHelper
{
    /**
     * @param FormView $formView
     * @param array $item
     * @author Joachim Doerr
     * @return array
     * @throws \rex_sql_exception
     */
    public static function getMBlockDefinitions(FormView $formView, array $item)
    {
        $definitions = self::loadDefinitions($item['mblock_definition_table']);
        $continue = false;

        if (is_array($definitions) && sizeof($definitions) > 0) {
            foreach ($definitions as $definition) {
                $item['mblock_definition'][$definition['code']] = $definition;
            }
        } else {
            $continue = true;
        }

        return array('continue' => $continue, 'item' => $item);
    }

    /**
     * @param mblock_rex_form $form
     * @param array $item
     * @param array $urlParameters
     * @param int $id
     * @param array $active
     * @param string $uid
     * @param string $baseClass
     * @param null $tableBaseName
     * @author Joachim Doerr
     */
    public static function addMBlockSetNavigation(mblock_rex_form $form, array $item, array $urlParameters, $id, array $active, $uid, $baseClass = 'base', $tableBaseName = null)
    {
        // create navigation
        $navigation = array();
        foreach ($item['mblock_definition'] as $key => $definition) {

            if (!is_array($definition)) {
                $definition = array('name' => $definition);
            }

            if (!array_key_exists('name', $definition)) {
                $definition['name'] = $key;
            }

            if (!isset($definition['search_schema']) && !is_numeric($key)) {
                $k = explode('/', $key);
                $definition['search_schema'] = $key;
                $definition['code'] = array_pop($k);
            }

            // add settings to url parameter
            $settings = self::getSettings($item, $key);
            $settings = array_combine(array_map(function ($k) {
                return 'mblock_settings_' . $k;
            }, array_keys($settings)), $settings);

            $urlParameters = array_merge($urlParameters, $settings,
                array(
                    'id' => $id,
                    'add_mblock_block' => 1,
                    'definition_search_schema' => $definition['search_schema'],
                    'item' => $item['name'],
                    'definition_code' => $definition['code'],
                    'definition_name' => $definition['name'],
                )
            );

            foreach ($definition as $k => $v) {
                if (strpos($k, 'label') !== false) {
                    $urlParameters[$k] = $v;
                }
            }

            $link = rex_url::backendController($urlParameters, true);

            $class = '';
            $ok = '';

            if (in_array($definition['code'], $active)) {
                $class = ' class="disabled"';
                $ok = ' <span class="glyphicon glyphicon-ok">';
            }

            $linkName = ViewHelper::getLabel($definition, 'label', $tableBaseName);
            $navigation[] = '<li' . $class . ' data-type_key="' . $definition['code'] . '"><a href="#" data-link="' . $link . '" data-rel="loadmblock">' . $linkName . $ok . '</a></li>';
        }

        // add label for dropdown link
        $item['mblock_label'] = ViewHelper::getLabel($item, 'label', $tableBaseName);

        if (!isset($item['mblock_label'])) {
            $item['mblock_label'] = rex_i18n::msg('add_mblock_block');
        }

        if (!isset($item['icon'])) {
            $item['icon'] = 'fa-th-list';
        }

        // TODO use fragments

        // print to form
        $form->addRawField('
            <div class="' . $baseClass . '_mblock mblock_set_nav" data-unique_id="' . $uid . '">
                <div class="dropdown btn-block">
                    <button class="btn btn-white btn-block dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                        <i class="rex-icon ' . $item['icon'] . '"></i> ' . $item['mblock_label'] . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu clearfix" role="menu" style="">' . implode('', $navigation) . '</ul>
                </div>
            </div>
        ');
    }

    /**
     * @param FormView $view
     * @param array $item
     * @param array $active
     * @param string $uid
     * @param string $baseClass
     * @param null $tableBaseName
     * @internal param array $active
     * @author Joachim Doerr
     */
    public static function addMBlockSetFieldset(FormView $view, array $item, array $active, $uid, $baseClass = 'base')
    {
        $content = '';
        $item_clone = $item;

        foreach ($active as $type) {

            $headlineLabel = '';
            $headline = '';

            if (is_array($item_clone['mblock_definition'])) {
                foreach ($item_clone['mblock_definition'] as $code => $name) {
                    $d = explode('/', $code);
                    $cd = array_pop($d);
                    if ($cd == $type) {
                        $headlineLabel = ViewHelper::getLabel($item_clone['mblock_definition'][$code], 'label');
                        $item['mblock_definition'] = $code;
                        $headline = $code;
                    }
                }
            }

            // use schema is it exist
            if (isset($item_clone['mblock_definition'][$type]['search_schema'])) {
                $item['mblock_definition'] = $item_clone['mblock_definition'][$type]['search_schema'];
            }

            $settings = self::getSettings($item_clone, $type);

            // TODO MBlock headline
            // TODO collapse by settings
            // TODO collapse open with name
            if (isset($item_clone['mblock_definition'][$type]['name'])) {
                $headline = $item_clone['mblock_definition'][$type]['name'];
            } else {
                $headline = (!empty($headlineLabel)) ? $headlineLabel : rex_i18n::msg($headline);
            }
            if (!empty($headline)) {
                $headline = "<div class=\"mblock_headline\" data-type_key=\"{$type}\"><h6>{$headline}</h6></div>";
            }
            $content .=  $headline . $view->createMBlockFieldset($item, $type, $settings);
            // TODO collapse close
        }
        $view->form->addRawField('<div class="' . $baseClass . '_mblock mblock_set_content" data-unique_id="' . $uid . '">' . $content . '</div>');

    }

    /**
     * @return mixed
     * @author Joachim Doerr
     */
    public static function getAddMBlock()
    {
        // create form
        $form = new FormView(
            ViewHelper::getAddonByBasePath()->getName(),
            ViewHelper::getSearchFileByBasePath(),
            '',
            rex_request::get('id', 'string'),
            false,
            ['base_path' => rex_request::get('base_path', 'string')],
            false
        );

        // TODO if item_name empty == exception
        $item = ['name' => rex_request::get('item', 'string')];
        $lang = substr(rex::getProperty('lang'), 0, 2);

        if (!empty(rex_request::get('label_' . $lang))) {
            $item['label_' . $lang] = rex_request::get('label_' . $lang);
        }

        // load item
        foreach ($form->items as $set) {
            if (is_array($set)) {
                foreach ($set as $key => $value) {
                    if (is_array($value) && array_key_exists('name', $value) && $value['name'] == $item['name']) {
                        $item = array_merge($item, $value);
                        break;
                    }
                }
            }
        }

        // set search schema as definition for createMBlockFieldset
        $item['mblock_definition'] = rex_request::get('definition_search_schema', 'string');

        // use settings by request
        $settings = array();
        foreach (array(
                     'mblock_settings_min',
                     'mblock_settings_max',
                     'mblock_settings_delete_confirm',
                     'mblock_settings_input_delete',
                     'mblock_settings_smooth_scroll'
                 ) as $key) {
            if (!is_null(rex_request::get($key, 'int', null))) {
                $settings[str_replace('mblock_settings_', '', $key)] = rex_request::get($key, 'int');
            }
        }

        // TODO MBlock headline
        // TODO collapse by settings
        // TODO collapse open with name
        $content = '<h6>' . (isset($item['label_' . $lang])) ? $item['label_' . $lang] : rex_i18n::msg(rex_request::get('definition_name', 'string')) . '</h6>';
        // create block
        $content .= $form->createMBlockFieldset($item, rex_request::get('definition_code', 'string'), $settings);
        // TODO collapse close

        // return
        return $content;
    }

    /**
     * @param $table
     * @param string $tempfile
     * @return array
     * @author Joachim Doerr
     * @throws \rex_sql_exception
     */
    private static function loadDefinitions($table, $tempfile = 'data/definitions/default/temp/%s.yml')
    {
        $sql = rex_sql::factory();
        $sql->setQuery("SELECT * FROM " . $table . " ");

        $definitions = array();
        $addon = ViewHelper::getAddonByBasePath();
        $plugin = ViewHelper::getPluginByBasePath();

        if ($plugin instanceof rex_plugin) {
            $addon = $plugin;
        }

        // save as temp
        foreach ($sql->getArray() as $key => $item) {

            $file = $addon->getPath(sprintf($tempfile, $item['code']));
            $path = explode('/', pathinfo($file, PATHINFO_DIRNAME));

            $definitions[] = array_merge(
                array(
                    'path' => $file,
                    'file' => sprintf($tempfile, $item['code']),
                    'table' => $table,
                    'code' => $item['code'],
                    'search_schema' => array_pop($path) . '/' . $item['code']
                ), $item);
            $create = true;

            if (file_exists($file)) {
                $update = new DateTime($item['updatedate']);
                $create = ($update->getTimestamp() > filectime($file));
            }
            if ($create === true) {
                rex_file::put($addon->getPath(sprintf($tempfile, $item['code'])), $item['definition']);
            }
        }
        return $definitions;
    }

    /**
     * TODO use
     * @param array $item
     * @param null $type
     * @return array
     * @author Joachim Doerr
     */
    public static function getSettings(array $item, $type = null)
    {
        // settings
        $settings = array();
        foreach (array(
                     'mblock_settings_min',
                     'mblock_settings_max',
                     'mblock_settings_delete_confirm',
                     'mblock_settings_input_delete',
                     'mblock_settings_smooth_scroll'
                 ) as $key) {
            if (isset($item[$key])) {
                $settings[str_replace('mblock_settings_', '', $key)] = $item[$key];
            }
        }

        foreach (array($type, "sub/$type") as $typKey) {
            // settings by type definition
            if (!is_null($typKey) && is_array($item['mblock_definition']) && isset($item['mblock_definition'][$typKey])) {
                $definition = $item['mblock_definition'][$typKey];
                if (is_array($definition) && array_key_exists('mblock_settings', $definition)) {
                    if (is_string($definition['mblock_settings'])) {
                        $yml = new \Symfony\Component\Yaml\Yaml();
                        $definition['mblock_settings'] = $yml->parse($definition['mblock_settings']);
                    }
                }
            } else {
                $definition = $item;
            }
            if (isset($definition['mblock_settings']) && is_array($definition['mblock_settings'])) {
                foreach (array(
                             'min',
                             'max',
                             'delete_confirm',
                             'input_delete',
                             'smooth_scroll'
                         ) as $key) {
                    if (isset($definition['mblock_settings'][$key])) {
                        $settings[$key] = $definition['mblock_settings'][$key];
                    }
                }
            }
        }
        return $settings;
    }

    /**
     * @param string $prefix
     * @param bool $more_entropy
     * @return string
     * @author Joachim Doerr
     */
    public function uniqId($prefix = "", $more_entropy = false)
    {
        return uniqid($prefix, $more_entropy);
    }
}