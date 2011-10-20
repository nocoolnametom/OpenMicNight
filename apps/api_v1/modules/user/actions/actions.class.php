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

    public function getRequestValidators()
    {
        $validators = array();
        $validators['email_address'] = new sfValidatorEmail(array(
                    'max_length' => 255,
                    'required' => true,
                ));
        $validators['password'] = new sfValidatorString(array(
                    'max_length' => 255,
                    'required' => true,
                ));
        $validators['expires_in'] = new sfValidatorInteger(array(
                    'required' => false,
                    'min' => 1,
                ));
        return $validators;
    }

    public function getRequestPostValidators()
    {
        $validators = array();
        return $validators;
    }

    public function validateRequest($payload)
    {
        $params = $this->parsePayload($payload);

        $validators = $this->getRequestValidators();
        $this->validate($params, $validators);

        $postvalidators = $this->getRequestPostValidators();
        $this->postValidate($params, $postvalidators);
    }

    public function requestAuthKey($content)
    {
        $data = $this->parsePayload($content);
        $email_address = $data['email_address'];
        $password = $data['password'];
        $expires_in = (array_key_exists('expires_in', $data) ? $data['expires_in'] : null);
        return $this->getUser()
                        ->requestAuthKey($email_address, $password, $expires_in);
    }

    /**
     * Creates a sfGuardUser object
     * @param   sfWebRequest   $request a request object
     * @return  string
     */
    public function executeRequest_key(sfWebRequest $request)
    {
        //$this->forward404Unless($request->isMethod(sfRequest::POST));
        $content = $request->getContent();

        // Restores backward compatibility. Content can be the HTTP request full body, or a form encoded "content" var.
        if (strpos($content, 'content=') === 0) {
            $content = $request->getParameter('content');
        }
        if ($content === false) {
            $content = $request->getPostParameter('content'); // Last chance to get the content!
        }

        $request->setRequestFormat('html');
        $this->setTemplate('index');

        try {
            $parameters = $request->getParameterHolder()->getAll();
            $params = $this->getApiAuthFieldValues($parameters, $content);
            $this->validateApiAuth($parameters, $content);
            $this->validateRequest($content);
            $auth_key = $this->requestAuthKey($content);
            if (!$auth_key) {
                throw new Exception("Invalid credentials given.");
            }
        } catch (Exception $e) {
            $this->getResponse()->setStatusCode(406);
            $serializer = $this->getSerializer();
            $this->getResponse()->setContentType($serializer->getContentType());
            $error = $e->getMessage();

            // event filter to enable customisation of the error message.
            $result = $this->dispatcher->filter(
                            new sfEvent($this, 'sfDoctrineRestGenerator.filter_error_output'), $error
                    )->getReturnValue();

            if ($error === $result) {
                $error = array(array('message' => $error));
                $this->output = $serializer->serialize($error, 'error');
            } else {
                $this->output = $serializer->serialize($result);
            }

            $this->setTemplate('index');
            return sfView::SUCCESS;
        }

        $serializer = $this->getSerializer();
        $this->getResponse()->setContentType($serializer->getContentType());
        $this->output = $serializer->serialize(array('auth_key' => $auth_key), $this->model, false);
    }

}
