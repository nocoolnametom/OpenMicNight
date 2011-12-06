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
        $episodes_data = Api::getInstance()->setUser($auth_key)->get('episode/released', true);
        $this->episodes = ApiDoctrine::createQuickObjectArray($episodes_data['body']);
    }

    public function executeAssign(sfWebRequest $request)
    {
        $auth_key = $this->getUser()->getApiAuthKey();
        $this->forward404Unless($request->getParameter('author_type_id') || $request->getParameter('episode_id'));
        $author_type_id = $request->getParameter('author_type_id');
        $episode_id = $request->getParameter('episode_id');
        $episode = EpisodeTable::getInstance()->find($episode_id);
        $user_id = $this->getUser()->getApiUserId();
        $post_values = array(
            'author_type_id' => $author_type_id,
            'episode_id' => $episode_id,
            'sf_guard_user_id' => $user_id,
        );
        $create = Api::getInstance()->setUser($auth_key)->post('episodeassignment', $post_values, false);
        if ($create['headers']['http_code'] == 200)
            $this->getUser()->setFlash('notice', 'Registered for Episode!');
        else
            $this->getUser()->setFlash('error', 'An error occured.');
        
        $this->redirect('subreddit/signup?domain=' . $episode->getSubreddit()->getDomain());
    }

    public function executeEdit(sfWebRequest $request)
    {
        $auth_key = $this->getUser()->getApiAuthKey();
        $episode_data = Api::getInstance()->setUser($auth_key)->get('episode/' . $request->getParameter('id'), true);
        // Cheating for this one for now.
        //$episode = ApiDoctrine::createQuickObject($episode_data['body']);
        $episode = EpisodeTable::getInstance()->find($request->getParameter('id'));
        $this->forward404Unless($episode && $episode->getId());
        $this->forward404Unless(strtotime($episode->getReleaseDate()) >= time());
        
        $this->is_submitted = (bool)$episode->getIsSubmitted();
        $this->is_approved = (bool)$episode->getIsApproved();
        
        $this->form = new EpisodeForm($episode);
        unset($this->form['sf_guard_user_id']);
        unset($this->form['file_is_remote']);
        unset($this->form['remote_url']);
        unset($this->form['approved_at']);
    }
    
    public function executeApprove(sfWebRequest $request)
    {
        $auth_key = $this->getUser()->getApiAuthKey();
        $episode_data = Api::getInstance()->setUser($auth_key)->get('episode/' . $request->getParameter('id'), true);
        $episode = ApiDoctrine::createObject('Episode', $episode_data['body']);
        $this->forward404Unless($episode && $episode->getId());
        $this->forward404Unless(strtotime($episode->getReleaseDate()) >= time());
        
        $this->form = new EpisodeForm($episode);
        unset($this->form['sf_guard_user_id']);
        unset($this->form['file_is_remote']);
        unset($this->form['remote_url']);
        unset($this->form['approved_at']);
    }
    
    public function executeSubmit(sfWebRequest $request)
    {
        $auth_key = $this->getUser()->getApiAuthKey();
        $episode_data = Api::getInstance()->setUser($auth_key)->get('episode/' . $request->getParameter('id'), true);
        $episode = ApiDoctrine::createObject('Episode', $episode_data['body']);
        $this->forward404Unless($episode && $episode->getId());
        $this->forward404Unless(strtotime($episode->getReleaseDate()) >= time());
        
        $submission_change = array(
            'is_submitted' => 1,
        );
        $result = $episode_data = Api::getInstance()->setUser($auth_key)->put('episode/' . $episode->getIncremented(), $submission_change, true);
        $this->checkHttpCode($result);
        $this->redirect('episode/edit?id=' . $episode->getId());
    }

    public function executeUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isMethod(sfRequest::POST) || $request->isMethod(sfRequest::PUT));

        $auth_key = $this->getUser()->getApiAuthKey();
        $episode_data = Api::getInstance()->setUser($auth_key)->get('episode/' . $request->getParameter('id'), true);
        $episode = ApiDoctrine::createObject('Episode', $episode_data['body']);
        $this->forward404Unless($episode && $episode->getId());

        $this->form = new EpisodeForm($episode);
        unset($this->form['sf_guard_user_id']);
        unset($this->form['file_is_remote']);
        unset($this->form['remote_url']);
        unset($this->form['approved_at']);

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
        $result = Api::getInstance()->setUser($auth_key)->delete('episode/' . $episode->getId(), true);
        $this->checkHttpCode($result);

        $this->redirect('episode/index');
    }

    protected function processForm(sfWebRequest $request, sfForm $form)
    {
        $form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));
        if ($form->isValid()) {
            $auth_key = $this->getUser()->getApiAuthKey();
            if ($form->getValue('id')) {
                // Update existing item.
                $values = $form->getTaintedValues();
                unset(
                    $values['_csrf_token'],
                    $values['id']
                );
                $episode = $form->getObject();
                if (!array_key_exists('is_nsfw', $values) && $episode->getIsNsfw())
                        $values['is_nsfw'] = '0';
                foreach($values as $key => $value)
                {
                    if ($value == "on")
                        $values[$key] = 1;
                    if ($value == "off")
                        $values[$key] = 0;
                }
                $id = $episode->getId();
                $result = Api::getInstance()->setUser($auth_key)->put('episode/' . $id, $values);
                $this->checkHttpCode($result);
                $test_episode = ApiDoctrine::createObject('Episode', $result['body']);
                $episode = $test_episode ? $test_episode : $episode;
            }
            $this->redirect('episode/edit?id=' . $episode->getId());
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
