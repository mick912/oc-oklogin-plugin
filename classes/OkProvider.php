<?php

namespace Mirjan\Oklogin\Classes;

use JhaoDa\SocialiteProviders\Odnoklassniki\Provider;
use Laravel\Socialite\Two\ProviderInterface;
use SocialiteProviders\Manager\OAuth2\User;

class OkProvider extends Provider
{
    protected $customConfig =  [];

    public function setCustomConfig($conf) {
        $this->customConfig = $conf;
        return $this;
    }

    protected function getUserByToken($token)
    {
        $params = [
            'format'          => 'json',
            'method'          => 'users.getCurrentUser',
            'application_key' => $this->customConfig['client_public'],
            'fields'          => 'uid,name,first_name,last_name,birthday,pic190x190,has_email,email'
        ];

        ksort($params, SORT_STRING);

        $_params = array_map(function($key, $value) {
            return $key . '=' . $value;
        }, array_keys($params), array_values($params));

        $params['sig'] = md5(implode('', $_params) . md5($token . $this->clientSecret));
        $params['access_token'] = $token;

        $response = $this->getHttpClient()->get(
            'https://api.ok.ru/fb.do?' . http_build_query($params)
        );

        return json_decode($response->getBody(), true);
    }


    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User())->setRaw($user)->map([
            'id'       => $user['uid'],
            'name'     => $user['name'],
            'nickname' => null,
            'email'    => array_get($user, 'email', $user['uid'] . '@' . $user['uid'] .'.com'),
            'avatar'   => array_get($user, 'pic190x190'),
            'avatar_original'   => array_get($user, 'pic190x190'),
        ]);
    }

}
