<?php
/**
 * User: joachimdorr
 * Date: 16.04.20
 * Time: 22:55
 */

namespace BSC\Listener;


use BSC\Service\TanCodeGenerator;
use BSC\Model\MemberRegister;
use BSC\Model\RegisterResend;
use BSC\Model\RegisterVerify;
use rex_extension;
use rex_extension_point;
use rex_logger;

class RegistrationActionListener extends AbstractActionListener
{
    /**
     * @param rex_extension_point $ep
     * @return MemberRegister
     * @throws \rex_sql_exception
     * @author Joachim Doerr
     */
    public static function executeMemberRegistrationAction(rex_extension_point $ep)
    {
        /** @var MemberRegister $memberRegister */
        $memberRegister = $ep->getSubject();

        try {
            self::saveOAuth2User($memberRegister);
            $result = self::getUserData('email', $memberRegister->getEmail(), false);

            TanCodeGenerator::createTanCode($result['id'], true);

            rex_extension::registerPoint(new rex_extension_point('BSC_EMAIL_MEMBER_REGISTER', $memberRegister->getEmail()));

        } catch (\Exception $e) {
            rex_logger::logException($e);
        }
        return $memberRegister;
    }

    /**
     * @param rex_extension_point $ep
     * @return RegisterResend
     * @author Joachim Doerr
     */
    public static function executeMemberRegistrationEmailResendAction(rex_extension_point $ep)
    {
        /** @var RegisterResend $registerResend */
        $registerResend = $ep->getSubject();
        rex_extension::registerPoint(new rex_extension_point('BSC_EMAIL_MEMBER_REGISTER', $registerResend->getEmail()));
        return $registerResend;
    }

    /**
     * @param rex_extension_point $ep
     * @return RegisterVerify
     * @author Joachim Doerr
     */
    public static function executeMemberSuccessVerifyAction(rex_extension_point $ep)
    {
        /** @var RegisterVerify $registerVerify */
        $registerVerify = $ep->getSubject();
        rex_extension::registerPoint(new rex_extension_point('BSC_EMAIL_MEMBER_REGISTER_DONE', $registerVerify->getEmail()));
        return $registerVerify;
    }
}