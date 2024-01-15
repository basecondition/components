<?php

namespace BSC\View;

abstract class AbstractView
{
    abstract public static function getView(?array $parameter = []);

    // TODO permissions manipulation
    public static function setViewUserPermissions(): void
    {
        // die zugriffsberechtigungen für yform manager werden über einen technischen user gebeipasst
        // dazu muss der user instanziert und an die rex properties gehängt werden
        if (!\rex::getProperty('user', false)) {
            $user = \rex_user::require(3);
            // TODO manipulation der permissions je nach ycom user groups
            // TODO SEHR SEHR SEHR WICHTIG!!!
            \rex::setProperty('user', $user);
        }
    }

    public static function returnDefaultOutputOrAjaxCleanBuffers(string $output)
    {
        // printe nur den output wenn ajax call
        if (\rex_request::get('ajax', 'int', 0)) {
            \rex_response::cleanOutputBuffers();
            \rex_response::sendContent($output);
            die;
        }
        return $output;
    }
}