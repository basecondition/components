<?php

namespace BSC\Listener;


use BSC\config;
use btu_portal;
use BSC\Model\MemberPatch;
use BSC\Model\MemberRegister;
use BSC\OAuth2\Server;
use rex_extension_point;
use rex_logger;
use rex_sql;
use rex_extension;
use rex_ycom_user;
use rex_yform_email_template;
use rex_yform_value_text;

class MemberActionListener extends AbstractActionListener
{
    /**
     * @param rex_extension_point $ep
     * @return bool
     * @throws \rex_sql_exception
     * @author Joachim Doerr
     */
    public static function executeMemberDeleteAction(rex_extension_point $ep)
    {
        /** @var \rex_ycom_user $yUser */
        $yUser = $ep->getSubject();

        try {
            // delete btu participant
            return btu_portal::deleteParticipant($yUser->getId(), 'API');
        } catch (\Exception $e) {
            rex_logger::logException($e);
            return false;
        }
    }

    /**
     * @param rex_extension_point $ep
     * @return boolean
     * @throws \rex_exception
     * @author Joachim Doerr
     */
    public static function executePasswordResetAction(rex_extension_point $ep)
    {
        $template_name = 'reset_password';
        $subject = $ep->getSubject();
        $userData = $subject['data'];

        if ($userData instanceof \stdClass) {
            $userData = (array)$userData;
        }

        if (isset($userData['data']) && $userData['data'] instanceof \stdClass) {
            $userData['data'] = (array)$userData['data'];
        }

        $etpl = rex_yform_email_template::getTemplate($template_name);
        $etpl = rex_yform_email_template::replaceVars($etpl, $userData);
        $etpl['mail_to'] = $userData['email'];
        $etpl['mail_to_name'] = $userData['email'];

        // send mail
        return (rex_yform_email_template::sendMail($etpl, $template_name));
    }

    /**
     * @param rex_extension_point $ep
     * @return boolean
     * @throws \rex_sql_exception
     * @throws \Exception
     * @author Joachim Doerr
     */
    public static function executeMemberYFormSaveAction(rex_extension_point $ep)
    {
        if (\rex::isBackend()) {
            // check if is update mode
            if(!is_null(rex_request('data_id', 'int', null)) && rex_request('func') == 'edit') {
                return true;
            }

            // execute USER stuff
            if ($ep->getParam('table') == config::get('table.ycom_user')) {
                $email = null;

                foreach ($ep->getParam('form')->getParam('values') as $value) {
                    //echo $value->getName().": ".$value->getValue()."<br />\n";

                    if ($value instanceof rex_yform_value_text) {
                        if ($value->getName() == 'email') {
                            $email = $value->getValue();
                        }
                    }
                }

                // go on with registration as if performed via API, just change
                try {
                    $user = btu_portal::getParticipantByMail($email);
                    $memberRegister = new MemberRegister($user);

                    self::saveOAuth2User($memberRegister);

                    rex_extension::registerPoint(new rex_extension_point('BSC_EMAIL_MEMBER_REGISTER', $user['email'], ["email_template" => "registration_byadmin_de"]));

                } catch (\Exception $e) {
                    rex_logger::logException($e);
                }
            }
        }

        return true;
    }

    /**
     * @param rex_extension_point $ep
     * @return bool
     * @author Joachim Doerr
     */
    public static function executeMemberSetPWAction(rex_extension_point $ep)
    {
        $subject = $ep->getSubject();
        $user = $subject['user'];
        $user['password'] = $subject['password'];
        $user['hash'] = sha1($subject['password']);

        $server = new Server();
        $server->saveUser($user);

        return true;
    }

    /**
     * @param rex_extension_point $ep
     * @return bool
     * @throws \rex_sql_exception
     * @author Joachim Doerr
     */
    public static function executeMemberPWResetAction(rex_extension_point $ep)
    {
        $form = $ep->getParam('form');
        $sql = rex_sql::factory();
        $user = $sql->getArray("SELECT * FROM ".config::get('table.ycom_user')." WHERE activation_key = :key", [':key' => $form->params['value_pool']['sql']['token']]);

        if (sizeof($user) == 1) {
            $user = $user[0];
        }

        $user['password'] = $form->params['value_pool']['sql']['password'];
        $user['hash'] = sha1($form->params['value_pool']['sql']['password']);
        $server = new Server();
        $server->saveUser($user);

        $sql = rex_sql::factory();
        $sql->setTable(config::get('table.ycom_user'));
        $sql->setValue('new_password_required', 0);
        $sql->setWhere('activation_key = :key', [':key' => $form->params['value_pool']['sql']['token']]);
        $sql->update();

        return true;
    }

    /**
     * @param rex_extension_point $ep
     * @return bool
     * @throws \rex_sql_exception
     * @author Joachim Doerr
     */
    public static function executeMemberActivateAction(rex_extension_point $ep)
    {
        $activationAction = ($ep->getParam('action') == 'ycom_activate_account');
        $template_name = 'confirm_registration';

        if ($ep->getParam('yform')) {
            $form = $ep->getParam('form');
            $key = $form->params['value_pool']['sql']['token'];
            $sql = rex_sql::factory();
            $user = $sql->getArray("SELECT * FROM ".config::get('table.ycom_user')." WHERE activation_key = :key", [':key' => $key]);

            if (sizeof($user) == 1) {
                $user = $user[0];
            } else {
                $user = null;
            }
        } else {
            $user = $ep->getParam('userData');

            if ($user instanceof \stdClass) {
                $user = (array)$user;
            }

            $data = $ep->getParam('data');
            $user['password'] = $data->password;
        }

        if ($activationAction && is_array($user)) {
            // load user
            $user['activation_key'] = sha1(uniqid(rand(), true));

            if ($user['status'] == 1) {
                $user['hash'] = sha1($user['password']);

                $server = new Server();
                $server->saveUser($user);

                // update token
                $sql = rex_sql::factory();
                $sql->setTable(config::get('table.ycom_user'));
                $sql->setValue('activation_key', $user['activation_key']);
                $sql->setWhere('id = :id', [':id' => $user['id']]);
                $sql->update();

                $etpl = rex_yform_email_template::getTemplate($template_name);
                $etpl = rex_yform_email_template::replaceVars($etpl, $user);

                $etpl['mail_to'] = $user['email'];
                $etpl['mail_to_name'] = $user['email'];

                // send mail
                return (rex_yform_email_template::sendMail($etpl, $template_name));
            }
        }
        return true;
    }

    public static function executeApiPostMemberUpdateAction(rex_extension_point $ep)
    {
        $subject = $ep->getSubject();
        /** @var MemberPatch $member */
        $member = $subject['member_patch'];
        /** @var rex_ycom_user $yUser */
        $yUser = $subject['y_user'];
        $password = $subject['password'];
        $changeAuthUser = false;
        $changeAuthPassword = false;
        $lastEmail = $yUser->getValue('email');

        $user = [
            'email' => $yUser->getValue('email'),
            'login' => $yUser->getValue('email'),
            'firstname' => (!empty($member->getFirstname())) ? $member->getFirstname() : $yUser->getValue('firstname'),
            'name' => (!empty($member->getName())) ? $member->getName() : $yUser->getValue('name'),
        ];

        if (!empty($member->getEmail()) && $yUser->getValue('email') != $member->getEmail()) {
            // email changed
            $changeAuthUser = true;
            $user['email'] = $member->getEmail();
            $user['login'] = $member->getEmail();
        }

        if (!is_null($password) && is_array($password) && isset($password['plain_password'])) {
            // pw was updated
            $changeAuthPassword = true;
            $user['password'] = $password['plain_password'];
        }

        try {
            if ($changeAuthUser) {
                // login
                $sql = rex_sql::factory();
                $sql->setTable(config::get('table.auth_user'));
                $sql->setValue('username', $user['login']);
                $sql->setWhere('username = :mail', ['mail' => $lastEmail]);
                $sql->update();

                // token
                $sql = rex_sql::factory();
                $sql->setTable(config::get('table.token'));
                $sql->setValue('user_id', $user['login']);
                $sql->setWhere('user_id = :mail', ['mail' => $lastEmail]);
                $sql->update();

                // refresh token
                $sql = rex_sql::factory();
                $sql->setTable(config::get('table.refresh_token'));
                $sql->setValue('user_id', $user['login']);
                $sql->setWhere('user_id = :mail', ['mail' => $lastEmail]);
                $sql->update();
            }
            if ($changeAuthPassword) {
                $server = new Server();
                $server->saveUser($user);
            }
        } catch (\Exception $e) {
            rex_logger::logException($e);
            return false;
        }

        return $subject;
    }
}