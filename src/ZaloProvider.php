<?php

namespace FriendsOfBotble\SocialLogin\Zalo;


use GuzzleHttp\RequestOptions;
use Illuminate\Support\Arr;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\InvalidStateException;

class ZaloProvider extends AbstractProvider
{
    protected $scopes = [''];

    /**
     * @var \FriendsOfBotble\SocialLogin\Zalo\ZaloUser|null
     */
    protected $user;

    protected array $credentialsResponseBody;

    /**
     * @return \FriendsOfBotble\SocialLogin\Zalo\ZaloUser
     *
     * @throws \Laravel\Socialite\Two\InvalidStateException
     */
    public function user()
    {
        if ($this->user) {
            return $this->user;
        }

        if ($this->hasInvalidState()) {
            throw new InvalidStateException();
        }

        $response = $this->getAccessTokenResponse($this->getCode());
        $this->credentialsResponseBody = $response;

        $this->user = $this->mapUserToObject($this->getUserByToken(
            $token = Arr::get($response, 'access_token')
        ));

        if ($this->user instanceof ZaloUser) {
            $this->user->setAccessTokenResponseBody($this->credentialsResponseBody);
        }

        return $this->user
            ->setToken($token)
            ->setRefreshToken(Arr::get($response, 'refresh_token'))
            ->setExpiresIn(Arr::get($response, 'expires_in'));
    }

    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://oauth.zaloapp.com/v4/permission', $state);
    }

    protected function getCodeFields($state = null): array
    {
        $fields = parent::getCodeFields($state);

        $fields['app_id'] = $this->clientId;

        return $fields;
    }

    public function getAccessTokenResponse($code)
    {
        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            RequestOptions::HEADERS => ['secret_key' => $this->clientSecret],
            RequestOptions::FORM_PARAMS => $this->getTokenFields($code),
        ]);

        return json_decode((string) $response->getBody(), true);
    }

    protected function getTokenUrl(): string
    {
        return 'https://oauth.zaloapp.com/v4/access_token';
    }


    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get('https://graph.zalo.me/v2.0/me', [
            RequestOptions::HEADERS => ['access_token' => $token],
            RequestOptions::QUERY => ['fields' => 'id,error,message,name,picture'],
        ]);

        return json_decode((string) $response->getBody(), true);
    }

    protected function mapUserToObject(array $user)
    {
        return (new ZaloUser())->setRaw($user)->map([
            'id' => $id = $user['id'],
            'nickname' => null,
            'name' => $user['name'] ?? null,
            'email' => $user['email'] ?? $id . '+zalo@' . $this->request->host(),
            'avatar' => preg_replace('/^http:/i', 'https:', $user['picture']['data']['url']),
        ]);
    }

    protected function getTokenFields($code)
    {
        return array_merge(parent::getTokenFields($code), [
            'app_id' => $this->clientId,
        ]);
    }
}
