<?php

class myUser extends sfGuardSecurityUser
{

    private $_user_id;

    /**
     * Initializes the myUser object.
     *
     * @param sfEventDispatcher $dispatcher The event dispatcher object
     * @param sfStorage $storage The session storage object
     * @param array $options An array of options
     */
    public function initialize(sfEventDispatcher $dispatcher, sfStorage $storage, $options = array())
    {
        parent::initialize($dispatcher, $storage, $options);

        if (!$this->isAuthenticated()) {
            $this->_user_id = null;
        }
    }

    public function requestToken($email_address, $password, $expires_in = null)
    {
        if (is_null($expires_in)) {
            $expires_in = sfConfig::get('app_web_app_api_auth_key_login_expiration', 7200);
        }
        $response = Api::getInstance()->requestAuthToken($email_address, $password, $expires_in);
        if (array_key_exists('auth_key', $response['body'])
                && $response['body']['auth_key']) {
            $this->setApiAuthkey($response['body']['auth_key']);
        }
    }
    
    public function formatMarkdown($input)
    {
        ProjectConfiguration::registerMarkdown();
        return Markdown($input);
    }

    public function setApiAuthkey($auth_key)
    {
        $this->setAttribute('api_auth_key', $auth_key, 'sfGuardSecurityUser');
    }

    public function setApiUserId($user_id)
    {
        $this->_user_id = $user_id;
    }

    public function getApiAuthKey()
    {
        return $this->getAttribute('api_auth_key', null, 'sfGuardSecurityUser');
    }

    public function getApiUserId()
    {
        if (!$this->_user_id) {
            $user_id = Api::getInstance()->setUser($this->getApiAuthKey())->get('user/token_user_id');
            if (array_key_exists('body', $user_id)
                    && array_key_exists('user_id', $user_id['body'])
                    && $user_id['body']['user_id']) {
                $this->setApiUserId($user_id['body']['user_id']);
            } else {
                $this->setAuthenticated(false);
            }
        }
        return $this->_user_id;
    }

    /**
     * Returns the related sfGuardUser.
     *
     * @return sfGuardUser
     */
    public function getGuardUser()
    {
        if (!$this->user && $id = $this->getApiUserId()) {
            //$this->user = Doctrine_Core::getTable('sfGuardUser')->find($id);
            $data = Api::getInstance()->get('user/' . $this->_user_id);
            $this->user = ApiDoctrine::createObject('sfGuardUser', $data['body']);

            if (!$this->user) {
                // the user does not exist anymore in the database
                $this->signOut();

                throw new sfException('The user does not exist anymore.');
            }
        }

        return $this->user;
    }

    /**
     * Signs in the user on the application.
     *
     * @param sfGuardUser $user The sfGuardUser id
     * @param boolean $remember Whether or not to remember the user
     * @param Doctrine_Connection $con A Doctrine_Connection object
     */
    public function signIn($user, $auth_key = null, $con = null)
    {
        // signin
        $this->setApiUserid($user->getId());
        $this->setAuthenticated(true);
        $this->clearCredentials();
        $this->addCredentials($user->getAllPermissionNames());

        // save last login
        $user->setLastLogin(date('Y-m-d H:i:s'));
        $user->save($con);

        // Set login messages
        $message = array();
        foreach ($user->getUndisplayedLoginMessages() as $message) {
            $messages[] = $message->getMessage();
            $message->setDisplayed(true);
            $message->save();
        }
        if (count($message) > 0)
            $this->setFlash('login', $messages);

        // remember?
        if ($auth_key) {
            $this->setApiAuthkey($auth_key);
            $api_key = sfConfig::get('app_web_app_api_key');
            $api = ApiKeyTable::getInstance()->findOneBy('api_key', $api_key);
            $auth_key = sfGuardUserAuthKeyTable::getInstance()->getMostRecentValidByApiKeyIdAndAuthKey($api->getIncremented(), $auth_key);
            $expires = strtotime($auth_key->getExpiresAt());

            // make key as a cookie
            $remember_cookie = sfConfig::get('app_sf_guard_plugin_remember_cookie_name', 'sfRemember');
            sfContext::getInstance()->getResponse()->setCookie($remember_cookie, $auth_key->getAuthKey(), $expires);
        }
    }

    /**
     * Signs out the user.
     *
     */
    public function signOut()
    {
        $auth_key = $this->getApiAuthKey();
        $this->getAttributeHolder()->removeNamespace('sfGuardSecurityUser');
        $this->user = null;
        $this->_user_id = null;
        $this->clearCredentials();
        $this->setAuthenticated(false);
        $api_key = sfConfig::get('app_web_app_api_key');
        $api = ApiKeyTable::getInstance()->findOneBy('api_key', $api_key);
        $auth_key = sfGuardUserAuthKeyTable::getInstance()->getMostRecentValidByApiKeyIdAndAuthKey($api->getIncremented(), $auth_key);
        if ($auth_key)
            $auth_key->delete();
        $expiration_age = sfConfig::get('app_sf_guard_plugin_remember_key_expiration_age', 15 * 24 * 3600);
        $remember_cookie = sfConfig::get('app_sf_guard_plugin_remember_cookie_name', 'sfRemember');
        sfContext::getInstance()->getResponse()->setCookie($remember_cookie, '', time() - $expiration_age);
    }

    public function sendMail($body_function, $additional_params = array())
    {
        $user = $this->getGuardUser();
        if (!$user)
            return;
        if (!$user->getReceiveNotificationOfEpisodeApprovalPending()
                && $body_function == "EpisodeApprovalPending")
            return;
        if (!$user->getReceiveNotificationOfNewlyOpenedEpisodes()
                && $body_function == "NewlyOpenedEpisode")
            return;
        if (!$user->getReceiveNotificationOfPrivateMessages()
                && $body_function == "NewPrivateMessage")
            return;

        $prefer_html = $user->getPreferHtml();
        $address = $user->getEmailAddress();
        $name = ($user->getPreferredName() ?
                        $user->getPreferredName() : $user->getFullName());
        $user_id = $this->getApiUserId();
        if (!array_key_exists('user_id', $additional_params)) {
            $additional_params['user_id'] = $user_id;
        }

        if (array_key_exists('language', $additional_params))
            $email = EmailTable::getInstance()->getFirstByEmailTypeAndLanguage($body_function, $additional_params['language']);
        else
            $email = EmailTable::getInstance()->getFirstByEmailTypeAndLanguage($body_function);
        if (!$email)
            throw new sfException("Cannot find email '$body_function' in language '$language'.");

        $subject = $email->generateSubject($additional_params);
        $body = $email->generateBodyText($additional_params, $prefer_html);

        $from = sfConfig::get('app_email_address', ProjectConfiguration::getApplicationEmailAddress());
        
        return AppMail::sendMail($address, $from, $subject, $body, $prefer_html ? $body : null);
    }

}
