<?php

namespace BSC\Trait;

use BSC\Exception\NotFoundException;
use BSC\OAuth2\Server;
use OAuth2\Response;
use OAuth2\ResponseInterface;
use rex_login;
use rex_request;
use rex_ycom_auth;
use rex_ycom_user;

trait Authorization
{
    protected static ?rex_ycom_user $user = null;

    protected static function getBearer(): string
    {
        $server = $_SERVER;
        $authorizationHeader = rex_request::get('token', 'string', null);
        if (is_null($authorizationHeader)) {
            if (isset($server['HTTP_AUTHORIZATION'])) {
                $authorizationHeader = $server['HTTP_AUTHORIZATION'];
            } elseif (isset($server['REDIRECT_HTTP_AUTHORIZATION'])) {
                $authorizationHeader = $server['REDIRECT_HTTP_AUTHORIZATION'];
            } elseif (function_exists('apache_request_headers')) {
                $requestHeaders = (array)apache_request_headers();
                // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
                $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
                if (isset($requestHeaders['Authorization'])) {
                    $authorizationHeader = trim($requestHeaders['Authorization']);
                }
            }
        }
        if (!is_string($authorizationHeader)) $authorizationHeader = '';
        return str_replace('Bearer ', '', $authorizationHeader);
    }

    protected static function saveOAuth2User($member): void
    {
        $server = new Server();
        $server->saveUser($member);
    }

    protected static function getYComAuthUser(): rex_ycom_user
    {
        if (!self::$user instanceof rex_ycom_user) self::$user = rex_ycom_auth::getUser();
        if (!self::$user instanceof rex_ycom_user) throw new NotFoundException('User not found', 'user_not_found');
        return self::$user;
    }

    protected static function plainPasswordToDbPassword(string $password = null): ?array
    {
        if (empty($password)) return null;

        $hash_info = password_get_info($password);

        if (!isset($hash_info['algoName']) || 'bcrypt' != $hash_info['algoName']) {
            $hashed_value = rex_login::passwordHash($password);
        } else {
            $hashed_value = $password;
        }

        return [
            'plain_password' => $password,
            'hash' => sha1($password),
            'ycom_password' => $hashed_value,
        ];
    }

    protected static function verifyYComUserStatus(?int $id = null): ?ResponseInterface
    {
        $user = rex_ycom_auth::getUser();
        if ($user instanceof rex_ycom_user) {
            switch ((int)$user->getValue('status')) {
                case -3: return new Response(array('error' => 'account_terminated', 'error_description' => 'Account is terminated by service provider'), 403);
                case -2:
                case -1: return new Response(array('error' => 'account_deactivated', 'error_description' => 'Account is inactive'), 401);
                case  0: return new Response(array('error' => 'account_inactive', 'error_description' => 'Registration process is not finished'), 412);
            }
        } else {
            return new Response(array('error' => 'im_a_teapot', 'error_description' => 'User authentication lost'), 418);
        }
        return null;
    }
}