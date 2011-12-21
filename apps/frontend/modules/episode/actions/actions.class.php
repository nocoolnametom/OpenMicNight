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

    public function preExecute()
    {
        parent::preExecute();
        $request = $this->getRequest();
        if ($request->hasAttribute('api_log')) {
            $dispatcher = sfApplicationConfiguration::getActive()
                    ->getEventDispatcher();
            $string = $request->getAttribute('api_log');
            $dispatcher->notify(new sfEvent('Api', 'application.log', array(
                        'priority' => sfLogger::WARNING,
                        $string
                    )));
            $request->getAttributeHolder()->remove('api_log');
        }
    }

    public function executeIndex(sfWebRequest $request)
    {
        $auth_key = $this->getUser()->getApiAuthKey();
        $page = $this->page = (int) $request->getParameter('page', 1);
        $this->forward404Unless(is_integer($page));
        $page = ($page == 1 || $page == 0) ? '' : '?page=' . $page;
        $episodes_data = Api::getInstance()->setUser($auth_key)->get('episode/released' . $page, true);
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
        $result = Api::getInstance()->setUser($auth_key)->post('episodeassignment', $post_values, false);
        $success = $this->checkHttpCode($result, 'post', 'episodeassignment', json_encode($post_values));
        if ($success)
            $this->getUser()->setFlash('notice', 'Registered for Episode!');

        $this->redirect('subreddit/signup?domain=' . $episode->getSubreddit()->getDomain());
    }

    public function executeEdit(sfWebRequest $request)
    {
        // Clear pluploader session variables.
        $this->getUser()->getAttributeHolder()->remove('valid_episode');
        $this->getUser()->getAttributeHolder()->remove('valid_episode_id');
        $this->getUser()->getAttributeHolder()->remove('valid_episode_user_id');
        $this->getUser()->getAttributeHolder()->remove('valid_episode_audio_file_hash');
        $this->getUser()->getAttributeHolder()->remove('valid_episode_image_file_hash');
        $this->getUser()->getAttributeHolder()->remove('valid_episode_user_id');

        $auth_key = $this->getUser()->getApiAuthKey();
        $episode_data = Api::getInstance()->setUser($auth_key)->get('episode/' . $request->getParameter('id'), true);
        $episode = ApiDoctrine::createObject('Episode', $episode_data['body']);
        $quick_episode = ApiDoctrine::createQuickObject($episode_data['body']);
        $this->forward404Unless($episode && $episode->getId());
        $this->forward404Unless(strtotime($quick_episode->getReleaseDate()) >= time());
        // If the episode is not released, only the admins and moderators can view it.
        $permission = $this->verifyPermissionsForCurrentUser($quick_episode->getSubredditId(), array('admin'));
        // Unless the owner of the episode is trying to download it.  That's okay.
        $this->forward404Unless($permission || $quick_episode->getSfGuardUserId() == $this->getUser()->getApiUserId());

        $assignment_data = Api::getInstance()->setUser($auth_key)->get('episodeassignment?episode_id=' . $quick_episode->getId() . '&sf_guard_user_id=' . $this->getUser()->getApiUserId() . '&missed_deadline=0', true);
        $this->forward404Unless(array_key_exists(0, $assignment_data['body']));
        $assignment = ApiDoctrine::createQuickObject($assignment_data['body'][0]);
        $author_type_id = $assignment->getAuthorTypeId();

        $deadline_data = Api::getInstance()->setUser($auth_key)->get('subredditdeadline?subreddit_id=' . $quick_episode->getSubredditId() . '&author_type_id=' . $author_type_id, true);
        $this->forward404Unless(array_key_exists(0, $deadline_data['body']));
        $deadline = ApiDoctrine::createQuickObject($deadline_data['body'][0]);

        $this->deadline = strtotime($quick_episode->getReleaseDate()) - $deadline->getSeconds();

        $this->is_submitted = (bool) $quick_episode->getIsSubmitted();
        $this->is_approved = (bool) $quick_episode->getIsApproved();


        $this->form = new EpisodeForm($episode);
        $this->form->setDefault('is_nsfw', $quick_episode->getIsNsfw());
        unset($this->form['sf_guard_user_id']);
        unset($this->form['file_is_remote']);
        unset($this->form['remote_url']);
        unset($this->form['approved_at']);
        unset($this->form['nice_filename']);

        $this->graphic_hash = sha1(
                sfConfig::get('app_web_app_image_hash_salt')
                . (int) $request->getParameter('id')
                . (int) $this->getUser()->getApiUserId()
        );

        $this->audio_hash = sha1(
                sfConfig::get('app_web_app_audio_hash_salt')
                . (int) $request->getParameter('id')
                . (int) $this->getUser()->getApiUserId()
        );
    }

    public function executeAudio(sfWebRequest $request)
    {
        $auth_key = $this->getUser()->getApiAuthKey();
        $episode_data = Api::getInstance()->setUser($auth_key)->get('episode/' . $request->getParameter('id'), true);
        $episode = ApiDoctrine::createQuickObject($episode_data['body']);
        $this->forward404Unless($episode && $episode->getId());

        // If the episode is released, everyone is allowed to download the file from Amazon
        if ($episode->getReleaseDate('U') > time()) {
            // @todo Redirect to the Amazon location.
        }

        // If the episode is not released, only the admins and moderators can view it.
        $permission = $this->verifyPermissionsForCurrentUser($episode->getSubredditId(), array('admin', 'moderator'));

        // Unless the owner of the episode is trying to download it.  That's okay.
        $this->forward404Unless($permission || $episode->getSfGuardUserId() == $this->getUser()->getApiUserId());

        // Now that we're serving the local file, let's set up the server to serve it.
        header('Content-Disposition: attachment;filename=' . $episode->getNiceFilename());
        switch ($request->getParameter('format')) {
            case "wma":
                header("Content-Type: audio/x-ms-wma");
                break;
            case "m4a":
                header("Content-Type: audio/mp4a-latm");
                break;
            case "ogg":
                header("Content-Type: application/ogg");
                break;
            default:
            case 'mp3':
                header("Content-Type: audio/mpeg");
                break;
        }

        // Check if we're using nginx and if nxginx and XSendfile are installed and use that
        if (array_key_exists('SERVER_SOFTWARE', $_SERVER) && preg_match('/nginx/i', $_SERVER['SERVER_SOFTWARE'])) {
            header("X-Accel-Redirect: /audio/temp/" . $episode->getAudioFile());
            die();
        }

        // If not, check if Apache has mod_xsendfile and use that
        if (array_key_exists('SERVER_SOFTWARE', $_SERVER) && preg_match('/apache/i', $_SERVER['SERVER_SOFTWARE']) && in_array('mod_xsendfile', apache_get_modules())) {
            header('X-Sendfile: ' . sfConfig::get('sf_data_dir') . '/temp/' . $episode->getAudioFile());
            die();
        }

        // If not that, then we'll try and see if we have lighttpd
        if (array_key_exists('SERVER_SOFTWARE', $_SERVER) && preg_match('/lighttpd/i', $_SERVER['SERVER_SOFTWARE'])) {
            header('X-LIGHTTPD-send-file: ' . sfConfig::get('sf_data_dir') . '/temp/' . $episode->getAudioFile());
            die();
        }

        // Otherwise, let's waste time by loading the file into memory and serving it through PHP (horror!  Not a joke!)
        if (sfConfig::get('app_enable_slow_audio_download', false)) {
            $filename = sfConfig::get('sf_data_dir') . '/temp/' . $episode->getAudioFile();
            header("Content-type: application/octet-stream");
            header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
            header("Content-Length: " . filesize($filename));
            readfile($filename);
            die();
        }
    }

    public function executeApproval(sfWebRequest $request)
    {
        $auth_key = $this->getUser()->getApiAuthKey();
        $episode_data = Api::getInstance()->setUser($auth_key)->get('episode/' . $request->getParameter('id'), true);
        $this->episode = ApiDoctrine::createQuickObject($episode_data['body']);
        $this->forward404Unless($this->episode && $this->episode->getId());
        $this->forward404Unless(strtotime($this->episode->getReleaseDate()) >= time());
        $this->forward404If($this->episode->getIsApproved());
        // If the episode is not released, only the admins and moderators can view it.
        $permission = $this->verifyPermissionsForCurrentUser($this->episode->getSubredditId(), array('admin', 'moderator'));
        // The owner of the Episode *cannot* be an approver for the episode.
        $this->forward404If($this->episode->getSfGuardUserId() == $this->getUser()->getApiUserId());

        $this->forward404Unless($permission);

        $subreddit_data = Api::getInstance()->setUser($auth_key)->get('subreddit/' . $this->episode->getSubredditId(), true);
        $this->subreddit = ApiDoctrine::createQuickObject($subreddit_data['body']);

        $assignment_data = Api::getInstance()->setUser($auth_key)->get('episodeassignment?episode_id=' . $this->episode->getId() . '&sf_guard_user_id=' . $this->episode->getSfGuardUserId() . '&missed_deadline=0', true);
        $this->forward404Unless(array_key_exists(0, $assignment_data['body']));
        $assignment = ApiDoctrine::createQuickObject($assignment_data['body'][0]);
        $author_type_id = $assignment->getAuthorTypeId();

        $deadline_data = Api::getInstance()->setUser($auth_key)->get('subredditdeadline?subreddit_id=' . $this->episode->getSubredditId() . '&author_type_id=' . $author_type_id, true);
        $this->forward404Unless(array_key_exists(0, $deadline_data['body']));
        $deadline = ApiDoctrine::createQuickObject($deadline_data['body'][0]);

        $this->deadline = strtotime($this->episode->getReleaseDate()) - $deadline->getSeconds();

        $this->is_submitted = (bool) $this->episode->getIsSubmitted();
        $this->is_approved = (bool) $this->episode->getIsApproved();
    }

    public function executeSubmit(sfWebRequest $request)
    {
        $auth_key = $this->getUser()->getApiAuthKey();
        $episode_data = Api::getInstance()->setUser($auth_key)->get('episode/' . $request->getParameter('id'), true);
        $episode = ApiDoctrine::createQuickObject($episode_data['body']);
        $this->forward404Unless($episode && $episode->getId());
        $this->forward404Unless(strtotime($episode->getReleaseDate()) >= time());
        $this->forward404Unless($episode->getSfGuardUserId() == $this->getUser()->getApiUserId());

        $submission_change = array(
            'is_submitted' => 1,
        );
        $result = $episode_data = Api::getInstance()->setUser($auth_key)->put('episode/' . $episode->getIncremented(), $submission_change, true);
        $success = $this->checkHttpCode($result, 'put', 'episode/' . $episode->getIncremented(), json_encode($submission_change));
        if ($success)
            $this->getUser()->setFlash('notice', 'Episode was submitted for approval.');
        $this->redirect('episode/edit?id=' . $episode->getId());
    }

    public function executeApprove(sfWebRequest $request)
    {
        $auth_key = $this->getUser()->getApiAuthKey();
        $episode_data = Api::getInstance()->setUser($auth_key)->get('episode/' . $request->getParameter('id'), true);
        $this->episode = ApiDoctrine::createQuickObject($episode_data['body']);
        $this->forward404Unless($this->episode && $this->episode->getId());
        $this->forward404Unless(strtotime($this->episode->getReleaseDate()) >= time());
        $this->forward404If($this->episode->getIsApproved());
        // If the episode is not released, only the admins and moderators may approve it
        $permission = $this->verifyPermissionsForCurrentUser($this->episode->getSubredditId(), array('admin', 'moderator'));
        $this->forward404Unless($permission);
        // The owner of the Episode *cannot* be an approver for the episode.
        $this->forward404If($this->episode->getSfGuardUserId() == $this->getUser()->getApiUserId());
        
        $subreddit_data = Api::getInstance()->setUser($auth_key)->get('subreddit/' . $this->episode->getSubredditId(), true);
        $this->subreddit = ApiDoctrine::createQuickObject($subreddit_data['body']);

        $submission_change = array(
            'is_approved' => 1,
            'approved_by' => $this->getUser()->getApiUserid(),
        );
        $result = Api::getInstance()->setUser($auth_key)->put('episode/' . $this->episode->getIncremented(), $submission_change, true);
        $success = $this->checkHttpCode($result, 'put', 'episode/' . $this->episode->getIncremented(), json_encode($submission_change));
        if ($success)
        {
            $this->getUser()->setFlash('notice', 'Episode was approved and will appear on its release date.');
            $this->redirect('profile/episodes');
        }
        $this->setTemplate('approval');
    }

    public function executeShow(sfWebRequest $request)
    {
        $auth_key = $this->getUser()->getApiAuthKey();
        $episode_data = Api::getInstance()->setUser($auth_key)->get('episode/' . $request->getParameter('id'), true);
        $this->episode = ApiDoctrine::createQuickObject($episode_data['body']);
        $this->forward404Unless($this->episode && $this->episode->getId());
        if (strtotime($this->episode->getReleaseDate()) >= time()) {
            // If the episode is not released, only the admins,  moderators, and submitter can view it.
            $permission = $this->verifyPermissionsForCurrentUser($this->episode->getSubredditId(), array('admin', 'moderator'));
            $this->forward404Unless($permission || $this->episode->getSfGuardUserId() == $this->getUser()->getApiUserId());
        } else {
            $this->forward404Unless($this->episode->getIsApproved());
        }

        $subreddit_data = Api::getInstance()->setUser($auth_key)->get('subreddit/' . $this->episode->getSubredditId(), true);
        $this->subreddit = ApiDoctrine::createQuickObject($subreddit_data['body']);
    }

    public function executeUpdate(sfWebRequest $request)
    {
        // Clear pluploader session variables
        $this->getUser()->getAttributeHolder()->remove('valid_episode');
        $this->getUser()->getAttributeHolder()->remove('valid_episode_id');
        $this->getUser()->getAttributeHolder()->remove('valid_episode_user_id');
        $this->getUser()->getAttributeHolder()->remove('valid_episode_audio_file_hash');
        $this->getUser()->getAttributeHolder()->remove('valid_episode_image_file_hash');
        $this->getUser()->getAttributeHolder()->remove('valid_episode_user_id');

        $this->forward404Unless($request->isMethod(sfRequest::POST) || $request->isMethod(sfRequest::PUT));

        $auth_key = $this->getUser()->getApiAuthKey();
        $episode_data = Api::getInstance()->setUser($auth_key)->get('episode/' . $request->getParameter('id'), true);
        $episode = ApiDoctrine::createObject('Episode', $episode_data['body']);
        $quick_episode = ApiDoctrine::createQuickObject($episode_data['body']);
        $this->forward404Unless($episode && $episode->getId());
        $permission = $this->verifyPermissionsForCurrentUser($quick_episode->getSubredditId(), array('admin'));
        $this->forward404Unless($permission || $quick_episode->getSfGuardUserId() == $this->getUser()->getApiUserId());
        
        $episode->setIsNsfw($quick_episode->getIsNsfw());

        $this->form = new EpisodeForm($episode);
        $this->form->setDefault('is_nsfw', $quick_episode->getIsNsfw());
        unset($this->form['sf_guard_user_id']);
        unset($this->form['file_is_remote']);
        unset($this->form['remote_url']);
        unset($this->form['approved_at']);
        unset($this->form['nice_filename']);

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
        $permission = $this->verifyPermissionsForCurrentUser($quick_episode->getSubredditId(), array('admin'));
        $this->forward404Unless($permission);

        //$episode->delete();
        $result = Api::getInstance()->setUser($auth_key)->delete('episode/' . $episode->getId(), true);
        $success = $this->checkHttpCode($result, 'episode/' . $episode->getId());
        if ($success)
            $this->getUser()->setFlash('notice', 'Episode was deleted successfully.');

        $this->redirect('episode/index');
    }

    protected function verifyPermissionsForCurrentUser($subreddit_id, $permissions = array())
    {
        $membership = sfGuardUserSubredditMembershipTable::getInstance()->getFirstByUserSubredditAndMemberships(
                $this->getUser()->getApiUserId(), $subreddit_id, $permissions
        );
        return ($membership ? true : false);
    }

    protected function processForm(sfWebRequest $request, EpisodeForm $form)
    {
        $form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));
        if ($form->isValid()) {
            $form->processValues($form->getValues());
            $auth_key = $this->getUser()->getApiAuthKey();
            if ($form->getValue('id')) {
                // Update existing item.
                $values = $form->getTaintedValues();
                if ($form->getValue('audio_file_delete') == true) {
                    if (!$form->getObject()->getApprovedAt() && !$form->getObject()->getSubmittedAt()) {
                        $values['audio_file'] = null;
                        $values['nice_filename'] = null;
                        unlink(sfConfig::get('sf_data_dir') . '/temp/' . $form->getObject()->getAudioFile());
                    }
                }
                if ($form->getValue('graphic_file_delete') == true) {
                    if (!$form->getObject()->getApprovedAt()) {
                        $values['graphic_file'] = null;
                        unlink(sfConfig::get('sf_web_dir') . '/uploads/graphics/' . $form->getObject()->getGraphicFile());
                    }
                }
                unset(
                        $values['_csrf_token'],
                        $values['id'],
                        $values['graphic_file_delete'],
                        $values['audio_file_delete']
                );
                $episode = $form->getObject();
                if (!array_key_exists('is_nsfw', $values) && $episode->getIsNsfw())
                    $values['is_nsfw'] = 0;
                foreach ($values as $key => $value) {
                    if ($value == "on" )
                        $values[$key] = 1;
                    if ($value == "off")
                        $values[$key] = 0;
                }
                $id = $episode->getId();
                $result = Api::getInstance()->setUser($auth_key)->put('episode/' . $id, $values);
                $success = $this->checkHttpCode($result, 'put', 'episode/' . $id, json_encode($values));
                if ($success)
                    $this->getUser()->setFlash('notice', 'Episode was saved successfully.');
                $test_episode = ApiDoctrine::createObject('Episode', $result['body']);
                $episode = $test_episode ? $test_episode : $episode;
            }
            $this->redirect('episode/edit?id=' . $episode->getId());
        }
    }

    protected function checkHttpCode($result, $getpost = null, $location = null, $request = null)
    {
        $http_code = $result['headers']['http_code'];
        if ($http_code != 200) {
            $message = array_key_exists('message', $result['body']) ? $result['body']['message'] : 'An error occured.';
            $message = array_key_exists(0, $result['body']) && array_key_exists('message', $result['body'][0]) ? $result['body'][0]['message'] : $message;
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
