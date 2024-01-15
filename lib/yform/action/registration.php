<?php
/**
 * User: joachimdoerr
 * Date: 23.12.23
 * Time: 22:23
 */

namespace yform\action;

use BSC\Repository\AddressRepository;
use BSC\Repository\YComUserRepository;
use rex_ycom_auth;

class registration
{
    public static function registerAddress(\rex_yform_action_callback $callback): void
    {
        $user = rex_ycom_auth::getUser();
        if (!is_null($user)) {
            YComUserRepository::updateUser($user->getId(), [
                'termsofuse_accepted' => 1,
                'gender' => $callback->params['value_pool']['email']['gender'],
                'birthday' => $callback->params['value_pool']['email']['birthday']
            ]);
            AddressRepository::insertUserAddressRelation($user->getId(), $callback->getParam('main_id'));
            YComUserRepository::setTermsOfUseAccepted($user->getId());
        }
    }
}