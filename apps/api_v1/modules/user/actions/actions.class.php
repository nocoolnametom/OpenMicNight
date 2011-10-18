<?php

/**
 * user actions.
 *
 * @package    OpenMicNight
 * @subpackage user
 * @author     Tom Doggett
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z xavier $
 * @see        class::autouserActions
 */
class userActions extends autouserActions
{

    public function checkApiAuth($parameters, $content = null)
    {
        parent::checkApiAuth($parameters, $content);
        $this->getUser()->setParams($parameters);
        if (!$this->getUser()->apiIsAuthorized())
            throw new sfException('API authorization failed.');
        return true;
    }

    public function parsePayload($payload, $force = false, $remove_api = true)
    {
        //return parent::parsePayload($payload, $force, $remove_api);
        if ($remove_api) {
            $api_auth_params = $this->getApiAuthFields();
            $this->_payload_array = parent::parsePayload($payload, $force,
                                                         $remove_api);
            $this->_payload_array = array_diff_key($this->_payload_array,
                                                   $api_auth_params);
        }

        return $this->_payload_array;
    }

    public function getUpdateValidators()
    {
        $validators = parent::getUpdateValidators();
        $validators['email_address'] = new sfValidatorEmail(array(
                    'max_length' => 255,
                    'required' => false,
                ));
        $validators['username'] = new sfValidatorString(array(
                    'max_length' => 255,
                    'required' => false,
                ));
        return $validators;
    }

}
