<?php

namespace FriendsOfBotble\SocialLogin\Zalo;

use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Botble\Setting\Facades\Setting;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        Setting::delete([
            'social_login_zalo_enable',
            'social_login_zalo_app_id',
            'social_login_zalo_app_secret',
        ]);
    }
}
