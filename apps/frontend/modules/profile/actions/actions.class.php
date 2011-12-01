<?php

/**
 * profile actions.
 *
 * @package    OpenMicNight
 * @subpackage profile
 * @author     Tom Doggett
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class profileActions extends sfActions
{

    public function executeIndex(sfWebRequest $request)
    {
        $user_id = $this->getUser()->getApiUserId();
        $user_data = Api::getInstance()->get('user/' . $user_id);
        $this->user = ApiDoctrine::createObject('sfGuardUser', $user_data['body']);

        $this->auth_tokens = $this->user->getAuthKeysExcluding(sfConfig::get('app_web_app_api_key'));
    }

    public function executeEdit(sfWebRequest $request)
    {
        $user_id = $this->getUser()->getApiUserId();
        $user_data = Api::getInstance()->get('user/' . $user_id);
        $user = ApiDoctrine::createObject('sfGuardUser', $user_data['body']);
        $this->form = new sfGuardUserAdminForm($user);
    }

    public function executeUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isMethod(sfRequest::POST) || $request->isMethod(sfRequest::PUT));
        $user_id = $this->getUser()->getApiUserId();
        $user_data = Api::getInstance()->get('user/' . $user_id);
        $user = ApiDoctrine::createObject('sfGuardUser', $user_data['body']);

        $this->form = new sfGuardUserAdminForm($user);

        $this->processForm($request, $this->form);

        $this->setTemplate('edit');
    }

    public function executeAuth_revoke(sfWebRequest $request)
    {
        $this->forward404Unless($auth_key = Doctrine_Core::getTable('sfGuardUserAuthKey')->find(array($request->getParameter('id'))), sprintf('Auth key does not exist (%s).', $request->getParameter('id')));
        $this->forward404Unless($this->getUser()->getApiUserId() == $auth_key->getSfGuardUserId(), sprintf('Auth key does not exist (%s).', $request->getParameter('id')));

        $auth_key->setIsRevoked(true);
        $auth_key->save();
        $this->getUser()->setFlash('notice', 'Action was completed successfully.');
        $this->redirect('profile');
    }

    protected function processForm(sfWebRequest $request, sfForm $form)
    {
        $form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));
        if ($form->isValid()) {
            $auth_key = $this->getUser()->getApiAuthKey();
            // Update existing item.
            $values = $form->getObject()->getModified();
            $user = $form->getObject();
            if (array_key_exists('id', $values))
                unset($values['id']);
            $id = $this->getUser()->getApiUserId();
            $result = Api::getInstance()->setUser($auth_key)->put('user/' . $id, $values);
            $this->checkHttpCode($result);

            $this->redirect('profile');
        }
    }
    
    protected function checkHttpCode($result)
    {
        $http_code = $result['headers']['http_code'];
        if ($http_code != 200) {
            $message = array_key_exists('message', $result['body']) ? $result['body']['message'] : 'An error occured.';
            $message = array_key_exists(0, $result['body']) && array_key_exists('message', $result['body'][0]) ? $result['body'][0]['message'] : $message;
            $this->getUser()->setFlash('error', "($http_code) $message");
        } else {
            $this->getUser()->setFlash('notice', 'Action was completed successfully.');
        }
    }

}
