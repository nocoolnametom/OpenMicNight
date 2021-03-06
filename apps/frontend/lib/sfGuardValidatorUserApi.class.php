<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of sfGuardValidatorUserApi
 *
 * @author doggetto
 */
class sfGuardValidatorUserApi extends sfGuardValidatorUser
{

    public function configure($options = array(), $messages = array())
    {
        parent::configure($options, $messages);
        $this->addOption('remember_field', 'remember');
    }

    protected function doClean($values)
    {
        $email = isset($values[$this->getOption('username_field')]) ? $values[$this->getOption('username_field')]
                    : '';
        $password = isset($values[$this->getOption('password_field')]) ? $values[$this->getOption('password_field')]
                    : '';
        $remember = isset($values[$this->getOption('remember_field')]) ? $values[$this->getOption('remember_field')]
                    : '';

        $allowEmail = sfConfig::get('app_sf_guard_plugin_allow_login_with_email',
                                    true);
        $onlyEmail = sfConfig::get('app_sf_guard_plugin_only_login_with_email',
                                   false);
        $expires_in = $remember ?
                sfConfig::get('app_web_app_api_auth_key_remember_me_expiration',
                              1209600) :
                sfConfig::get('app_web_app_api_auth_key_login_expiration', 7200);

        $method = $onlyEmail ? 'retrieveByEmailAddress' : ($allowEmail ? 'retrieveByUsernameOrEmailAddress'
                            : 'retrieveByUsername');

        // don't allow to sign in with an empty username
        if ($email) {
            $package = array(
                'email_address' => preg_replace('/\s+/', '', $email), //str_replace(" ", '', $email),
                'password' => $password,
                'expires_in' => $expires_in,
            );
            $response = Api::getInstance()->post('user/token', $package, false);
            if (array_key_exists('auth_key', $response['body'])
                    && $response['body']['auth_key']) {
                $user = sfGuardUserTable::getInstance()->find($response['body']['user_id']);
                if ($user->getIsActive()) {
                    return array_merge($values,
                                       array(
                                'user' => $user,
                                'remember' => $response['body']['auth_key'],
                            ));
                }
            }
            if (array_key_exists('message', $response['body']))
            {
                throw new sfValidatorError($this, $response['body']['message']);
            }
        }

        if ($this->getOption('throw_global_error')) {
            throw new sfValidatorError($this, 'invalid');
        }

        throw new sfValidatorErrorSchema($this, array($this->getOption('username_field') => new sfValidatorError($this, 'invalid')));
    }
}