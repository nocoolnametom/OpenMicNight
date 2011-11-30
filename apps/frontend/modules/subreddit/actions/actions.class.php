<?php

/**
 * subreddit actions.
 *
 * @package    OpenMicNight
 * @subpackage subreddit
 * @author     Tom Doggett
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class subredditActions extends sfActions
{

    public function executeIndex(sfWebRequest $request)
    {
        $auth_key = $this->getUser()->getApiAuthKey();
        $subreddit_data = Api::getInstance()->setUser($auth_key)->get('subreddit', true);
        $this->subreddits = ApiDoctrine::createQuickObjectArray($subreddit_data['body']);
    }

    public function executeNew(sfWebRequest $request)
    {
        $this->form = new SubredditForm();
    }

    public function executeCreate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isMethod(sfRequest::POST));

        $this->form = new SubredditForm();

        $this->processForm($request, $this->form);

        $this->setTemplate('new');
    }

    public function executeEdit(sfWebRequest $request)
    {
        $auth_key = $this->getUser()->getApiAuthKey();
        $subreddit_data = Api::getInstance()->setUser($auth_key)->get('subreddit/' . $request->getParameter('id'), true);
        $subreddit = ApiDoctrine::createObject('Subreddit', $subreddit_data['body']);
        $this->forward404Unless($subreddit && $subreddit->getId());

        $this->form = new SubredditForm($subreddit);
    }

    public function executeUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isMethod(sfRequest::POST) || $request->isMethod(sfRequest::PUT));

        $auth_key = $this->getUser()->getApiAuthKey();
        $subreddit_data = Api::getInstance()->setUser($auth_key)->get('subreddit/' . $request->getParameter('id'), true);
        $subreddit = ApiDoctrine::createObject('Subreddit', $subreddit_data['body']);
        $this->forward404Unless($subreddit && $subreddit->getId());

        $this->form = new SubredditForm($subreddit);

        $this->processForm($request, $this->form);

        $this->setTemplate('edit');
    }

    public function executeDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $auth_key = $this->getUser()->getApiAuthKey();
        $subreddit_data = Api::getInstance()->setUser($auth_key)->get('subreddit/' . $request->getParameter('id'), true);
        $subreddit = ApiDoctrine::createObject('Subreddit', $subreddit_data['body']);
        $this->forward404Unless($subreddit && $subreddit->getId());

        //$subreddit->delete();
        $result = Api::getInstance()->setUser($auth_key)->delete('subreddit/' . $subreddit->getId(), true);
        $this->checkHttpCode($result);

        $this->redirect('subreddit/index');
    }

    protected function processForm(sfWebRequest $request, sfForm $form)
    {
        $form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));
        if ($form->isValid()) {
            $auth_key = $this->getUser()->getApiAuthKey();
            if ($form->getValue('id')) {
                // Update existing item.
                $values = $form->getObject()->getModified();
                $subreddit = $form->getObject();
                unset($values['id']);
                $id = $form->getValue('id');
                $result = Api::getInstance()->setUser($auth_key)->put('subreddit/' . $id, $values);
                $this->checkHttpCode($result);
                $test_subreddit = ApiDoctrine::createObject('Subreddit', $result['body']);
                $subreddit = $test_subreddit ? $test_subreddit : $subreddit;
            } else {
                // Create new item
                $values = $form->getValues();
                $subreddit = $form->getObject();
                foreach ($values as $key => $value) {
                    if (is_null($value))
                        unset($values[$key]);
                }
                $result = Api::getInstance()->setUser($auth_key)->post('subreddit', $values);
                $this->checkHttpCode($result);
                $test_subreddit = ApiDoctrine::createObject('Subreddit', $result['body']);
                $subreddit = $test_subreddit ? $test_subreddit : $subreddit;
                if (is_null($subreddit->getIncremented()))
                    $this->redirect('subreddit');
            }

            $this->redirect('subreddit/edit?id=' . $subreddit->getId());
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
