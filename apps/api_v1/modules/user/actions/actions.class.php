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
            throw new sfException('API authorization failed.', 401);
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
        unset($validators["is_validated"], $validators["salt"], $validators["is_active"], $validators["reddit_validation_key"], $validators["algorithm"], $validators["is_super_admin"], $validators["last_login"], $validators["created_at"], $validators["updated_at"]);
        return $validators;
    }

    public function validateCreate($payload, sfWebRequest $request = null)
    {
        parent::validateCreate($payload, $request);
        if ($this->getUser()->getAttribute("api_key") != sfConfig::get("app_web_app_api_key"))
            throw new sfException("Only the web application is allowed to "
                    . "create new users.", 403);
    }

    public function validateDelete($payload, sfWebRequest $request = null)
    {
        parent::validateDelete($payload, $request);
        if ($this->getUser()->getAttribute("api_key") != sfConfig::get("app_web_app_api_key")
                || (!$this->getUser()->isSuperAdmin()))
            throw new sfException("Only the web application is allowed to "
                    . "delete users.", 403);
    }

    public function validateUpdate($payload, sfWebRequest $request = null)
    {
        parent::validateUpdate($payload, $request);
        $params = $this->parsePayload($payload);
        $primaryKey = $request->getParameter('id');
        if (!$this->getUser()->getGuardUser() ||
                (($this->getUser()->getGuardUser()->getIncremented() != $primaryKey)
                && (!$this->getUser()->isSuperAdmin())))
            throw new sfException("You can only alter information for your own "
                    . "user record.", 403);
    }

    public function getTokenValidators()
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

    public function getTokenPostValidators()
    {
        $validators = array();
        return $validators;
    }

    public function getTokenUserIdValidators()
    {
        $validators = array();
        return $validators;
    }

    public function getTokenUserIdPostValidators()
    {
        $validators = array();
        return $validators;
    }

    public function validateToken($payload)
    {
        $params = $this->parsePayload($payload);

        $validators = $this->getTokenValidators();
        $this->validate($params, $validators);

        $postvalidators = $this->getTokenPostValidators();
        $this->postValidate($params, $postvalidators);
    }

    public function validateTokenUserId($params, sfWebRequest $request = null)
    {
        $validators = $this->getTokenUserIdValidators();
        $this->validate($params, $validators);

        $postvalidators = $this->getTokenUserIdPostValidators();
        $this->postValidate($params, $postvalidators);
    }

    public function requestToken($content)
    {
        $data = $this->parsePayload($content);
        $email_address = str_replace(' ', '', $data['email_address']);
        $password = $data['password'];
        $expires_in = (array_key_exists('expires_in', $data) ? $data['expires_in'] : null);
        return $this->getUser()
                        ->requestAuthKey($email_address, $password, $expires_in);
    }

    public function requestTokenUserId()
    {
        return ($this->getUser()->getGuardUser() ? 
                $this->getUser()->getGuardUser()->getIncremented() : null);
    }

    /**
     * Creates a token referring to an sfGuardUser object
     * @param   sfWebRequest   $request a request object
     * @return  string
     */
    public function executeToken(sfWebRequest $request)
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
            $this->validateToken($content);
            $auth_key = $this->requestToken($content);
            $data = $this->parsePayload($content);
            $email_address = $data['email_address'];
        } catch (Exception $e) {
            $this->getResponse()->setStatusCode($e->getCode() ? $e->getCode() : 406);
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
        $user = sfGuardUserTable::getInstance()->findOneBy('email_address', $email_address);
        $user_id = ($user && $auth_key ? $user->getIncremented() : null);
        $this->output = $serializer->serialize(array(
            'auth_key' => $auth_key,
            'user_id' => $user_id,
                ), $this->model, false);
    }

    /**
     * Creates a token referring to an sfGuardUser object
     * @param   sfWebRequest   $request a request object
     * @return  string
     */
    public function executeToken_user_id(sfWebRequest $request)
    {
        $this->forward404Unless($request->isMethod(sfRequest::GET));
        $params = $request->getParameterHolder()->getAll();

        // notify an event before the action's body starts
        $this->dispatcher->notify(new sfEvent($this, 'sfDoctrineRestGenerator.get.pre', array('params' => $params)));

        $request->setRequestFormat('html');
        $this->setTemplate('index');
        $params = $this->cleanupParameters($params);

        try {
            $format = $this->getFormat();
            $this->validateApiAuth($request->getParameterHolder()->getAll());
            $this->validateShow($params, $request);
        } catch (Exception $e) {
            $this->getResponse()->setStatusCode($e->getCode() ? $e->getCode() : 406);
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

            return sfView::SUCCESS;
        }

        $serializer = $this->getSerializer();
        $this->getResponse()->setContentType($serializer->getContentType());
        $user_id = $this->requestTokenUserId();
        $this->output = $serializer->serialize(array(
            'user_id' => $user_id,
                ), $this->model, false);
    }
    
    /**
     * Creates a token referring to an sfGuardUser object
     * @param   sfWebRequest   $request a request object
     * @return  string
     */
    public function executeTime(sfWebRequest $request)
    {
        $this->forward404Unless($request->isMethod(sfRequest::GET));
        $params = $request->getParameterHolder()->getAll();

        // notify an event before the action's body starts
        $this->dispatcher->notify(new sfEvent($this, 'sfDoctrineRestGenerator.get.pre', array('params' => $params)));

        $request->setRequestFormat('html');
        $this->setTemplate('index');
        $params = $this->cleanupParameters($params);

        try {
            $format = $this->getFormat();
        } catch (Exception $e) {
            $this->getResponse()->setStatusCode($e->getCode() ? $e->getCode() : 406);
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

            return sfView::SUCCESS;
        }

        $serializer = $this->getSerializer();
        $this->getResponse()->setContentType($serializer->getContentType());
        $user_id = $this->requestTokenUserId();
        $this->output = $serializer->serialize(array(
            'time' => time(),
                ), $this->model, false);
    }

}
