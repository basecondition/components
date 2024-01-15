<?php

namespace BSC\OAuth2;


use BSC\Model\Member;
use BSC\Model\MemberRegister;
use OAuth2\GrantType\AuthorizationCode;
use OAuth2\GrantType\ClientCredentials;
use OAuth2\GrantType\RefreshToken;
use OAuth2\GrantType\UserCredentials;
use OAuth2\Request;
use OAuth2\Storage\Pdo;
use rex;
use rex_logger;

class Server
{
    protected \OAuth2\Server $server;

    protected Pdo $storage;

    protected Request $request;

    protected rex_logger $logger;

    public function __construct()
    {
        $this->logger = rex_logger::factory();
        $dbconfig = rex::getProperty('db');
        $db       = $dbconfig[1];
        $dsn      = "mysql:dbname={$db['name']};host={$db['host']}";
        $username = $db['login'];
        $password = $db['password'];

        $this->storage = new Pdo(array('dsn' => $dsn, 'username' => $username, 'password' => $password));
        # $this->storage->setUser('bshaffer', 'brent123', 'Brent', 'Shaffer');
        $this->server = new \OAuth2\Server($this->storage, array(
            'access_lifetime' => 86400, // 24 h
            'refresh_token_lifetime' => 3600 * 24 * 30,
        ));
        $this->server->addGrantType(new ClientCredentials($this->storage));
        $this->server->addGrantType(new AuthorizationCode($this->storage));
        $this->server->addGrantType(new RefreshToken($this->storage, [
            'always_issue_new_refresh_token' => true,
            'refresh_token_lifetime' => 3600 * 24 * 30,
            //'refresh_token_lifetime' => 2419200, // 672 h
        ]));
        $this->server->addGrantType(new UserCredentials($this->storage));
        $this->request = Request::createFromGlobals();
    }

    public function saveUser(Member|MemberRegister|null $user): bool
    {
        if ($user instanceof Member || $user instanceof MemberRegister) {
            $user = array(
                'login' => $user->getEmail(),
                'password' => $user->getPassword(),
                'firstname' => $user->getFirstname(),
                'name' => $user->getName(),
            );
        }
        if (is_array($user) &&
            isset($user['login']) && !empty($user['login']) &&
            isset($user['password']) && !empty($user['password'])
        ) {
            $userLogClone = $user;
            $userLogClone['password'] = '***';
            $this->logger->log(INFO_GENERAL,'save hash for : ' . print_r($userLogClone, true));
            $this->storage->setUser($user['login'], $user['password'], ($user['firstname']) ? $user['firstname'] : null, ($user['name']) ? $user['name'] : null);
        }
        return true;
    }
}