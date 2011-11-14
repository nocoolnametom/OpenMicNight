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

        /* if ($request->getParameter('api_key', $this->getAttribute('api_key'))
          && $request->getParameter('time', $this->getAttribute('time'))
          && $request->getParameter('signature',
          $this->getAttribute('signature'))) {
          $this->setAttribute('api_key',
          $request->getParameter('api_key',
          $this->getAttribute('api_key')));
          $time = $request->getParameter('time', $this->getAttribute('time'));
          $signature = $request->getParameter('signature',
          $this->getAttribute('signature')); */
        $time = $this->getAttribute('time', 0);
        // API authentication is only valid if the timestamp given is current within a half-hour window - The current time of the API server can be found by running a GET request against "usr/time" or "/time"
        if (!($time > time() - 900 && $time < time() + 900))
            return false;
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

    public function sendAuthRequestEmail(ApiKey $api)
    {
        $this->sendMail('ApiAuthRequest', array(
            'api_id' => $api->getIncremented(),
        ));
        return;
    }

    public function requestAuthKey($email_address, $password, $expires_in = null)
    {
        if (is_null($expires_in))
            $expires_in = sfConfig::get('app_web_app_api_auth_key_login_expiration');

        if (!self::apiIsAuthorized()) {
            return $this->getAttribute('auth_key');
        }

        $api = ApiKeyTable::getInstance()
                ->findOneByApiKey($this->getAttribute('api_key'));
        if ($api->getApiKey() != sfConfig::get('app_web_app_api_key'))
            $this->sendAuthRequestEmail($api);
        return $api->requestAuthKey($email_address, $password, $expires_in);
    }

    public function sendMail($body_function, $additional_params = array())
    {
        ProjectConfiguration::registerZend();
        $mail = new Zend_Mail();
        $mail->addHeader('X-MailGenerator', ProjectConfiguration::getApplicationName());

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
        $user_id = $user->getIncremented();

        $email_subject = new EmailSubject();
        $subject = call_user_func_array(array(
            $email_subject,
            $body_function
                ), array(
            $user_id,
            $additional_params,
                ));

        $email_body = new EmailBody();
        $body = call_user_func_array(array(
            $email_body,
            $body_function
                ), array(
            $user_id,
            $additional_params,
                ));

        if ($prefer_html) {
            $mail->setBodyHtml($body);
        }

        $body = preg_replace('/<br??>/', "\n", $body);
        $body = strip_tags($body);
        $mail->setBodyText($body);
        $mail->setFrom(sfConfig::get('app_email_address', 'donotreply@' . ProjectConfiguration::getApplicationName()), sfconfig::get('app_email_name', ProjectConfiguration::getApplicationName() . 'Team'));
        $mail->addTo($address, $name);
        $mail->setSubject($subject);
        if (sfConfig::get('sf_environment') == 'prod') {
            $mail->send();
        } else {
            throw new sfException('Mail sent: ' . $mail->getBodyText());
        }
    }

}
