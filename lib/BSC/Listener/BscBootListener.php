<?php
/**
 * User: joachimdoerr
 * Date: 14.01.24
 * Time: 13:46
 */

namespace BSC\Listener;

use BSC\api;
use BSC\config;
use BSC\dispatcher;
use DateTime;
use Doctrine\Common\Annotations\AnnotationRegistry;
use rex_addon;
use rex_autoload;
use rex_extension_point;
use rex_ycom_auth;
use rex_ycom_user;
use rex_yrewrite;

class BscBootListener
{
    public static function executeBscInitBoot(rex_extension_point $ep): mixed
    {
        $subject = $ep->getSubject();

        if (!is_null(config::get('composer.autoload'))) {
            AnnotationRegistry::registerLoader([config::get('composer.autoload'), 'loadClass']);
        }

        // load ycom user
        config::setConfig(rex_addon::get('ycom')->getPlugin('auth')->getConfig(), 'ycom');

        // set user to config
        if (is_null(config::get('ycom.user'))) {
            $user = rex_ycom_auth::getUser();
            if ($user instanceof rex_ycom_user) config::set('ycom.user', $user);
        }

        // TODO später drüber nachdenken
        //    $yUser = rex_ycom_auth::getUser();
        //    if (!is_null($yUser)) rex_redirect($yComAuthConfig['article_id_logout']);

        // TODO hier noch ein EP werfen nach dem alles geladen ist damit sich irgendwer wenn nötig hier anklinken kann

        dispatcher::dispatch('BSC_INIT_DONE', true);

        // erst ganz am schluss nach dem yrewrite und ycom initialisiert wurde kann alles geladen und verarbeitet werden
        return $subject;
    }

    public static function executeBscAfterInitBootAction(rex_extension_point $ep): void
    {
        // execute after BSC INIT DONE
        // API ROUTING
        if (\rex::isFrontend()) {
            if (config::get('bsc.api.developmentMode')) {
                rex_autoload::reload(true);
            }
            api::handleRoutes();
        }
    }

    public static function executeConfigOverwrite(rex_extension_point $ep): array
    {
        // to overwrite search schemes for the default definition keys
        /** @var array $subject */
        $subject = $ep->getSubject();
        $newSubject = [];

        // laufe über alle schema
        foreach ($subject as $item) {
            $newSubject[] = $item;
            foreach (config::getConfigDefinitionKeys() as $key) { //  ['navigation', 'template', 'module/*']
                if (str_contains($item, $key)) {
                    // der mandant key entspricht der subdomain
                    $host = explode('.', rex_yrewrite::getHost()); // die subdomain ist immer da
                    // add mandant schema
                    $newSubject[] = str_replace('definitions/', "definitions/mandant/{$host[0]}/", $item);

                    // add user schema
                    if (config::get('ycom.user') instanceof rex_ycom_user) {
                        $creationDate = new DateTime(config::get('ycom.user')->getValue('creation_time'));
                        $datePath = $creationDate->format("Y/m/d");
                        $newSubject[] = str_replace('definitions/', "definitions/$datePath/user/" . config::get('ycom.user')->getId() . "/", $item);
                    }
                }
            }
        }

        // $newSubject[] = 'addons/base/resources/*.yml';
        return $newSubject;
    }

    public static function executeUrlRewriteDomainReplace(rex_extension_point $ep): string
    {
        // der domain alias wird vom yrewrite auf den domain host überschrieben, das setzen wir hiermit zurück
        // ist sicher besser als noch mehr im yrewrite addon manipulieren zu müssen
        // TODO man muss mal noch prüfen wie das mit form actions und assets frontend urls ist
        $domain = rex_yrewrite::getCurrentDomain();
        $subject = (string)$ep->getSubject();
        $domainHost = $domain->getHost();
        $currentHost = rex_yrewrite::getHost();
        return str_replace($domainHost, $currentHost, $subject);
    }

    public static function executeYComGroupPermissionSync(rex_extension_point $ep): mixed
    {
        if (\rex::isBackend()) {
            // check if is update mode
            if(!is_null(rex_request('data_id', 'int', null)) && rex_request('func') == 'edit') {
                return true;
            }
/*
            // execute USER stuff
            if ($ep->getParam('table') == config::get('table.ycom_group')) {
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
*/
        }
        return true;
    }

}