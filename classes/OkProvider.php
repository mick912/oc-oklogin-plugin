<?php

namespace Mirjan\Oklogin\Classes;

use Hybridauth\Exception\UnexpectedApiResponseException;
use Hybridauth\Data;
use Hybridauth\User;

class OkProvider extends \Hybridauth\Provider\Odnoklassniki
{
    public function getUserProfile()
    {
        $fields = array(
            'uid', 'locale', 'first_name', 'last_name', 'name', 'gender', 'age', 'birthday',
            'has_email', 'current_status', 'current_status_id', 'current_status_date','online',
            'photo_id', 'pic_1', 'pic_2', 'pic1024x768', 'location', 'email'
        );

        $sig = md5(
            'application_key=' . $this->config->get('keys')['public'] .
            'client_id=' . $this->config->get('keys')['key'] .
            'fields=' . implode(',', $fields) .
            'method=users.getCurrentUser' .
            md5($this->getStoredData('access_token') . $this->config->get('keys')['secret'])
        );

        $parameters = [
            'access_token'    => $this->getStoredData('access_token'),
            'application_key' => $this->config->get('keys')['public'],
            'client_id' => $this->config->get('keys')['key'],
            'method'          => 'users.getCurrentUser',
            'fields'          => implode(',', $fields),
            'sig'             => $sig,
        ];

        $response = $this->apiRequest('fb.do', 'GET', $parameters);
        $data = new Data\Collection($response);

        if (! $data->exists('uid')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }

        $userProfile = new User\Profile();

        $email = $data->get('email');
        if (!$email) {
            $uid = $data->get('uid');
            $email = $uid . '@' . $uid .'.com';
        }

        $userProfile->identifier  = $data->get('uid');
        $userProfile->email       = $email;
        $userProfile->firstName   = $data->get('first_name');
        $userProfile->lastName    = $data->get('last_name');
        $userProfile->displayName = $data->get('name');
        $userProfile->photoURL    = $data->get('pic1024x768');
        $userProfile->profileURL  = 'http://ok.ru/profile/' . $data->get('uid');

        return $userProfile;
    }
}
