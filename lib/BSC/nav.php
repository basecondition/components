<?php

namespace BSC;

use rex_article;
use rex_category;
use rex_navbuilder;

class nav
{
    public static function getFragmentConfig(string $configKey): array
    {
        $config = base::config($configKey); // setze die default config werte
        unset($config['items']); // entferne die items
        $config['items'] = self::getNavArrayByConfig($configKey); // bereitet die items korrekt auf setze sie erneut
        return $config;
    }

    private static function createNavArray(?array $navConfig = null, int $deep = 1, bool $ignoreOfflines = true): array
    {
        $nav = [];
        if (isset($navConfig['items'])) {
            $items = $navConfig['items'];
            if (is_array($items)) {
                foreach ($items as $item) {
                    $mergeChildren = (isset($item['merge'])) ? $item['merge'] : false;
                    $article = (isset($item['artId'])) ? rex_article::get($item['artId']) : null;
                    $category = (isset($item['catId'])) ? rex_category::get($item['catId']) : null;
                    $itemDeep = (isset($item['deep'])) ? $item['deep'] : $deep;

                    if (isset($item['navBuilder'])) { // ist ein navBuilder key vorhanden wird hier das gesamte NavBuilder menu ausgelesen
                        $menuKeyNav = self::getNavArrayByNavBuilderKey($item['navBuilder'], $itemDeep, $ignoreOfflines);
                        $nav = array_filter(array_merge($nav, $menuKeyNav));
                        continue;
                    }

                    if ($category instanceof rex_category) {
                        if (!self::isCategoryPermitted($category)) continue;
                        $categoryItems = navArray($category->getId(), $itemDeep, $ignoreOfflines);
                        if ($mergeChildren === true) {
                            $nav = array_merge($nav, $categoryItems);
                        } else {
                            // add category article with children
                            $catStartArticle = array_merge($item, self::getNavArrayItemByArticle($category->getStartArticle(), $ignoreOfflines));
                            $catStartArticle['hasChildren'] = (count($categoryItems) > 0);
                            $catStartArticle['children'] = $categoryItems;
                            $nav[] = $catStartArticle;
                        }
                    } else if ($article instanceof rex_article) {
                        if (!self::isArticlePermitted($article)) continue;
                        $nav[] = array_merge($item, self::getNavArrayItemByArticle($article, $ignoreOfflines));
                    } else if (isset($item['legend'])) {
                        $nav[] = $item;
                    } else if (isset($item['fragment'])) {
                        $nav[] = $item;
                    } else if (isset($item['url'])) {
                        $nav[] = $item;
                    }
                }
            }
        }
        return $nav;
    }

    public static function getNavArrayByConfig(?string $configKey, int $deep = 1, bool $ignoreOfflines = true): array
    {
        if (empty($configKey)) return [];
        $navConfig = base::config($configKey);
        if (!is_null($navConfig)) {
            return self::createNavArray($navConfig, $deep, $ignoreOfflines);
        }
        return [];
    }

    private static function isArticlePermitted(rex_article $article): bool
    {
        $ycomCheck = \rex_addon::get('ycom')->getPlugin('auth')->isAvailable();
        return !$ycomCheck || $article->isPermitted();
    }

    private static function isCategoryPermitted(rex_category $category): bool
    {
        $ycomCheck = \rex_addon::get('ycom')->getPlugin('auth')->isAvailable();
        return !$ycomCheck || $category->isPermitted();
    }

    private static function getNavArrayItemByArticle(?rex_article $article = null, bool $ignoreOfflines = true)
    {
        if (!$article instanceof rex_article || !$article->isOnline() && $ignoreOfflines) return null;
        $path = array_filter(explode('|',rex_article::getCurrent()->getPath()));
        return [
            'artId' => $article->getId(),
            'catId' => $article->getCategoryId(),
            'parentId' => $article->getParentId(),
            'level' => 0,
            'catName' => $article->getName(),
            'url' => $article->getUrl(),
            'hasChildren' => false,
            'children' => null,
            'path' => $article->getPath(),
            'active' => in_array($article->getId(), $path) || rex_article::getCurrentId() == $article->getId(),
            'current' => rex_article::getCurrentId() == $article->getId(),
            'artObject' => $article,
            'catObject' => $article->getCategory()
        ];
    }

    public static function getNavArrayByNavBuilderKey(?string $navBuilderKey, int $deep = 1, bool $ignoreOfflines = true): array
    {
        if (empty($navBuilderKey)) return [];
        return self::buildNavigation(rex_navbuilder::getStructure($navBuilderKey), 0, $deep, $ignoreOfflines);
    }

    private static function buildNavigation(?array $items = [], $depth = 0, int $deep = 1, bool $ignoreOfflines = true)
    {
        if (count($items) <= 0) return [];
        $navItems = [];
        foreach ($items as $item) {
            if (!isset($item['type'])) continue;
            if ($item['type'] == 'intern' || $item['type'] == 'extern') {
                $article = rex_article::get($item['id']);
                if (!self::isArticlePermitted($article)) continue;
                $navItems[] = self::getNavArrayByArticle($article, $deep, $ignoreOfflines);
            } else if ($item['type'] == 'group' && isset($item['children']) && is_array($item['children']) && count($item['children']) > 0) {
                $buildNavItems = self::buildNavigation($item['children'], $depth+1, $deep, $ignoreOfflines);
                $item['children'] = $buildNavItems;
                if (count($buildNavItems) == 1 && $depth == 0) {
                    $navItems = array_merge($navItems, $buildNavItems);
                } else if (count($buildNavItems) > 1) {
                    $navItems[] = $item;
                }
            }
        }
        return $navItems;
    }

    private static function getNavArrayByArticle(?rex_article $article = null, int $deep = 1, bool $ignoreOfflines = false): array
    {
        if (!is_null($article)) {
            $item = self::getNavArrayItemByArticle($article, $ignoreOfflines);
            if ($deep > 1 || !$article->isStartArticle()) {
                $item['children'] = navArray($article->getCategoryId(), $deep, $ignoreOfflines);
                $item['hasChildren'] = true;
            }
            return $item;
        }
        return [];
    }

    public static function getPath(int $level = null): array|int
    {
        $path = array_values(array_filter(explode('|', rex_article::getCurrent()->getValue('path') . rex_article::getCurrent()->getId() . '|')));
        if (isset($path[$level])) {
            return (int)$path[$level];
        }
        return $path;
    }
}