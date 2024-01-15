<?php
/**
 * User: joachimdorr
 * Date: 16.04.20
 * Time: 09:16
 */

namespace BSC\Listener;


use btu_portal;
use BSC\Exception\NotFoundException;
use rex_extension_point;
use rex_logger;
use rex_yform;
use rex_global_settings;

class MailSendActionListener extends AbstractActionListener
{
    /**
     * @param rex_extension_point $ep
     * @return mixed
     * @author Joachim Doerr
     */
    public static function executeSendRegistrationEmailAction(rex_extension_point $ep)
    {
        $email = $ep->getSubject();
        $emailTPL = $ep->getParam("email_template", "registration_de");

        try {
            $user = btu_portal::getParticipantByMail($email);

            $verificationURL = rex_global_settings::getDefaultValue("frontendURL");
            $verificationURL .= '?'.http_build_query([
                'firstname'         => $user['firstname'],
                'name'              => $user['name'],
                'gender'            => $user['gender'],
                'birthday'          => $user['birthday'],
                'zipcode'           => $user['zipcode'],
                'place'             => $user['place'],
                'country'           => $user['country'],
            ]);

            // send mail with verification key
            $fields = [
                'firstname'         => $user['firstname'],
                'name'              => $user['name'],
                'verification_key'  => $user['verification_key'],
                'verification_url'  => $verificationURL
            ];

            $yf = new rex_yform();
            $yf->setObjectparams('csrf_protection', false);

            foreach($fields as $key => $val) {
                $yf->setValueField('hidden', [$key, $val]);
            }

            $yf->setActionField('tpl2email', [$emailTPL, $user['email']]);
            $yf->getForm();
            $yf->setObjectparams('send', 1);

            return ($executed = $yf->executeActions());

        } catch (\Exception $e) {
            rex_logger::logException($e);
            return false;
        }
    }

    /**
     * @param rex_extension_point $ep
     * @return bool
     * @throws \rex_sql_exception
     * @author Joachim Doerr
     */
    public static function executeSendRegistrationDoneEmailAction(rex_extension_point $ep)
    {
        $email = $ep->getSubject();

        try {
            $user = btu_portal::getParticipantByMail($email);

            // send mail with verification key
            $files = btu_portal::getParticipantQR($user['id']);
            $fields = [
                'firstname'         => $user['firstname'],
                'name'              => $user['name'],
                'verification_key'  => $user['verification_key'],
                'qr_image_filename' => $files['image'],
                'qr_pdf_attachment' => $files['pdf']
            ];

            $yf = new rex_yform();
            $yf->setObjectparams('csrf_protection', false);

            foreach($fields as $key => $val) {
                $yf->setValueField('hidden', [$key, $val]);
            }

            $yf->setValueField('upload', array('upload','Dateianhang','1,9999999','.pdf'));
            $yf->setValueField('php', array('php_attach', 'attach_qr_pdf', '<?php $this->params[\'value_pool\'][\'email_attachments\'][] = [\''.rex_getString("email_template_qr_attachment_name").'.pdf\', rex_path::base(\'codes/'.$fields['qr_pdf_attachment'].'\')]; ?>'));
            $yf->setActionField('tpl2email', ["welcome_de", $user['email']]);
            $yf->getForm();
            $yf->setObjectparams('send', 1);

//            var_dump($email);
//            var_dump($user['email']);
//            die();

            return (bool)($executed = $yf->executeActions());

        } catch (\Exception $e) {
            rex_logger::logException($e);
            return false;
        }
    }
}