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
use rex_clang;
use rex_extension_point;
use rex_form_element;
use rex_form_prio_element;
use rex_form_select_element;
use rex_form_widget_media_element;
use rex_form_widget_medialist_element;
use rex_i18n;
use rex_form;
use rex_plugin;
use rex_sql;
use rex_view;

class FormHelper
{
    /**
     * @param rex_form $form
     * @param array $item
     * @param null $id
     * @param null $tableBaseName
     * @return mixed|null|rex_form_element|rex_form_widget_media_element|rex_form_widget_medialist_element|rex_form_prio_element
     * @throws \rex_exception
     * @author Joachim Doerr
     */
    public static function addFormElementByField(rex_form $form, array $item, $id = null, $tableBaseName = null)
    {
        if (array_key_exists('form_hidden', $item) && $item['form_hidden'] == 1) {
            return null;
        }

        // TODO STYLE FIELDSET LEGEND
        if (array_key_exists('fields_legend', $item)) {
            return $form->addRawField(rex_i18n::msg($item['fields_legend']));
        }

        if (
            (array_key_exists('type', $item) or array_key_exists('form_type', $item))
            && (array_key_exists('name', $item) or array_key_exists('lang_name', $item))
        ) {
            $name = (array_key_exists('lang_name', $item)) ? $item['lang_name'] : $item['name'];

            if (array_key_exists('form_callable', $item)) {
                return call_user_func_array($item['form_callable'], array($form, $item, $id, $tableBaseName));
            }

            $type = (array_key_exists('form_type', $item)) ? $item['form_type'] : $item['type'];
            $defaultValue = (isset($item['form_default_value'])) ? $item['form_default_value'] : null;

            if (array_key_exists('form_default_value_callable', $item)) {
                $defaultValue = call_user_func($item['form_default_value_callable']);
            }

            switch ($type) {
                case 'hidden':
                    $form->addRawField('<div class="hide">');
                    $element = $form->addTextField($name, $defaultValue);
                    $form->addRawField('</div>');
                    return $element;
                case 'link':
                    return $form->addLinkmapField($name, $defaultValue);
                case 'linklist':
                    return $form->addLinklistField($name, $defaultValue);
                case 'media':
                    return $form->addMediaField($name, $defaultValue);
                case 'medialist':
                    return $form->addMedialistField($name, $defaultValue);
                case 'varchar':
                case 'text':
                    return $form->addTextField($name, $defaultValue);
                case 'textarea':
                    return $form->addTextAreaField($name, $defaultValue);
                case 'number':
                case 'float':
                case 'int':
                    $element = $form->addTextField($name, $defaultValue);
                    $element->setAttribute('type', 'number');
                    return $element;
                case 'prio':
                    $element = $form->addPrioField($name);
                    $element->setLabelField($item['form_prio_label']);
                    $element->setAttribute('class', 'selectpicker form-control');
                    return $element;
                case 'multiple_select':
                    $item['multiple'] = true;
                case 'select':
                    /** @var rex_form_select_element $element */
                    $element = $form->addSelectField($name, $defaultValue);
                    $select = $element->getSelect();
                    if ($item['multiple']) {
                        $select->setMultiple($item['multiple']);
                    }
                    if (isset($item['form_select_options'])) {
                        foreach ($item['form_select_options'] as $key => $value) {
                            $select->addOption($value, $key);
                        }
                    }
                    if ((isset($item['form_select_sql_table']) or isset($item['form_select_sql_table_callback'])) && isset($item['form_select_sql_select'])) {
                        if (strpos($item['form_select_sql_table'], '::') !== false) {
                            $item['form_select_sql_table_constant'] = $item['form_select_sql_table'];
                        }
                        if (isset($item['form_select_sql_table_callback'])) {
                            $item['form_select_sql_table'] = call_user_func($item['form_select_sql_table_callback']);
                        }
                        if (isset($item['form_select_sql_table_constant'])) {
                            $item['form_select_sql_table'] = constant($item['form_select_sql_table_constant']);
                        }
                        $prefix = rex::getTablePrefix();
                        if (isset($item['form_select_sql_table_prefix'])) {
                            $prefix = $item['form_select_sql_table_prefix'];
                        }
                        $item['form_select_sql_where'] = (isset($item['form_select_sql_where'])) ? ' WHERE ' . $item['form_select_sql_where'] : '';
                        $select->addSqlOptions("SELECT {$item['form_select_sql_select']} FROM {$prefix}{$item['form_select_sql_table']} {$item['form_select_sql_where']}");
                    }
                    if (isset($item['form_select_sql'])) {
                        $select->addSqlOptions($item['form_select_sql']);
                    }
                    return $element;
                default:
                    // add exception
                    break;
            }
        }

        return null;
    }

    /**
     * @param mixed|null|rex_form_element|rex_form_widget_media_element|rex_form_widget_medialist_element $element
     * @param array $item
     * @param null $tableBaseName
     * @return mixed|null|rex_form_element|rex_form_widget_media_element|rex_form_widget_medialist_element
     * @author Joachim Doerr
     */
    public static function setElementProperties($element, array $item, $tableBaseName = null)
    {
        // add label
        if (is_object($element) && empty($element->getLabel())) {
            $label = ViewHelper::getLabel($item, 'label', $tableBaseName);
            if (isset($item['lang_key_name'])) {
                $label = $label . ' ' . $item['lang_key_name'];
            }
            $element->setLabel($label);
        }
        // add class
        if (is_object($element) && array_key_exists('form_class', $item)) {
            $element->setAttribute('class', $element->getAttribute('class') . ' ' . $item['form_class']);
        }
        // add style
        if (is_object($element) && array_key_exists('form_style', $item)) {
            $element->setAttribute('style', $item['form_style']);
        }
        // add data
        if (is_object($element) && array_key_exists('form_data', $item) && is_array($item['form_data']) && sizeof($item['form_data']) > 0) {
            foreach ($item['form_data'] as $key => $data) {
                $element->setAttribute('data-' . $key, $data);
            }
        }

        return $element;
    }

    /**
     * @param rex_form $form
     * @param $type
     * @param array $item
     * @return bool
     * @author Joachim Doerr
     */
    public static function addColumns(rex_form $form, $type, array $item = array())
    {
        switch ($type) {
            case 'wrapper':
                $form->addRawField('<div class="row">');
                return true;

            case 'column':
                $class = $item['field_column'];
                if (array_key_exists('field_class', $item)) $class .= $item['field_class'];
                $form->addRawField("<div class=\"$class\">");
                return true;

            case 'close_column':
            case 'close_wrapper':
                $form->addRawField('</div>');
                return false;
        }
    }

    /**
     * @param rex_form $form
     * @param string $type
     * @param null $key
     * @param null $curClang
     * @param string $baseClass
     * @author Joachim Doerr
     */
    public static function addLangTabs(rex_form $form, $type, $key = null, $curClang = null, $baseClass = 'base')
    {
        if (rex_clang::count() > 1) {
            switch ($type) {
                case 'wrapper':
                    $form->addRawField('<div class="'.$baseClass.'-clangtabs"><ul class="nav nav-tabs" role="tablist">');
                    foreach (rex_clang::getAll() as $clang) {
                        $active = '';
                        if ($key == $clang->getId()) {
                            $active = ' active';
                        }
                        $form->addRawField("<li role=\"presentation\" class=\"$active\"><a href=\"#lang{$clang->getId()}\" aria-controls=\"home\" role=\"tab\" data-toggle=\"tab\">{$clang->getName()}</a></li>");
                    }
                    $form->addRawField('</ul><div class="tab-content '.$baseClass.'-tabform">');
                    break;

                case 'close_wrapper':
                    $form->addRawField('</div></div>');
                    break;

                case 'inner_wrapper':
                    $active = '';
                    if ($key == $curClang) {
                        $active = ' active';
                    }
                    $form->addRawField("\n\n\n<div id=\"lang$key\" role=\"tabpanel\" class=\"tab-pane $active\">\n");
                    break;

                case 'close_inner_wrapper':
                    $form->addRawField('</div>');
                    break;
            }
        }
    }

    /**
     * @param rex_form $form
     * @param $type
     * @author Joachim Doerr
     */
    public static function closeLangTabs(rex_form $form, $type)
    {
        // close lang tabs
        self::addLangTabs($form, $type);
    }

    /**
     * @param rex_form $form
     * @param string $type
     * @param null $key
     * @param null $curKey
     * @param null $nav
     * @param string $baseClass
     * @author Joachim Doerr
     */
    public static function addTabs(rex_form $form, $type, $key = null, $curKey = null, $nav = null, $baseClass = 'base')
    {
        switch ($type) {
            case 'wrapper':
                $form->addRawField('<div class="'.$baseClass.'-tabs"><ul class="nav nav-tabs" role="tablist">');
                if (is_array($nav)) {
                    foreach ($nav as $nKey => $value) {
                        $active = '';
                        if ($key == $nKey) {
                            $active = ' active';
                        }
                        $form->addRawField("<li role=\"presentation\" class=\"$active\"><a href=\"#pnl{$nKey}\" aria-controls=\"home\" role=\"tab\" data-toggle=\"tab\">{$value}</a></li>");
                    }
                }
                $form->addRawField('</ul><div class="tab-content '.$baseClass.'-tabform">');
                break;
            case 'navigation':
                break;
            case 'close_wrapper':
                $form->addRawField('</div></div>');
                break;

            case 'inner_wrapper':
                $active = '';
                if ($key == $curKey) {
                    $active = ' active';
                }
                $form->addRawField("\n\n\n<div id=\"pnl$key\" role=\"tabpanel\" class=\"tab-pane $active\">\n");
                break;

            case 'close_inner_wrapper':
                $form->addRawField('</div>');
                break;
        }
    }

    /**
     * @param rex_form $form
     * @param $type
     * @author Joachim Doerr
     */
    public static function closeTabs(rex_form $form, $type)
    {
        // close lang tabs
        self::addTabs($form, $type);
    }

    /**
     * @param rex_form $form
     * @param $type
     * @param string $name
     * @param string $baseClass
     * @author Joachim Doerr
     */
    public static function addCollapsePanel(rex_form $form, $type, $name = '', $baseClass = 'base')
    {
        $in = '';
        switch ($type) {
            case 'wrapper':
                $keya = uniqid('a');
                $form->addRawField("<div class=\"panel-group '.$baseClass.'-panel\" id=\"$keya\">");
                break;
            case 'close_wrapper':
                $form->addRawField('</div>');
                break;
            case 'inner_wrapper_open':
                $in = 'in';
            case 'inner_wrapper':
                $keyp = uniqid('p');
                $keyc = uniqid('c');
                $form->addRawField("<div class=\"panel panel-default\" id=\"$keyp\"><div class=\"panel-heading\"><h4 class=\"panel-title\"><a data-toggle=\"collapse\" data-target=\"#$keyc\" href=\"#$keyc\" class=\"collapsed\">$name</a></h4></div> <div id=\"$keyc\" class=\"panel-collapse collapse$in\"><div class=\"panel-body\">");
                break;
            case 'close_inner_wrapper':
                $form->addRawField('</div></div></div>');
                break;
        }
    }

    /**
     * @param rex_form $form
     * @param $type
     * @author Joachim Doerr
     */
    public static function closeCollapsePanel(rex_form $form, $type)
    {
        self::addCollapsePanel($form, $type);
    }

    /**
     * @param rex_form $form
     * @param array $item
     * @param null $id
     * @param null $tableBaseName
     * @return mixed|rex_form_select_element
     * @author Joachim Doerr
     */
    public static function addStatusElement(rex_form $form, array $item, $id = null, $tableBaseName = null)
    {
        $available = false;

        foreach (rex_view::getJsFiles() as $jsFile) {
            if (strpos($jsFile, 'bsc-toggle/js') !== false) {
                $available = true;
                break;
            }
        }

        if ($available === true) {
            // add toggle button online offline
            $element = $form->addCheckboxField($item['name']);

            if (array_key_exists('label', $item)) {
                $element->setLabel(ViewHelper::getLabel($item, 'label', $tableBaseName));
            }

            $element->addOption('', 1);
            $element->setAttribute('data-bsc-toggle', 'toggle');
            $element->setAttribute('data-on', '<i class=\'rex-icon rex-icon-online\'> ' . rex_i18n::msg('clang_online'));
            $element->setAttribute('data-off', '<i class=\'rex-icon rex-icon-offline\'> ' . rex_i18n::msg('clang_offline'));
            $element->setAttribute('data-width', 160);
            $element->setAttribute('data-onstyle', 'info');
            $element->setAttribute('class', 'basecondition-toggle');
        } else {
            // fallback
            $element = $form->addSelectField('status');
            $select = $element->getSelect();
            $select->addOptions(array(1=>'online', 0=>'offline'));
            $element->setLabel(ViewHelper::getLabel($item, 'label', $tableBaseName));

            if (array_key_exists('style', $item)) {
                $element->setAttribute('style', $item['style']);
            }
        }

        return $element;
    }

    /**
     * @param rex_form $form
     * @param array $item
     * @param null $tableBaseName
     * @return mixed|rex_form_select_element
     * @throws \rex_sql_exception
     * @author Joachim Doerr
     */
    public static function addSelectField(rex_form $form, array $item, $tableBaseName = null)
    {
        $sql = rex_sql::factory();
        $sql->setQuery($item['query']);

        $element = $form->addSelectField($item['name']);
        $select = $element->getSelect();

        if (isset($item['multiple']) && $item['multiple']) {
            $select->setMultiple(true);
        }

        while($sql->hasNext()) {
            $select->addOption($sql->getValue('name'), $sql->getValue('id'), $sql->getValue('id'));
            $sql->next();
        }

        if ($form->isEditMode()) {
            $select->setSelected(explode(',', str_replace(array('[',']', '"'), '', $element->getValue())));
        }

        $element->setLabel(ViewHelper::getLabel($item, 'label', $tableBaseName));

        return $element;

    }

    /**
     * @param $message
     * @param rex_form|FormView $form
     * @param string $class
     * @param string $data
     * @return string
     * @author Joachim Doerr
     */
    public static function wrapForm($message, $form, $class = 'base-form', $data = '')
    {
        return '<div class="'.$class.'" '.$data.'>' . $message . $form->show() . '</div>';
    }

    /**
     * @param rex_extension_point $params
     * @return mixed
     * @author Joachim Doerr
     */
    public static function removeDeleteButton(rex_extension_point $params)
    {
        $subject = $params->getSubject();
        $subject['delete'] = '';
        return $subject;
    }
}