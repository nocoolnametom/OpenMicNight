<?php

class myUser extends sfGuardSecurityUser
{

    protected $api_key;
    protected $auth_key;
    protected $api_authorized;

    /**
     * Initializes the sfGuardSecurityUser object.
     *
     * @param sfEventDispatcher $dispatcher The event dispatcher object
     * @param sfStorage $storage The session storage object
     * @param array $options An array of options
     */
    public function initialize(sfEventDispatcher $dispatcher,
                               sfStorage $storage, $options = array())
    {
        parent::initialize($dispatcher, $storage, $options);
        $this->auth_key = false;
        $this->api_key = false;
        $this->api_authorized = false;
    }

    public function apiIsAuthorized(sfWebRequest $request)
    {
        if ($this->api_authorized)
            return $this->api_authorized;
        if ($request->getParameter('api_key', $this->api_key)
                && $request->getParameter('time', false)
                && $request->getParameter('signature', false)) {
            $this->api_key = $request->getParameter('api_key');
            $time = $request->getParameter('time');
            $signature = $request->getParameter('signature');
            $api = ApiKeyTable::getInstance()->findOneByApiKey($api_key);
            if ($api instanceof ApiKey && $api->getIsActive()) {
                $shared_secret = $api->getSharedSecret();
                $constructed_signature = sha1($shared_secret . $time);
                if ($constructed_signature == $signature) {
                    $this->api_authorized = true;
                    return $this->api_authorized;
                }
            }
        }
        return false;
    }

    public function getGuardUser(sfWebRequest $request)
    {
        if ($this->user) {
            return $this->user;
        }

        if (self::apiIsAuthorized($request)
                && $request->getParameter('auth_key', $this->auth_key)) {
            $api = ApiKeyTable::getInstance()
                    ->findOneByApiKey($request->getParameter('api_key',
                                                             $this->api_key));
            $auth_key = $request->getParameter('auth_key');
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
        if ($this->auth_key) {
            return $this->auth_key;
        }

        if (!$this->api_authorized) {
            return $this->auth_key;
        }

        $api = ApiKeyTable::getInstance()
                ->findOneByApiKey($this->api_key);

        $this->auth_key = $api->requestAuthKey($email_address, $password,
                                               $expires_in);
        return $this->auth_key;
    }

}
