<?php

/**
 * episode actions.
 *
 * @package    OpenMicNight
 * @subpackage episode
 * @author     Tom Doggett
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class episodeActions extends sfActions
{

    public function executeIndex(sfWebRequest $request)
    {
        $auth_key = $this->getUser()->getApiAuthKey();
        $episodes_data = Api::getInstance()->setUser($auth_key)->get('episode', true);
        $this->episodes = ApiDoctrine::createObjectArray('Episode',
                                                         $episodes_data['body']);
    }

    public function executeNew(sfWebRequest $request)
    {
        $this->form = new EpisodeForm();
    }

    public function executeCreate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isMethod(sfRequest::POST));

        $this->form = new EpisodeForm();

        $this->processForm($request, $this->form);

        $this->setTemplate('new');
    }

    public function executeEdit(sfWebRequest $request)
    {
        $auth_key = $this->getUser()->getApiAuthKey();
        $episode_data = Api::getInstance()->setUser($auth_key)->get('episode/' . $request->getParameter('id'),
                                                                                    true);
        $episode = ApiDoctrine::createObject('Episode', $episode_data['body']);
        $this->forward404Unless($episode && $episode->getId());

        $this->form = new EpisodeForm($episode);
    }

    public function executeUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isMethod(sfRequest::POST) || $request->isMethod(sfRequest::PUT));

        $auth_key = $this->getUser()->getApiAuthKey();
        $episode_data = Api::getInstance()->setUser($auth_key)->get('episode/' . $request->getParameter('id'),
                                                                                    true);
        $episode = ApiDoctrine::createObject('Episode', $episode_data['body']);
        $this->forward404Unless($episode && $episode->getId());

        $this->form = new EpisodeForm($episode);

        $this->processForm($request, $this->form);

        $this->setTemplate('edit');
    }

    public function executeDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $auth_key = $this->getUser()->getApiAuthKey();
        $episode_data = Api::getInstance()->setUser($auth_key)->get('episode/' . $request->getParameter('id'));
        $episode = ApiDoctrine::createObject('Episode', $episode_data['body']);
        $this->forward404Unless($episode && $episode->getId());

        //$episode->delete();
        $result = Api::getInstance()->setUser($auth_key)->delete('episode/' . $episode->getId(),
                                             true);
        $this->checkHttpCode($result);

        $this->redirect('episode/index');
    }

    protected function processForm(sfWebRequest $request, sfForm $form)
    {
        $form->bind($request->getParameter($form->getName()),
                                           $request->getFiles($form->getName()));
        if ($form->isValid()) {
            $auth_key = $this->getUser()->getApiAuthKey();
            if ($form->getValue('id')) {
                // Update existing item.
                $values = $form->getObject()->getModified();
                //die(var_dump($values));
                unset($values['id']);
                $id = $form->getValue('id');
                $result = Api::getInstance()->setUser($auth_key)->put('episode/' . $id, $values);
                $this->checkHttpCode($result);
            } else {
                // Create new item
                $values = $values = $form->getObject()->getModified();
                $result = Api::getInstance()->setUser($auth_key)->post('episode', $values);
                $this->checkHttpCode($result);
            }

            $this->redirect('episode/edit?id=' . $episode->getId());
        }
    }

    protected function checkHttpCode($result)
    {
        if ($result['headers']['http_code'] != 200) {
            $message = array_key_exists('message', $result['body']) ? $result['body']['message']
                        : 'An error occured.';
            $message = array_key_exists(0, $result['body']) && array_key_exists('message',
                                                                                $result['body'][0])
                        ? $result['body'][0]['message'] : $message;
            throw new sfException($message);
        }
    }
}
