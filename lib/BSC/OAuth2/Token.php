<?php

namespace BSC\OAuth2;


use BSC\config;
use BSC\Exception\NotFoundException;
use OAuth2\Response;
use rex_sql;

class Token extends Server
{
    /**
     * @return array
     * @throws \rex_sql_exception
     * @author Joachim Doerr
     */
    public function handleToken()
    {
        $this->server->handleTokenRequest($this->request);
        $userData = array();

        if ($this->server->getResponse()->getStatusCode() == 200) {
            try {
                $sql = rex_sql::factory();
                $username = '';

                // load user check status
                if ($this->request->request['grant_type'] == 'refresh_token') {
                    $token = $this->server->getResponse()->getParameter('refresh_token');
                    if (!is_null($token)) {
                        // TODO use repository
                        $tokenData = $sql->getArray('select * from ' . config::get('table.refresh_token') . ' where refresh_token = :token', ['token' => $this->server->getResponse()->getParameter('refresh_token')]);
                        if (sizeof($tokenData) == 1) {
                            $tokenData = $tokenData[0];
                            if (isset($tokenData['user_id'])) {
                                $username = $tokenData['user_id'];
                            }
                        }
                    }
                } else {
                    if (isset($this->request->request['username'])) {
                        $username = $this->request->request['username'];
                    }
                }

                if (!empty($username)) {
                    // TODO use repository oder eine provider traid classe oder so wobei keine traid ... überlgs dir noch
                    $userData = $sql->getArray('select * from ' .config::get('table.ycom_user') . ' where login = :login', ['login' => $username]);
                }

                if (sizeof($userData) == 1) {
                    $userData = $userData[0];
                } else {
                    throw new NotFoundException('No active user found', 'user_not_found');
                }

                /*
                   -3, -> Zugang wurde gekündigt (Durch Import update oder Member-Update)
                   -2, -> Zugang wurde deaktiviert [Loginfehlversuche] (OFFEN)
                   -1, -> Zugang ist inaktiv (OFFEN)
                    0, -> Zugang wurde angefragt (erstellt by Member-Creation or Import)
                    1, -> Zugang wurde bestätigt und ist aktiv (durch QR Link + PW und Email eingabe)
                    2  -> Zugang ist aktiv (durch Double Opt-In Confirmation)
                */

                // TODO auslagern und für checkUserPermission verfügbar machen siehe api/middleware

                switch ($userData['status']) {
                    case -3:
                        self::countLoginTry($this->request->request['username'], $userData);
                        return array('user' => $userData, 'response' => new Response(array('error' => 'account_terminated', 'error_description' => 'Account is terminated by service provider'), 403));
                    case -2:
                    case -1:
                        self::countLoginTry($this->request->request['username'], $userData);
                        return array('user' => $userData, 'response' => new Response(array('error' => 'account_deactivated', 'error_description' => 'Account is inactive'), 401));
                    case 0:
                    // case 1:
                        self::countLoginTry($this->request->request['username'], $userData);
                        return array('user' => $userData, 'response' => new Response(array('error' => 'account_inactive', 'error_description' => 'Registration process is not finished'), 412));
                }

                $sql->setTable(config::get('table.ycom_user'));
                $sql->setValue('last_action_time', date('Y-m-d H:i:s'));
                $sql->setValue('last_login_time', date('Y-m-d H:i:s'));
                $sql->setValue('login_tries', 0);
                $sql->setWhere('login = :login', ['login' => $username]);
                $sql->update();

            } catch (NotFoundException $e) {
                \rex_logger::logException($e);
            }
        }

        return array('user' => $userData, 'response' => $this->server->getResponse());
    }

    /**
     * @param $username
     * @param $userData
     * @throws \rex_sql_exception
     * @author Joachim Doerr
     */
    private static function countLoginTry($username, $userData)
    {
        $tries = intval($userData['login_tries']);
        $sql = rex_sql::factory();
        $sql->setTable(config::get('table.ycom_user'));
        $sql->setValue('last_action_time', date('Y-m-d H:i:s'));
        $sql->setValue('login_tries', $tries++);
        $sql->setWhere('login = :login', ['login' => $username]);
        $sql->update();
    }
}