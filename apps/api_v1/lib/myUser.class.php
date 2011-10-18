<?php

class myUser extends sfGuardSecurityUser
{

    public function setParams($params)
    {
        if (array_key_exists('api_key', $params))
            $this->setAttribute('api_key', $params['api_key']);
        if (array_key_exists('auth_key', $params))
            $this->setAttribute('auth_key', $params['auth_key']);
        if (array_key_exists('time', $params))
            $this->setAttribute('time', $params['time']);
        if (array_key_exists('signature', $params))
            $this->setAttribute('signature', $params['signature']);
        return $this;
    }

    public function apiIsAuthorized()
    {
        //if ($this->getAttribute('api_authorized', false))
        //    return $this->getAttribute('api_authorized');

        /*if ($request->getParameter('api_key', $this->getAttribute('api_key'))
                && $request->getParameter('time', $this->getAttribute('time'))
                && $request->getParameter('signature',
                                          $this->getAttribute('signature'))) {
            $this->setAttribute('api_key',
                                $request->getParameter('api_key',
                                                       $this->getAttribute('api_key')));
            $time = $request->getParameter('time', $this->getAttribute('time'));
            $signature = $request->getParameter('signature',
                                                $this->getAttribute('signature'));*/
            $api = ApiKeyTable::getInstance()->findOneByApiKey($this->getAttribute('api_key'));
            if ($api instanceof ApiKey && $api->getIsActive()) {
                $shared_secret = $api->getSharedSecret();
                $constructed_signature = sha1($shared_secret . $this->getAttribute('time'));
                if ($constructed_signature == $this->getAttribute('signature')) {
                    $this->setAttribute('api_authorized', true);
                    return $this->getAttribute('api_authorized');
                }
            }
        //}
        return false;
    }

    public function getGuardUser()
    {
        if ($this->user) {
            return $this->user;
        }

        if (self::apiIsAuthorized()
                && $this->getAttribute('auth_key')) {
            $api = ApiKeyTable::getInstance()
                    ->findOneByApiKey($this->getAttribute('api_key'));
            $auth_key = $this->getAttribute('auth_key');
            $user_auth = sfGuardUserAuthKeyTable::getInstance()
                    ->getMostRecentValidByApiKeyIdAndAuthKey(
                    $api->getIncremented(), $auth_key);
            if ($user_auth instanceof sfGuardUserAuthKey) {
                $this->user = $user_auth->getSfGuardUser();
                $this->setAuthenticated(true);
                $this->clearCredentials();
                $this->addCredentials($this->user->getAllPermissionNames());
                return $this->user;
            }
        }
        return null;
    }

    public function requestAuthKey($email_address, $password, $expires_in = 7200)
    {
        if ($this->getAttribute('auth_key')) {
            return $this->getAttribute('auth_key');
        }

        if (!self::apiIsAuthorized()) {
            return $this->getAttribute('auth_key');
        }

        $api = ApiKeyTable::getInstance()
                ->findOneByApiKey($this->getAttribute('api_key'));

        $this->setAttribute('auth_key', $api->requestAuthKey($email_address, $password,
                                               $expires_in));
        return $this->getAttribute('auth_key');
    }

}
