<?php

namespace BSC;

use Multiavatar;
use rex_ycom_user;

class avatar
{
    protected static function getUserAvatarId(rex_ycom_user $user): string
    {
        // TODO check if a user custom avatar id exist?
        if (empty(base::get('ycom.user.avatar.id'))) {
            config::set('ycom.user.avatar.id', $user->getValue('id') . $user->getValue('firstname') . $user->getValue('name') . $user->getValue('birthday'));
        }
        return base::get('ycom.user.avatar.id');
    }

    public static function getUserAvatar(rex_ycom_user $user): array
    {
        // TODO check if a avatar img path exist?
        if (empty(base::get('ycom.user.avatar.path'))) { }
        // fallback or svg img by custom user avatar id
        if (empty(base::get('ycom.user.avatar.svg'))) {
            config::overwriteDefinition(self::getMultiAvatar(self::getUserAvatarId($user)), 'ycom.user.avatar');
        }
        return base::get('ycom.user.avatar');
    }

    public static function getMultiAvatar(string $avatarId = null): array
    {
        $multiAvatar = new Multiavatar();
        $avatarId = (!empty($avatarId)) ? $avatarId : uniqid(rand(), true);
        $svgCode = $multiAvatar($avatarId, null, null);
        return [
            'id' => $avatarId,
            'svg' => $svgCode
        ];
    }
}