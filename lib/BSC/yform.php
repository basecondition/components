<?php

namespace BSC;

use rex_clang;
use rex_yform;

class yform extends \rex_yform_manager
{

    public function executeOutputReplacements(string $output): string
    {
        $url = rex_getUrl(\rex_article::getCurrentId());
        $search = [
            '/index.php?page=',
            'index.php?page=',
            $url,
            urlencode($url),
            'data-toggle',
        ];
        $replace = [
            '',
            '',
            $url . '?',
            urldecode($url) . '?',
            'data-bs-toggle',
        ];

        return str_replace($search, $replace, $output);
    }

    public static function getYComForm(string $formData, string $formName = 'ycom_REX_SLICE_ID', array $params = [], string $anker = ''): rex_yform {
        $yForm = new rex_yform();
        $yForm->setObjectparams('form_action',rex_getUrl('REX_ARTICLE_ID', rex_clang::getCurrentId(), $params) . $anker);
        $yForm->setObjectparams('form_ytemplate', 'metronic,bootstrap');
        $yForm->setObjectparams('hide_top_warning_messages', false);
        $yForm->setObjectparams('hide_field_warning_messages', true);
        $yForm->setObjectparams('form_showformafterupdate', 0);
        $yForm->setObjectparams('form_name', $formName);
        $yForm->setObjectparams('real_field_names', false);
        $yForm->setFormData(trim(rex_yform::unhtmlentities($formData)));
        return $yForm;
    }
}