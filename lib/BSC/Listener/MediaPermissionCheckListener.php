<?php
/**
 * User: joachimdorr
 * Date: 10.06.20
 * Time: 15:49
 */

namespace BSC\Listener;


use BSC\Api\Middleware;
use BSC\Service\MediaPermissionHelper;
use rex_backend_login;
use rex_media;
use rex_media_manager;
use rex_response;
use rex_ycom_auth;
use rex_ycom_user;

class MediaPermissionCheckListener
{
    /**
     * @param \rex_extension_point $ep
     * @return mixed|null
     * @throws \rex_exception
     * @author Joachim Doerr
     */
    public static function executeMediaPermissionCheckAction(\rex_extension_point $ep)
    {
        // return media for backend usage
        if (rex_backend_login::hasSession()) {
            return $ep->getSubject();
        }

        // add ycom user by token
        Middleware::processWithoutRouting();
    
        // check med_participants
        /** @var rex_media_manager $mediaManager */
        $mediaManager = $ep->getSubject();
        $media = rex_media::get($mediaManager->getMedia()->getMediaFilename());

        $permitted = false;

        $mediaPermissionList = MediaPermissionHelper::checkMediaPermission([$media]);
        
        if (isset($mediaPermissionList[0]) && array_key_exists('is_permitted', $mediaPermissionList[0])) {
            $permitted = $mediaPermissionList[0]['is_permitted'];
        }

        if ($permitted) {
            $ep->setParam('ycom_ignore', 1);
            return $ep->getSubject();
        } else {
            rex_response::setStatus(403);
            rex_response::sendContent('');
            exit();
        }
    }
}