<?php

namespace FriendsOfBotble\SocialLogin\Zalo;

use Laravel\Socialite\Two\User;

class ZaloUser extends User
{
    /**
     * @var array{access_token: string, refresh_token: string, expires_in: int}
     */
    public array $accessTokenResponseBody;

    public function setAccessTokenResponseBody(array $accessTokenResponseBody): static
    {
        $this->accessTokenResponseBody = $accessTokenResponseBody;

        return $this;
    }
}
