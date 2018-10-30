<?php
/**
 * User: joachimdoerr
 * Date: 21.10.18
 * Time: 18:49
 */

namespace Basecondition\Provider;


use rex_dir;
use rex_exception;
use rex_logger;
use rex_path;
use rex_url;
use rex_view;

class BaseconditionAssetsProvider
{
    const VENDOR_PATH = '/vendor/basecondition/components';
    const ASSETS_PATH = '/assets';

    /**
     * @author Joachim Doerr
     * @param bool $install
     */
    public static function provideBaseconditionTools($install = false)
    {
        if ($install) {
            rex_dir::copy(self::getAssetsBasePath(), rex_path::addonAssets('basecondition'));
        }

        // add js vendors
        self::addJS([
            'basecondition-numeric'     => '/bsc-autonumeric/bsc-autonumeric.js',
            'basecondition-datepicker'  => '/bsc-daterangepicker/bsc-daterangepicker.js',
            'basecondition-select'      => '/bsc-multiselect/bsc-multiselect.js',
            'basecondition-toggle'      => '/bsc-toggle/bsc-toggle.js',
            'baseconsition-tagsinput'   => '/bsc-tagsinput/bsc-tagsinput.js',
            'basecondition-tools'       => '/bsc-plugins/bsc-plugins.js'
        ]);
        // add css vendors
        self::addCss([
            'basecondition-datepicker'  => '/bsc-daterangepicker/bsc-daterangepicker.css',
            'basecondition-select'      => '/bsc-multiselect/bsc-multiselect.css',
            'basecondition-toggle'      => '/bsc-toggle/bsc-toggle.css',
            'baseconsition-tagsinput'   => '/bsc-tagsinput/bsc-tagsinput.css',
            'basecondition-tools'       => '/bsc-plugins/bsc-plugins.css'
        ]);
    }

    /**
     * @param array $js
     * @author Joachim Doerr
     */
    private static function addJS(array $js)
    {
        foreach ($js as $name => $fullPathFile) {
            $add = true;
            foreach (rex_view::getJsFiles() as $jsFile) {
                if (strpos($jsFile, $name) !== false) {
                    $add = false;
                }
            }
            if ($add) {
                try {
                    rex_view::addJsFile(rex_url::addonAssets('basecondition', substr($fullPathFile, 1)));
                } catch (rex_exception $e) {
                    rex_logger::logException($e);
                }
            }
        }
    }

    /**
     * @param array $css
     * @author Joachim Doerr
     */
    private static function addCss(array $css)
    {
        foreach ($css as $name => $fullPathFile) {
            $add = true;
            if (isset(rex_view::getCssFiles()['all'])) {
                foreach (rex_view::getCssFiles()['all'] as $cssFile) {
                    if (strpos($cssFile, $name) !== false) {
                        $add = false;
                    }
                }
            }
            if ($add) {
                try {
                    rex_view::addCssFile(rex_url::addonAssets('basecondition', substr($fullPathFile, 1)));
                } catch (rex_exception $e) {
                    rex_logger::logException($e);
                }
            }
        }
    }

    /**
     * @return string
     * @author Joachim Doerr
     */
    private static function getAssetsBasePath()
    {
        $dir = explode(self::VENDOR_PATH, __DIR__);
        return $dir[0] . self::VENDOR_PATH . self::ASSETS_PATH;
    }

}