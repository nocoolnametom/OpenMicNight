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
        $this->forward404Unless($user_id);
        $user_data = Api::getInstance()->get('user/' . $user_id);
        $this->user = ApiDoctrine::createObject('sfGuardUser', $user_data['body']);

        $this->auth_tokens = $this->user->getAuthKeysExcluding(sfConfig::get('app_web_app_api_key'));
    }

    public function executeEdit(sfWebRequest $request)
    {
        $user_id = $this->getUser()->getApiUserId();
        $this->forward404Unless($user_id);
        $user_data = Api::getInstance()->get('user/' . $user_id);
        $user = ApiDoctrine::createObject('sfGuardUser', $user_data['body']);
        $this->form = new sfGuardUserAdminForm($user);
        unset(
                $this->form['is_active'],
                $this->form['groups_list'],
                $this->form['permissions_list']
                );
    }

    public function executeUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isMethod(sfRequest::POST) || $request->isMethod(sfRequest::PUT));
        $user_id = $this->getUser()->getApiUserId();
        $this->forward404Unless($user_id);
        $user_data = Api::getInstance()->get('user/' . $user_id);
        $user = ApiDoctrine::createObject('sfGuardUser', $user_data['body']);

        $this->form = new sfGuardUserAdminForm($user);
        unset(
                $this->form['is_active'],
                $this->form['groups_list'],
                $this->form['permissions_list']
                );

        $this->processForm($request, $this->form);

        $this->setTemplate('edit');
    }
    
    public function executeEpisodes(sfWebRequest $request)
    {
        $user_id = $this->getUser()->getApiUserId();
        $this->forward404Unless($user_id);
        $page = $this->page = (int)$request->getParameter('page', 1);
        $this->forward404Unless(is_integer($page));
        $page = ($page == 1 || $page == 0) ? '' : '&page=' . $page;
        $subreddit_ids = array();
        $released_data = Api::getInstance()->get('episode/released?sf_guard_user_id=' . $user_id . $page);
        $this->released = ApiDoctrine::createQuickObjectArray($released_data['body']);
        $future_data = Api::getInstance()->get('episodeassignment/future?sf_guard_user_id=' . $user_id);
        $this->future = ApiDoctrine::createQuickObjectArray($future_data['body']);
        foreach($this->future as $assignment)
        {
            if (!in_array($assignment->getEpisode()->getSubredditId(), $subreddit_ids))
                $subreddit_ids[] = $assignment->getEpisode()->getSubredditId();
        }
        $this->current = array();
        foreach($this->future as $key => $assignment)
        {
            if ($assignment->getEpisode()->getSfGuardUserId() == $assignment->getSfGuardUserId())
            {
                $this->current[] = $assignment;
                unset($this->future[$key]);
            }
        }
        
        foreach($this->released as $episode)
        {
            if (!in_array($episode->getSubredditId(), $subreddit_ids))
                $subreddit_ids[] = $episode->getSubredditId();
        }
        
        $subreddit_data = Api::getInstance()->get('subreddit?id=' . implode(',', $subreddit_ids), true);
        
        $subreddits = ApiDoctrine::createQuickObjectArray($subreddit_data['body']);
        $this->subreddits = array();
        foreach($subreddits as $subreddit)
        {
            $this->subreddits[$subreddit->getId()] = $subreddit;
        }
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
            $values = $form->getTaintedValues();
            $user_id = $form->getValue('id') ? $form->getValue('id') : $this->getUser()->getApiUserId();
            unset(
                    $values['_csrf_token'],
                    $values['is_active'],
                    $values['password'],
                    $values['password_again'],
                    $values['groups_list'],
                    $values['permissions_list'],
                    $values['is_validated'],
                    $values['reddit_validation_key'],
                    $values['is_authorized'],
                    $values['email_authorization_key'],
                    $values['authorized_at'],
                    $values['is_super_admin'],
                    $values['algorithm'],
                    $values['id'],
                    $values['salt'],
                    $values['last_login']
                    );
            if ($form->getValue('password'))
                $values['password'] = $form->getValue('password');
            $user_data = Api::getInstance()->get('user/' . $user_id);
            $user = ApiDoctrine::createObject('sfGuardUser', $user_data['body']);
            $user_values = $user->toArray();
            foreach($values as $key => $value)
            {
                if ($value == $user_values[$key])
                    unset($values[$key]);
            }
            
            if (array_key_exists('id', $values))
                unset($values['id']);
            $id = $this->getUser()->getApiUserId();
            $result = Api::getInstance()->setUser($auth_key)->put('user/' . $id, $values);
            $success = $this->checkHttpCode($result);
            if ($success)
                $this->getUser()->setFlash('notice', 'Profile was edited successfully.');

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
            return false;
        } else {
            return true;
        }
    }

}
