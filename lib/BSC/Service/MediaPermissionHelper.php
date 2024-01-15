<?php
/**
 * User: joachimdorr
 * Date: 17.07.20
 * Time: 13:01
 */

namespace BSC\Service;


use rex_backend_login;
use rex_media;
use rex_ycom_auth;
use rex_ycom_user;

class MediaPermissionHelper
{
    /**
     * @param array $mediaList
     * @return array
     * @throws \rex_sql_exception
     * @author Joachim Doerr
     */
    public static function checkMediaPermission(array $mediaList)
    {
        /** @var rex_ycom_user $user */
        $user = rex_ycom_auth::getUser();

        $mediaPermissionList = [];
        // check milestone group permission
        $milestones = ($user instanceof rex_ycom_user) ? \btu_portal::getParticipantMilestones($user->getId()) : array();
        $groups = [];

        // find groups by milestones
        if (is_array($milestones) && sizeof($milestones) > 0) {
            foreach ($milestones as $milestone) {
                if (isset($milestone['type']) && is_numeric($milestone['type']) && $milestone['type'] > 0 && !in_array($milestone['type'], $groups)) {
                    $groups[] = $milestone['type'];
                }
            }
        }

        foreach ($mediaList as $key => $media) {
            if (!$media instanceof rex_media && is_string($media)) {
                // convert item to rex_media objc
                $media = rex_media::get($media);
            }

            // check for non existing media
            if(is_null($media)) {
                continue;
            }

            $mediaPermissionList[$key] = ['filename' => $media->getFileName(), 'media' => $media, 'is_permitted' => false];

            $authType = (int)$media->getValue('ycom_auth_type');
            $groupType = (int)$media->getValue('ycom_group_type');
            $mediaGroups = array_filter(explode(',', $media->getValue('ycom_groups')));
            $participants = array_filter(explode('|', $media->getValue('med_participants')));

            // final backend check
            if (rex_backend_login::hasSession()) {
                $mediaPermissionList[$key]['is_permitted'] = true;
                continue;
            }

            // if there is a list of specified users, check if the calling user is in ...
            // DEPRECATED:  && !in_array(2, $mediaGroups)
            if (count($participants) > 0 && (!$user instanceof rex_ycom_user || !in_array($user->getId(), $participants))) {
                $mediaPermissionList[$key]['is_permitted'] = false;
                continue;
            }

            // for all users, no matter what milestone ?
            if($authType == 0) {
                $mediaPermissionList[$key]['is_permitted'] = true;
                continue;
            }

            switch($groupType) {
                // atleast 1 milestone required
                case 0:
                    $mediaPermissionList[$key]['is_permitted'] = (count($groups) > 0);
                    break;
                // user needs every provided milestone
                case 1:
                    if(count($mediaGroups) == 0) {
                        $mediaPermissionList[$key]['is_permitted'] = true;
                    } elseif(count($groups) == 0) {
                        $mediaPermissionList[$key]['is_permitted'] = false;
                    } else {
                        // compare groups with media groups
                        $matchingGroups = 0;

                        foreach ($groups as $groupId) {
                            if (in_array($groupId, $mediaGroups)) {
                                $matchingGroups++;
                            }
                        }

                        $mediaPermissionList[$key]['is_permitted'] = ($matchingGroups == count($mediaGroups));
                    }
                    break;
                // user needs atleast 1 match with provided milestones
                case 2:
                    if(count($mediaGroups) == 0) {
                        $mediaPermissionList[$key]['is_permitted'] = true;
                    } elseif(count($groups) == 0) {
                        $mediaPermissionList[$key]['is_permitted'] = false;
                    } else {
                        $mediaPermissionList[$key]['is_permitted'] = false; // default false
                        // compare groups with media groups
                        foreach ($groups as $groupId) {
                            if (in_array($groupId, $mediaGroups)) {
                                $mediaPermissionList[$key]['is_permitted'] = true; // or true
                                break;
                            }
                        }
                    }
                    break;
                // for all users without any milestone
                case 3:
                    $mediaPermissionList[$key]['is_permitted'] = (count($groups) == 0);
                    break;
            }
        }

        return $mediaPermissionList;
    }
}