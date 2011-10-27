<?php

class myUser extends sfGuardSecurityUser
{

    public function requestToken($email_address, $password, $expires_in = null)
    {
        if (is_null($expires_in)) {
            $expires_in = sfConfig::get('app_web_app_api_auth_key_login_expiration', 7200);
        }
        $response = Api::getInstance()->requestAuthToken($email_address, $password, $expires_in);
        if (array_key_exists('auth_key', $response['body'])
                && $response['body']['auth_key']) {
            $this->setAttribute('api_auth_key', $response['body']['auth_key'], 'sfGuardSecurityUser');
            $this->setAttribute('user_id', $response['body']['user_id'], 'sfGuardSecurityUser');
        }
    }

    public function getApiAuthKey()
    {
        return $this->getAttribute('api_auth_key', null, 'sfGuardSecurityUser');
    }

}
