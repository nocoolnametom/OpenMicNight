<?php

/**
 * message actions.
 *
 * @package    OpenMicNight
 * @subpackage message
 * @author     Tom Doggett
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z xavier $
 * @see        class::automessageActions
 */
class messageActions extends automessageActions
{

    public function checkApiAuth($parameters, $content = null)
    {
        parent::checkApiAuth($parameters, $content);
        $this->getUser()->setParams($parameters);
        if (!$this->getUser()->apiIsAuthorized())
            throw new sfException('API authorization failed.');
        return true;
    }

    public function getCreateValidators()
    {
        $validators = parent::getCreateValidators();
        $validators["recipient_id"] = new sfValidatorDoctrineChoice(array(
                    'model' => Doctrine_Core::getTable('Message')
                            ->getRelation('sfGuardUser')
                            ->getAlias(),
                    'required' => true,
                ));
        $validators["sender_id"] = new sfValidatorDoctrineChoice(array(
                    'model' => Doctrine_Core::getTable('Message')
                            ->getRelation('sfGuardUser')
                            ->getAlias(),
                    'required' => true,
                ));
        $validators["text"] = new sfValidatorString(array(
                    'max_length' => 4000,
                    'required' => true,
                ));
        return $validators;
    }

    public function getUpdateValidators()
    {
        $validators = parent::getUpdateValidators();
        unset($validators['recipient_id'], $validators["sender_id"]);
        return $validators;
    }

    public function validateShow($params, sfWebRequest $request = null)
    {

        $primaryKey = $request->getParameter('id');
        $message = MessageTable::getInstance()->find($primaryKey);

        if ($message
                && !($this->getUser()->isSuperAdmin()
                || (($this->getUser()->getGuardUser()->getIncremented() == $message->getSenderId())
                || ($this->getUser()->getGuardUser()->getIncremented() == $message->getSenderId()))
                )
        )
            throw new sfException("Your user does not have permissions to "
                    . "view this Message.", 403);
    }

    public function validateCreate($payload, sfWebRequest $request = null)
    {
        $params = $this->parsePayload($payload);

        if (!$this->getUser()->isSuperAdmin() || !array_key_exists('sender_id', $params))
            $params['sender_id'] = $this->getUser()->getGuardUser()->getIncremented();

        $validators = $this->getUpdateValidators();
        $this->validate($params, $validators);

        $postvalidators = $this->getUpdatePostValidators();
        $this->postValidate($params, $postvalidators);
    }

    public function validateDelete($payload, sfWebRequest $request = null)
    {
        parent::validateDelete($payload, $request);

        $primaryKey = $request->getParameter('id');
        $message = MessageTable::getInstance()->find($primaryKey);

        if ($message
                && !($this->getUser()->isSuperAdmin()
                || $this->getUser()->getGuardUser()->getIncremented() == $message->getSenderId()))
            throw new sfException("Your user does not have permissions to "
                    . "delete this Message.", 403);
    }

    public function validateUpdate($payload, sfWebRequest $request = null)
    {
        $params = $this->parsePayload($payload);

        $primaryKey = $request->getParameter('id');
        $message = MessageTable::getInstance()->find($primaryKey);

        if (!$this->getUser()->isSuperAdmin() && array_key_exists('sender_id', $params))
            $params['sender_id'] = $this->getUser()->getGuardUser()->getIncremented();

        if ($message
                && !($this->getUser()->isSuperAdmin()
                || $this->getUser()->getGuardUser()->getIncremented() == $message->getSenderId()))
            throw new sfException("Your user does not have permissions to "
                    . "alter this Message.", 403);

        $validators = $this->getUpdateValidators();
        $this->validate($params, $validators);

        $postvalidators = $this->getUpdatePostValidators();
        $this->postValidate($params, $postvalidators);
    }

    /**
     * Creates a Message object
     * @param   sfWebRequest   $request a request object
     * @return  string
     */
    public function executeCreate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isMethod(sfRequest::POST));
        $content = $request->getContent();

        // Restores backward compatibility. Content can be the HTTP request full body, or a form encoded "content" var.
        if (strpos($content, 'content=') === 0) {
            $content = $request->getParameter('content');
        }
        if ($content === false) {
            $content = $request->getPostParameter('content'); // Last chance to get the content!
        }

        $request->setRequestFormat('html');

        try {
            $parameters = $request->getParameterHolder()->getAll();
            $params = $this->getApiAuthFieldValues($parameters, $content);
            $this->validateApiAuth($parameters, $content);
            $this->validateCreate($content, $request);
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

        $object_params = $this->parsePayload($content);
        if (!$this->getUser()->isSuperAdmin() || !array_key_exists('sender_id', $object_params))
            $object_params['sender_id'] = $this->getUser()->getGuardUser()->getIncremented();

        $this->object = $this->createObject();
        $this->object->importFrom('array', $object_params);
        return $this->doSave($params);
    }
}
