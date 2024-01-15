<?php

namespace BSC\View;


use BSC\config;
use BSC\yform;

class UserManagementView extends AbstractView
{
    // get manipulated yform manager output
    public static function getView(?array $parameter = []): string
    {
        // legt den nötigen rex backend user an
        // TODO manipuliere die permissions
        parent::setViewUserPermissions();
        // TODO auslesen ob wir hier ein edit oder add
        //  haben und dann entsprechend damit weiter arbeiten
        //  ... noch offen
        $func = rex_request('func', 'string', '');

        // TODO später duch data table austauschen
        //  https://preview.keenthemes.com/html/metronic/docs/general/datatables/server-side
        return self::getYManagerView();
    }

    private static function getYManagerView(): string
    {
        // überschreibt die yform manager fragmente speziel für diesen view
        // frontend/ycom_user_fragments/yform/manager/page/list.php
        \rex_fragment::addDirectory(\rex_path::src('fragments/ycom_user_fragments'));

        // liest die tabellen manager definition für die user tabelle aus
        $table = \rex_yform_manager_table::get('rex_ycom_user');
        $table->offsetSet('search', false); // deaktiviert für die list view die suche damit wir nur die liste erzeugen
        $table->offsetSet('list_amount', 30); // anzahl der listen punkte die angezeigt werden sollen, wenn mehr vorhanden wird paginiert
//        dump($table);

        $page = new yform();
        $page->setTable($table);
        $page->setLinkVars(['page' => rex_getUrl(\rex_article::getCurrentId())]);

        // EXTENSION MANIPULATION
        // der page header muss entfernt werden
        \rex_extension::register('YFORM_MANAGER_DATA_PAGE_HEADER', function(\rex_extension_point $ep) {return '';});
        // für die user verwaltung sollte es keine clonen funktion geben
        \rex_extension::register('YFORM_DATA_LIST_ACTION_BUTTONS', function(\rex_extension_point $ep) {
            /** @var array $buttonConfig */
            $buttonConfig = $ep->getSubject();
            unset($buttonConfig['clone']);
            return $buttonConfig;
        });
        // das hier ist für die mulitmandanten fähigkeit EXTREM WICHTIG!
        // es werden nur die user ausgeliefert welche zur mandanten gruppe passt
        \rex_extension::register('YFORM_DATA_LIST_QUERY', function(\rex_extension_point $ep) {
            /** @var \rex_yform_manager_query $managerQuery */
            $managerQuery = $ep->getSubject();
            $managerQuery->whereListContains('ycom_groups', config::get('mandant.ycom_group.id'));
            return $managerQuery;
        });
        \rex_extension::register('YFORM_DATA_LIST', function(\rex_extension_point $ep) {
            $list = $ep->getSubject();
//            dump($list);
            return $list;
        });

        // weitere list view anpassungen müssen im fragment vorgenommen werden
        // frontend/ycom_user_fragments/yform/manager/page/list.php
        return parent::returnDefaultOutputOrAjaxCleanBuffers($page->executeOutputReplacements($page->getDataPage())); // potential clean buffers for ajax
    }
}