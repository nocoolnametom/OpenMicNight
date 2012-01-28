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

    public function executeBackup(sfWebRequest $request)
    {
        $this->redirectUnless($this->getUser()->isAuthenticated() && $this->getUser()->getApiUserId(), '@sf_guard_signin');

        $auth_key = $this->getUser()->getApiAuthKey();
        $episode_data = Api::getInstance()->setUser($auth_key)->get('episode/' . $request->getParameter('id'), true);
        $episode = ApiDoctrine::createObject('Episode', $episode_data['body']);
        $quick_episode = ApiDoctrine::createQuickObject($episode_data['body']);
        $this->forward404Unless($episode && $episode->getId());
        $this->forward404Unless(strtotime($quick_episode->getReleaseDate()) >= time());
        // If the episode is not released, only the admins and moderators can view it.
        $permission = $this->verifyPermissionsForCurrentUser($quick_episode->getSubredditId(), array('admin'));
        // Unless the owner of the episode is trying to edit it.  That's okay.
        $assignment_data = Api::getInstance()->setUser($auth_key)->get('episodeassignment/' . $quick_episode->getEpisodeAssignmentId(), true);
        $assignment = ApiDoctrine::createQuickObject($assignment_data['body']);
        $this->forward404Unless($permission || ($assignment && $assignment->getSfGuardUserId() == $this->getUser()->getApiUserId()));
        $this->setLayout(false);
        
        ProjectConfiguration::registerAws();

        if ($request->getParameter('which') == 'graphic') {
            $file_location = rtrim(ProjectConfiguration::getEpisodeGraphicFileLocalDirectory(), '/') . '/';
            $filename = $episode->getGraphicFile();
            if (file_exists($file_location . $filename)) {
                $result = $episode->saveFileToApplicationBucket($file_location, $filename, 'upload', AmazonS3::ACL_PUBLIC);
                if ($result->isOk())
                {
                    unlink($file_location . $filename);
                }
            } else {
                echo $file_location . $filename;
            }
        } elseif ($request->getParameter('which') == 'audio') {
            $file_location = rtrim(ProjectConfiguration::getEpisodeAudioFileLocalDirectory(), '/') . '/';
            $filename = $episode->getAudioFile();
            if (file_exists($file_location . $filename)) {
                $episode->saveFileToApplicationBucket($file_location, $filename, 'audio');
            }
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

        $subreddit_ids = array();
        $assignment_ids = array();

        foreach ($this->episodes as $episode) {
            if (!in_array($episode->getSubredditId(), $subreddit_ids)) {
                $subreddit_ids[] = $episode->getSubredditId();
            }
            $assignment_ids[] = $episode->getEpisodeAssignmentId();
        }

        $subreddit_data = Api::getInstance()->setUser($auth_key)->get('subreddit?id='
                . implode(',', $subreddit_ids), true);
        $subreddits = ApiDoctrine::createQuickObjectArray($subreddit_data['body']);
        $this->subreddits = array();
        foreach ($subreddits as $subreddit) {
            $this->subreddits[$subreddit->getIncremented()] = $subreddit;
        }

        $assignment_data = Api::getInstance()->setUser($auth_key)->get('episodeassignment?id='
                . implode(',', $assignment_ids), true);
        $assignments = ApiDoctrine::createQuickObjectArray($assignment_data['body']);
        $this->assignments = array();
        $user_ids = array();
        foreach ($assignments as $assignment) {
            $this->assignments[$assignment->getIncremented()] = $assignment;
            $user_ids[] = $assignment->getSfGuardUserId();
        }

        $user_data = Api::getInstance()->setUser($auth_key)->get('user?id='
                . implode(',', $user_ids), true);
        $users = ApiDoctrine::createQuickObjectArray($user_data['body']);
        $this->users = array();
        foreach ($users as $user) {
            $this->users[$user->getIncremented()] = $user;
        }
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
            $this->getUser()->setFlash('notice', 'Registered for Episode!  You will be notified when it becomes available.');

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

        $this->redirectUnless($this->getUser()->isAuthenticated() && $this->getUser()->getApiUserId(), '@sf_guard_signin');

        $auth_key = $this->getUser()->getApiAuthKey();
        $episode_data = Api::getInstance()->setUser($auth_key)->get('episode/' . $request->getParameter('id'), true);
        $episode = ApiDoctrine::createObject('Episode', $episode_data['body']);
        $quick_episode = ApiDoctrine::createQuickObject($episode_data['body']);
        $this->forward404Unless($episode && $episode->getId());
        $this->forward404Unless(strtotime($quick_episode->getReleaseDate()) >= time());
        // If the episode is not released, only the admins and moderators can view it.
        $permission = $this->verifyPermissionsForCurrentUser($quick_episode->getSubredditId(), array('admin'));
        // Unless the owner of the episode is trying to edit it.  That's okay.
        $assignment_data = Api::getInstance()->setUser($auth_key)->get('episodeassignment/' . $quick_episode->getEpisodeAssignmentId(), true);
        $assignment = ApiDoctrine::createQuickObject($assignment_data['body']);
        $this->forward404Unless($permission || ($assignment && $assignment->getSfGuardUserId() == $this->getUser()->getApiUserId()));

        $author_type_id = $assignment->getAuthorTypeId();
        $deadline_data = Api::getInstance()->setUser($auth_key)->get('subredditdeadline?subreddit_id=' . $quick_episode->getSubredditId() . '&author_type_id=' . $author_type_id, true);
        $this->forward404Unless(array_key_exists(0, $deadline_data['body']));
        $deadline = ApiDoctrine::createQuickObject($deadline_data['body'][0]);

        $this->deadline = strtotime($quick_episode->getReleaseDate()) - $deadline->getSeconds();

        $this->is_submitted = (bool) $quick_episode->getIsSubmitted();
        $this->is_approved = (bool) $quick_episode->getIsApproved();

        $phone_data = Api::getInstance()->setUser($auth_key)->get('subreddittropo?subreddit_id=' . $episode->getSubredditId(), true);
        $this->phone_numbers = ApiDoctrine::createQuickObjectArray($phone_data['body']);


        $this->form = new EpisodeForm($episode);
        $this->form->setDefault('is_nsfw', $quick_episode->getIsNsfw());
        unset($this->form['sf_guard_user_id']);
        unset($this->form['file_is_remote']);
        unset($this->form['remote_url']);
        unset($this->form['approved_at']);
        unset($this->form['nice_filename']);

        if (!$this->form->getObject()->getApprovedAt()) {
            unset($this->form['reddit_post_url']);
        }

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

        $this->is_admin = ($permission ? true : false);
    }

    public function executeAudio(sfWebRequest $request)
    {
        $auth_key = $this->getUser()->getApiAuthKey();
        $episode_data = Api::getInstance()->setUser($auth_key)->get('episode/' . $request->getParameter('id'), true);
        $episode = ApiDoctrine::createObject('Episode', $episode_data['body']);
        $this->forward404Unless($episode && $episode->getId());
        /* @var $episode Episode */

        // If the episode's file is remote, everyone is allowed to download the file from Amazon
        if ($episode->getFileIsRemote()) {
            $this->redirect($episode->getRemoteUrl());
        }

        // If the episode is not released, only the admins and moderators can view it.
        $permission = $this->verifyPermissionsForCurrentUser($episode->getSubredditId(), array('admin', 'moderator'));

        // Unless the owner of the episode is trying to download it.  That's okay.
        $assignment_data = Api::getInstance()->setUser($auth_key)->get('episodeassignment/' . $episode->getEpisodeAssignmentId(), true);
        $assignment = ApiDoctrine::createQuickObject($assignment_data['body']);
        $this->forward404Unless($permission || $assignment->getSfGuardUserId() == $this->getUser()->getApiUserId());
        
        // Check to make sure that the local file is there; if not try to get it from the Application bucket.
        $file_location = rtrim(ProjectConfiguration::getEpisodeAudioFileLocalDirectory(), '/') . '/';
        if (!file_exists($file_location . $episode->getAudioFile())) {
            $episode->pullAudioFileFromApplicationBucket();
            $this->forward404If(!file_exists($file_location . $episode->getAudioFile()));
        }

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
        $this->redirectUnless($this->getUser()->isAuthenticated() && $this->getUser()->getApiUserId(), '@sf_guard_signin');

        $auth_key = $this->getUser()->getApiAuthKey();
        $episode_data = Api::getInstance()->setUser($auth_key)->get('episode/' . $request->getParameter('id'), true);
        $this->episode = ApiDoctrine::createQuickObject($episode_data['body']);
        $this->forward404Unless($this->episode && $this->episode->getId());
        $this->forward404Unless(strtotime($this->episode->getReleaseDate()) >= time());
        // Approval should only be an option once the episode is submitted.
        $this->forward404If($this->episode->getIsApproved() || !$this->episode->getIsSubmitted());
        // If the episode is not released, only the admins and moderators can view it.
        $permission = $this->verifyPermissionsForCurrentUser($this->episode->getSubredditId(), array('admin', 'moderator'));
        // The owner of the Episode *cannot* be an approver for the episode.
        $assignment_data = Api::getInstance()->setUser($auth_key)->get('episodeassignment/' . $this->episode->getEpisodeAssignmentId(), true);
        $assignment = ApiDoctrine::createQuickObject($assignment_data['body']);
        $this->forward404If(!$assignment || $assignment->getSfGuardUserId() == $this->getUser()->getApiUserId());

        $this->forward404Unless($permission);

        $subreddit_data = Api::getInstance()->setUser($auth_key)->get('subreddit/' . $this->episode->getSubredditId(), true);
        $this->subreddit = ApiDoctrine::createQuickObject($subreddit_data['body']);

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
        $assignment_data = Api::getInstance()->setUser($auth_key)->get('episodeassignment/' . $episode->getEpisodeAssignmentId(), true);
        $assignment = ApiDoctrine::createQuickObject($assignment_data['body']);
        $this->forward404Unless($assignment && $assignment->getSfGuardUserId() == $this->getUser()->getApiUserId());

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
        $assignment_data = Api::getInstance()->setUser($auth_key)->get('episodeassignment/' . $this->episode->getEpisodeAssignmentId(), true);
        $assignment = ApiDoctrine::createQuickObject($assignment_data['body']);
        $this->forward404If($assignment->getSfGuardUserId() == $this->getUser()->getApiUserId());

        $subreddit_data = Api::getInstance()->setUser($auth_key)->get('subreddit/' . $this->episode->getSubredditId(), true);
        $this->subreddit = ApiDoctrine::createQuickObject($subreddit_data['body']);

        $submission_change = array(
            'approved_by' => $this->getUser()->getApiUserId(),
            'is_approved' => 1,
        );
        $result = Api::getInstance()->setUser($auth_key)->put('episode/' . $this->episode->getIncremented(), $submission_change, true);
        $success = $this->checkHttpCode($result, 'put', 'episode/' . $this->episode->getIncremented(), json_encode($submission_change));
        if ($success) {
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
        $this->full_episode = ApiDoctrine::createObject('Episode', $episode_data['body']);
        $this->forward404Unless($this->episode && $this->episode->getId());
        $assignment_data = Api::getInstance()->setUser($auth_key)->get('episodeassignment/' . $this->episode->getEpisodeAssignmentId(), true);
        $this->assignment = ApiDoctrine::createQuickObject($assignment_data['body']);
        $this->forward404Unless($this->assignment && $this->assignment->getSfGuardUserId());
        if (strtotime($this->episode->getReleaseDate()) >= time()) {
            // If the episode is not released, only the admins,  moderators, and submitter can view it.
            $permission = $this->verifyPermissionsForCurrentUser($this->episode->getSubredditId(), array('admin', 'moderator'));
            $this->forward404Unless($permission || $this->assignment->getSfGuardUserId() == $this->getUser()->getApiUserId());
        } else {
            $this->forward404Unless($this->episode->getIsApproved());
        }

        $subreddit_data = Api::getInstance()->setUser($auth_key)->get('subreddit/' . $this->episode->getSubredditId(), true);
        $this->subreddit = ApiDoctrine::createQuickObject($subreddit_data['body']);
        $user_data = Api::getInstance()->setUser($auth_key)->get('user/' . $this->assignment->getSfGuardUserId(), true);
        $this->user = ApiDoctrine::createQuickObject($user_data['body']);
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
        $assignment_data = Api::getInstance()->setUser($auth_key)->get('episodeassignment/' . $quick_episode->getEpisodeAssignmentId(), true);
        $assignment = ApiDoctrine::createQuickObject($assignment_data['body']);
        $this->forward404Unless($permission || ($assignment && $assignment->getSfGuardUserId() == $this->getUser()->getApiUserId()));

        $episode->setIsNsfw($quick_episode->getIsNsfw());

        $phone_data = Api::getInstance()->setUser($auth_key)->get('subreddittropo?subreddit_id=' . $episode->getSubredditId(), true);
        $this->phone_numbers = ApiDoctrine::createQuickObjectArray($phone_data['body']);

        $this->form = new EpisodeForm($episode);
        $this->form->setDefault('is_nsfw', $quick_episode->getIsNsfw());
        unset($this->form['sf_guard_user_id']);
        unset($this->form['file_is_remote']);
        unset($this->form['remote_url']);
        unset($this->form['approved_at']);
        unset($this->form['nice_filename']);

        if (!$permission && $this->form->getObject()->getApprovedAt()) {
            unset($this->form['title'], $this->form['description']);
        }
        if (!$permission && !$this->form->getObject()->getApprovedAt()) {
            unset($this->form['reddit_post_url']);
        }

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
        $permission = $this->verifyPermissionsForCurrentUser($episode->getSubredditId(), array('admin'));
        $this->forward404Unless($permission);

        //$episode->delete();
        $result = Api::getInstance()->setUser($auth_key)->delete('episode/' . $episode->getId(), true);
        $success = $this->checkHttpCode($result, 'episode/' . $episode->getId());
        if ($success)
            $this->getUser()->setFlash('notice', 'Episode was deleted successfully.');

        $this->redirect('profile/episodes');
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
                        $form->getObject()->removeFileFromApplicationBucket($form->getObject()->getAudioFile(), 'audio');
                        unlink(sfConfig::get('sf_data_dir') . '/temp/' . $form->getObject()->getAudioFile());
                    }
                }
                if ($form->getValue('graphic_file_delete') == true) {
                    if (!$form->getObject()->getApprovedAt()) {
                        $values['graphic_file'] = null;
                        $form->getObject()->removeFileFromApplicationBucket($form->getObject()->getGraphicFile(), 'upload');
                        unlink(sfConfig::get('sf_web_dir') . '/uploads/graphics/' . $form->getObject()->getGraphicFile());
                    }
                }
                unset(
                        $values['_csrf_token'], $values['id'], $values['graphic_file_delete'], $values['audio_file_delete']
                );
                $episode = $form->getObject();
                if (!array_key_exists('is_nsfw', $values) && $episode->getIsNsfw())
                    $values['is_nsfw'] = 0;
                foreach ($values as $key => $value) {
                    if ($value == "on")
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
