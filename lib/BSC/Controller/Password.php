<?php
/**
 * User: joachimdorr
 * Date: 16.04.20
 * Time: 23:07
 */

namespace BSC\Controller;

use BSC\Exception\InvalidArgumentException;
use BSC\Exception\NotFoundException;
use BSC\Model\ResetPassword;
use BSC\Model\SetResetPassword;
use Psr\Http\Message\ServerRequestInterface;
use OAuth2\Response;
use btu_portal;
use rex_yform;
use rex_password_policy;
use rex_yform_validate_password_policy;

class Password extends AbstractController
{
    /**
     * init password reset: generate key, safe to database, send mail
     *
     * @param ServerRequestInterface $request
     * @param array $args
     * @author Peter Schulze [p.schulze@bitshifters.de]
     */
    public static function passwordResetAction(ServerRequestInterface $request, array $args = []) {
        try {
            // set password reset key
            $passwordReset = self::deserialize($request->getBody()->getContents(), ResetPassword::class, 'json');
            $user = btu_portal::getParticipantByMail($passwordReset->getEmail());

            // send false positive response code if user has not double opted-in
            if(is_null($user) || (int)$user['status'] < 1) {
                return new Response([], 204);
            }
    
            $resetKey = str_pad(mt_rand(0,9999), 4, "0", STR_PAD_LEFT);
            $setKey = btu_portal::initParticipantPasswordReset($user['id'], $resetKey);

            if($setKey) {
                // send mail with password reset key
                $fields = [
                    'firstname'             => $user['firstname'],
                    'name'                  => $user['name'],
                    'password_reset_key'    => $resetKey
                ];

                $yf = new rex_yform();
                $yf->setObjectparams('csrf_protection', false);

                foreach($fields as $key => $val) {
                    $yf->setValueField('hidden', [$key, $val]);
                }

                $yf->setActionField('tpl2email', ['reset_password', $passwordReset->getEmail()]);
                $yf->getForm();
                $yf->setObjectparams('send', 1);
                $executed = $yf->executeActions();

                return new Response([], 204);
            } else {
                throw new InvalidArgumentException();
            }
        } catch (NotFoundException $e) {
            //self::getLogger()::logException($e);
            return new Response(array('error' => $e->getKey(), 'error_description' => $e->getMessage()), 401);
        } catch (InvalidArgumentException $e) {
            //self::getLogger()::logException($e);
            return new Response(array('error' => $e->getKey(), 'error_description' => $e->getMessage()), 400);
        } catch (\Exception $e) {
            //self::getLogger()::logException($e);
            return new Response(array('error' => 'internal_error', 'error_description' => $e->getMessage()), 500);
        }
    }

    /**
     * perform password reset
     *
     * @param ServerRequestInterface $request
     * @param array $args
     * @author Peter Schulze [p.schulze@bitshifters.de]
     */
    public static function passwordResetSetAction(ServerRequestInterface $request, array $args = []) {
        try {
            $setPassword = self::deserialize($request->getBody()->getContents(), SetResetPassword::class, 'json');
            $user = btu_portal::getParticipantByMail($setPassword->getEmail());

            if (!is_null($user) && $user['password_reset_key'] == $setPassword->getPasswordResetKey()) {
                // check if password is set
                if ($setPassword->getPassword() != "") {
                    // check if password matches requirements
                    $rules = json_decode(rex_yform_validate_password_policy::PASSWORD_POLICY_DEFAULT_RULES, true);
                    $policy = new rex_password_policy($rules);
                    $result = $policy->check($setPassword->getPassword());

                    if($result === true) {
                        $success = btu_portal::executeParticipantPasswordReset($user['id'], $setPassword->getPassword());

                        if (!$success) {
                            throw new Exception("Saving new password failed", "password_save_error");
                        }

                        return new Response([], 204);
                    } else {
                        throw new InvalidArgumentException(rex_getString("app_password_policy_error"), "unmatched_password_policy");
                    }
                } else {
                    throw new InvalidArgumentException(rex_getString("app_password_update_error_empty", "Empty password"), "empty_password");
                }
            } else {
                throw new InvalidArgumentException(rex_getString("app_welcome_register_error_resolvinguser", "Error resolving user"),"unknown_user");
            }
        } catch (InvalidArgumentException $e) {
            //self::getLogger()::logException($e);
            return new Response(array('error' => $e->getKey(), 'error_description' => $e->getMessage()), 400);
        } catch (\Exception $e) {
            //self::getLogger()::logException($e);
            return new Response(array('error' => 'internal_error', 'error_description' => $e->getMessage()), 500);
        }
    }
}