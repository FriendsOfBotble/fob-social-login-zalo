<?php

namespace FriendsOfBotble\SocialLogin\Zalo\Providers;

use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Base\Supports\ServiceProvider;
use Botble\Theme\Facades\Theme;
use FriendsOfBotble\SocialLogin\Zalo\ZaloProvider;
use Illuminate\Contracts\Container\Container;
use Laravel\Socialite\Facades\Socialite;

class SocialLoginZaloServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function boot(): void
    {
        if (! is_plugin_active('social-login')) {
            return;
        }

        $this
            ->setNamespace('plugins/social-login-zalo')
            ->loadAndPublishTranslations()
            ->publishAssets();

        Socialite::extend('zalo', function (Container $container) {
            $config = $container['config']->get('services.zalo');

            return Socialite::buildProvider(ZaloProvider::class, $config);
        });

        add_filter('social_login_providers', function (array $providers) {
            return [
                ...$providers,
                'zalo' => [
                    'data' => [
                        'app_id',
                        'app_secret',
                    ],
                    'disable' => [
                        'app_secret',
                    ],
                    'label' => [
                        'enable' => trans('plugins/social-login-zalo::social-login-zalo.settings.zalo.enable'),
                        'app_id' => trans('plugins/social-login-zalo::social-login-zalo.settings.zalo.app_id'),
                        'app_secret' => trans('plugins/social-login-zalo::social-login-zalo.settings.zalo.app_secret'),
                        'helper' => trans('plugins/social-login-zalo::social-login-zalo.settings.zalo.helper', [
                            'callback' => '<code class=\'text-danger\'>' . route('auth.social.callback', 'zalo') . '</code>',
                        ]),
                    ],
                ],
            ];
        }, 120);

        add_action('social_login_assets_register', function () {
            Theme::asset()
                ->usePath(false)
                ->add(
                    'social-login-zalo-css',
                    asset('vendor/core/plugins/social-login-zalo/css/social-login-zalo.css'),
                    [],
                    [],
                    '1.0.0'
                );
        }, 120);
    }
}
