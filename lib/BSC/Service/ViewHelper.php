<?php
/**
 * User: joachimdorr
 * Date: 04.01.20
 * Time: 18:31
 */

namespace BSC\Service;


use rex;
use rex_clang;

class ViewHelper
{
    /**
     * @param $article_page
     * @param $params
     * @return string
     * @author Joachim Doerr
     * // ViewHelper::rex_getUrl(['article_id' => $params['article_id'], 'page' => $params['page']], ['ajax_refresh' => 1])
     */
    public static function rex_getUrl(array $article_page = array(), array $params = array())
    {
        if (!isset($article_page['page'])) $article_page['page'] = null;
        if (!isset($article_page['article_id'])) $article_page['article_id'] = null;
        if (rex::isBackend()) {
            return "index.php?page={$article_page['page']}&" . http_build_query($params);
        } else {
            return rex_getUrl($article_page['article_id'], rex_clang::getCurrentId(), $params);
        }
    }
}