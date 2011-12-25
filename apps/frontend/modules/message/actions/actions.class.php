<?php

/**
 * message actions.
 *
 * @package    OpenMicNight
 * @subpackage message
 * @author     Tom Doggett
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class messageActions extends sfActions
{

    public function preExecute()
    {
        parent::preExecute();
        $request = $this->getRequest();
        if ($request->hasAttribute('api_log')) {
            $dispatcher = sfApplicationConfiguration::getActive()
                    ->getEventDispatcher();
            $string = $request->getAttribute('api_log');
            $dispatcher->notify(new sfEvent($this, 'application.log', array(
                        'priority' => sfLogger::WARNING,
                        $string
                    )));
            $request->getAttributeHolder()->remove('api_log');
        }
    }

    public function executeIndex(sfWebRequest $request)
    {
        $auth_key = $this->getUser()->getApiAuthKey();
        $sent_data = Api::getInstance()->setUser($auth_key)->get('message?sender_id=' . $this->getUser()->getApiUserId(),
                                                                 true);
        $this->sent_messages = ApiDoctrine::createQuickObjectArray($sent_data['body']);
        $received_data = Api::getInstance()->setUser($auth_key)->get('message?recipient_id=' . $this->getUser()->getApiUserId(),
                                                                     true);
        $this->received_messages = ApiDoctrine::createQuickObjectArray($received_data['body']);

        // Grab users
        $users = array();
        foreach ($this->sent_messages as $message) {
            if (!in_array($message->getRecipientId(), $users)) {
                $users[] = $message->getRecipientId();
            }
        }
        foreach ($this->received_messages as $message) {
            if (!in_array($message->getSenderId(), $users)) {
                $users[] = $message->getSenderId();
            }
        }
        $user_data = Api::getInstance()->setUser($auth_key)->get('user?id=' . implode(',',
                                                                                      $users),
                                                                                      true);
        $users = ApiDoctrine::createQuickObjectArray($user_data['body']);
        $this->users = array();
        foreach ($users as $user) {
            $this->users[$user->getIncremented()] = $user;
        }
    }

    public function executeCreate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isMethod(sfRequest::POST));

        $this->form = new MessageForm();

        $this->processForm($request, $this->form);

        $this->setTemplate('send');
    }

    public function executeEdit(sfWebRequest $request)
    {
        $auth_key = $this->getUser()->getApiAuthKey();
        $message_data = Api::getInstance()->setUser($auth_key)->get('message/' . $request->getParameter('id'),
                                                                                                        true);
        $message = ApiDoctrine::createObject('Message', $message_data['body']);
        $this->forward404Unless($message && $message->getId() && $message->getSenderId() == $this->getUser()->getApiUserId());

        $this->form = new MessageForm($message);
        unset($this->form['recipient_id']);
    }

    public function executeUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isMethod(sfRequest::POST) || $request->isMethod(sfRequest::PUT));

        $auth_key = $this->getUser()->getApiAuthKey();
        $message_data = Api::getInstance()->setUser($auth_key)->get('message/' . $request->getParameter('id'),
                                                                                                        true);
        $message = ApiDoctrine::createObject('Message', $message_data['body']);
        $this->forward404Unless($message && $message->getId());

        $this->form = new MessageForm($message);
        //unset($this->form['recipient_id']);

        $this->processForm($request, $this->form);

        $this->setTemplate('edit');
    }

    public function executeDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $auth_key = $this->getUser()->getApiAuthKey();
        $message_data = Api::getInstance()->setUser($auth_key)->get('message/' . $request->getParameter('id'),
                                                                                                        true);
        $message = ApiDoctrine::createObject('Message', $message_data['body']);
        $this->forward404Unless($message && $message->getId());

        //$message->delete();
        $result = Api::getInstance()->setUser($auth_key)->delete('message/' . $message->getId(),
                                                                 true);
        $success = $this->checkHttpCode($result, 'delete',
                                        'message/' . $message->getId());
        if ($success)
            $this->getUser()->setFlash('notice',
                                       'Message was deleted successfuly.');

        $this->redirect('message/index');
    }

    public function executeSend(sfWebRequest $request)
    {
        $this->forward404Unless($request->getParameter('id'));
        $auth_key = $this->getUser()->getApiAuthKey();
        $message = new Message();
        $user_data = Api::getInstance()->setUser($auth_key)->get('user/' . $request->getParameter('id'),
                                                                                                        true);
        $this->recipient = ApiDoctrine::createQuickObject( $user_data['body']);
        $message->setRecipientId($request->getParameter('id'));
        if ($request->getParameter('previous', false)) {
            $message->setPreviousMessageId($request->getParameter('previous'));
        }
        $this->form = new MessageForm($message);
    }

    protected function processForm(sfWebRequest $request, sfForm $form)
    {
        $form->bind($request->getParameter($form->getName()),
                                           $request->getFiles($form->getName()));
        if ($form->getValue('recipient_id') == $this->getUser()->getApiUserId())
        {
            $this->getUser()->setFlash('error', 'You cannot sent messages to yourself.');
            $this->redirect('message');
        }
        if ($form->isValid() && $this->getUser()->getApiUserId()) {
            $auth_key = $this->getUser()->getApiAuthKey();
            if ($form->getValue('id')) {
                // Update existing item.
                $values = $form->getObject()->getModified();
                $message = $form->getObject();
                unset($values['id']);
                $id = $form->getValue('id');
                $result = Api::getInstance()->setUser($auth_key)->put('message/' . $id,
                                                                      $values);
                $success = $this->checkHttpCode($result, 'put',
                                                'message/' . $id,
                                                json_encode($values));
                if ($success)
                    $this->getUser()->setFlash('notice',
                                               'Message was edited successfully.');
                $test_message = ApiDoctrine::createObject('Message',
                                                          $result['body']);
                $message = $test_message ? $test_message : $message;
            } else {
                // Create new item
                $values = $form->getValues();
                $message = $form->getObject();
                foreach ($values as $key => $value) {
                    if (is_null($value))
                        unset($values[$key]);
                }
                if (!array_key_exists('sender_id', $values)) {
                    $values['sender_id'] = $this->getUser()->getApiUserId();
                }
                $result = Api::getInstance()->setUser($auth_key)->post('message',
                                                                       $values);
                $success = $this->checkHttpCode($result, 'post', 'message',
                                                json_encode($values));
                if ($success)
                    $this->getUser()->setFlash('notice',
                                               'Message was sent successfully.');
            }
            if (!$this->getUser()->getApiUserId())
                $this->getUser()->setFlash('error', 'You are not logged in!');

            $this->redirect('message');
        }
    }

    protected function checkHttpCode($result, $getpost = null, $location = null,
                                     $request = null)
    {
        $http_code = $result['headers']['http_code'];
        if ($http_code != 200) {
            $message = array_key_exists('message', $result['body']) ? $result['body']['message']
                        : 'An error occured.';
            $message = array_key_exists(0, $result['body']) && array_key_exists('message',
                                                                                $result['body'][0])
                        ? $result['body'][0]['message'] : $message;
            $this->getUser()->setFlash('error', "($http_code) $message");

            $data = array(
                'getpost' => strtoupper($getpost),
                'location' => $location,
                'url' => $result['headers']['url'],
                'http_code' => $http_code,
                'response' => json_encode($result['body']),
            );
            if ($request)
                $data['request'] = $request;
            $this->getUser()->setAttribute('api_log', Api::buildLogString($data));
            return false;
        } else {
            return true;
        }
    }
}
