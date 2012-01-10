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

    public function preExecute()
    {
        parent::preExecute();
        if ($this->getUser()->hasAttribute('api_log')) {
            $dispatcher = sfApplicationConfiguration::getActive()
                    ->getEventDispatcher();
            $string = $this->getUser()->getAttribute('api_log');
            $dispatcher->notify(new sfEvent('Api', 'application.log', array(
                        'priority' => sfLogger::WARNING,
                        $string
                    )));
            $this->getUser()->getAttributeHolder()->remove('api_log');
        }
    }

    protected function getSubredditId(sfWebRequest $request)
    {
        $this->forward404Unless($request->getParameter('id') || $request->getParameter('domain'));
        if ($request->getParameter('domain')) {
            $auth_key = $this->getUser()->getApiAuthKey();
            $domain = $request->getParameter('domain');
            $subreddit_data = Api::getInstance()->setUser($auth_key)->get('subreddit?domain=' . urlencode($domain), true);
            $subreddits = ApiDoctrine::createQuickObjectArray($subreddit_data['body']);
            $this->forward404Unless(count($subreddits) && $subreddits[0]->getIncremented());
            $this->subreddit = ApiDoctrine::createObject('Subreddit', $subreddit_data['body'][0]);
            $this->subreddit_id = $subreddits[0]->getId();
        } else {
            $auth_key = $this->getUser()->getApiAuthKey();
            $this->subreddit_id = $request->getParameter('id');
            $subreddit_data = Api::getInstance()->setUser($auth_key)->get('subreddit/' . $this->subreddit_id, true);
            $this->subreddit = ApiDoctrine::createObject('Subreddit', $subreddit_data['body']);
            $this->forward404Unless($this->subreddit->getIncremented());
        }
        return $this->subreddit_id;
    }

    public function executeTropo(sfWebRequest $request)
    {
        $auth_key = $this->getUser()->getApiAuthKey();
        $this->getSubredditId($request);
        $this->setLayout(false);
        sfConfig::set('sf_web_debug', false);
        $this->getResponse()->setHttpHeader('Content-Type', 'application/x-httpd-php-source');
        //$this->getResponse()->setHttpHeader('Content-Type', 'text/plain');

        ProjectConfiguration::registerTropo();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://api.openmicnight/v1/api_v1_dev.php/episodeassignment/validhash?subreddit_id=2&id_hash=32");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = json_decode(curl_exec($ch), true);
        curl_close($ch);
        $is_valid = $output['is_valid'];
    }

    public function executeUsers(sfWebRequest $request)
    {
        $auth_key = $this->getUser()->getApiAuthKey();
        $this->getSubredditId($request);
        $members_data = Api::getInstance()->setUser($auth_key)->get('subredditmembership?subreddit_id=' . $this->subreddit_id, true);
        $this->members = ApiDoctrine::createQuickObjectArray($members_data['body']);

        $membership_data = Api::getInstance()->setUser($auth_key)->get('subredditmembership?sf_guard_user_id=' . $this->getUser()->getApiUserId() . '&subreddit_id=' . $this->subreddit_id, true);
        $membership = is_array($membership_data['body']) && array_key_exists(0, $membership_data['body']) ? ApiDoctrine::createQuickObject($membership_data['body'][0]) : null;

        /* @todo:  The following line should be uncommented so that membership editing can only be done by subreddit leadership. */
        /* $this->forward404Unless($membership instanceof ApiDoctrineQuick && in_array($membership->getMembership()->getType(),
          array(
          'admin',
          )
         * )); */
    }

    public function executeMembership(sfWebRequest $request)
    {
        $auth_key = $this->getUser()->getApiAuthKey();
        $membership_id = $request->getParameter('id');
        $membership_data = Api::getInstance()->setUser($auth_key)->get('subredditmembership/' . $membership_id, true);
        $membership_data = $membership_data['body'];
        $membership = !empty($membership_data) ? ApiDoctrine::createQuickObject($membership_data) : null;
        $this->forward404Unless((int) $membership->getIncremented() != 0);
        $this->username = $membership->getsfGuardUser()->getUsername();
        $membership_object = ApiDoctrine::createObject('sfGuardUserSubredditMembership', $membership_data);
        $this->form = new sfGuardUserSubredditMembershipForm($membership_object);
        unset($this->form['sf_guard_user_id'], $this->form['subreddit_id'], $this->form['display_membership']);

        $this->subreddit_id = $membership->getSubredditId();
        $subreddit_data = Api::getInstance()->setUser($auth_key)->get('subreddit/' . $this->subreddit_id, true);
        $this->subreddit = ApiDoctrine::createQuickObject($subreddit_data['body']);

        $my_membership_data = Api::getInstance()->setUser($auth_key)->get('subredditmembership?sf_guard_user_id=' . $this->getUser()->getApiUserId() . '&subreddit_id=' . $this->subreddit_id, true);
        $my_membership = is_array($my_membership_data['body']) && array_key_exists(0, $my_membership_data['body']) ? ApiDoctrine::createQuickObject($my_membership_data['body'][0]) : null;
        /* @todo:  The following line should be uncommented so that membership editing can only be done by subreddit leadership. */
        /* $this->forward404Unless($my_membership instanceof ApiDoctrineQuick && in_array($my_membership->getMembership()->getType(),
          array(
          'admin',
          )
         * )); */
    }

    public function executeUpdatemembership(sfWebRequest $request)
    {
        $this->forward404Unless($this->getUser()->isAuthenticated());
        $this->forward404Unless($request->isMethod(sfRequest::POST) || $request->isMethod(sfRequest::PUT));

        $auth_key = $this->getUser()->getApiAuthKey();
        $membership_data = Api::getInstance()->setUser($auth_key)->get('subredditmembership/' . $request->getParameter('id'), true);
        $membership = ApiDoctrine::createObject('sfGuardUserSubredditMembership', $membership_data['body']);
        $this->forward404Unless($membership && $membership->getId());

        $this->subreddit_id = $membership->getSubredditId();
        $subreddit_data = Api::getInstance()->setUser($auth_key)->get('subreddit/' . $this->subreddit_id, true);
        $this->subreddit = ApiDoctrine::createQuickObject($subreddit_data['body']);

        $my_membership_data = Api::getInstance()->setUser($auth_key)->get('subredditmembership?sf_guard_user_id=' . $this->getUser()->getApiUserId() . '&subreddit_id=' . $subreddit_id, true);
        $my_membership = is_array($my_membership_data['body']) && array_key_exists(0, $my_membership_data['body']) ? ApiDoctrine::createQuickObject($my_membership_data['body'][0]) : null;
        /* @todo:  The following line should be uncommented so that membership editing can only be done by subreddit leadership. */
        /* $this->forward404Unless($my_membership instanceof ApiDoctrineQuick && in_array($my_membership->getMembership()->getType(),
          array(
          'admin',
          )
         * )); */

        $this->form = new sfGuardUserSubredditMembershipForm($membership);
        unset($this->form['sf_guard_user_id'], $this->form['subreddit_id'], $this->form['display_membership']);

        $this->processMembershipForm($request, $this->form);

        $this->setTemplate('edit_membership');
    }

    public function executeIndex(sfWebRequest $request)
    {
        $auth_key = $this->getUser()->getApiAuthKey();
        $page = $this->page = (int) $request->getParameter('page', 1);
        $this->forward404Unless(is_integer($page));
        $page = ($page == 1 || $page == 0) ? '' : '?page=' . $page;
        $subreddit_data = Api::getInstance()->setUser($auth_key)->get('subreddit' . $page, true);
        $this->subreddits = ApiDoctrine::createQuickObjectArray($subreddit_data['body']);
    }

    public function executeEpisodes(sfWebRequest $request)
    {
        $auth_key = $this->getUser()->getApiAuthKey();
        $this->getSubredditId($request);
        $episodes_data = Api::getInstance()->setUser($auth_key)->get('episode/released?subreddit_id=' . $this->subreddit_id, true);
        $this->episodes = ApiDoctrine::createQuickObjectArray($episodes_data['body']);
    }

    public function executeJoin(sfWebREquest $request)
    {
        $auth_key = $this->getUser()->getApiAuthKey();
        $user_id = $this->getUser()->getApiUserId();
        $this->forward404Unless($auth_key && $user_id);
        $this->getSubredditId($request);
        $membership_data = Api::getInstance()->setUser($auth_key)->get('subredditmembership?sf_guard_user_id=' . $user_id . '&subreddit_id=' . $this->subreddit_id, true);
        $memberships = ApiDoctrine::createQuickObjectArray($membership_data['body']);

        // If a membership already exists we can't do anything.  Go back to the Subreddit page.
        if (count($memberships)) {
            $this->getUser()->setFlash('error', "Can't create membership; one already exists!");
        } else {

            $membership_data = Api::getInstance()->setUser($auth_key)->get('membershiptype?type=pending', true);
            $pending_membership = ApiDoctrine::createQuickObject($membership_data['body'][0]);

            $new_membership = array(
                'sf_guard_user_id' => $user_id,
                'subreddit_id' => $this->subreddit_id,
                'membership_id' => $pending_membership->getIncremented(),
            );

            $create = Api::getInstance()->setUser($auth_key)->post('subredditmembership', $new_membership, false);
            $success = $this->checkHttpCode($create, 'post', 'subredditmembership', json_encode($new_membership));
            if ($success) {
                if ($this->subreddit->getPendingUsersAreFullMembers())
                    $this->getUser()->setFlash('notice', 'Joined subreddit!');
                else
                    $this->getUser()->setFlash('notice', 'Subreddit membership is pending approval.  Please wait to sign up for episodes.');
            }
        }
        $this->redirect('subreddit/show?domain=' . $this->subreddit->getDomain());
    }

    public function executeSignup(sfWebRequest $request)
    {
        $auth_key = $this->getUser()->getApiAuthKey();

        // Get Subreddit info
        $this->getSubredditId($request);

        // Get unreleased Episodes
        $episodes_data = Api::getInstance()->setUser($auth_key)->get('episode/future?subreddit_id=' . $this->subreddit_id, true);
        $this->episodes = ApiDoctrine::createQuickObjectArray($episodes_data['body']);

        // Get the Deadlines for this Subreddit
        $deadline_data = Api::getInstance()->setUser($auth_key)->get('subredditdeadline?subreddit_id=' . $this->subreddit_id, true);
        $this->deadlines = ApiDoctrine::createQuickObjectArray($deadline_data['body']);

        // Get the authortypes of the Subreddit's Deadlines
        $search_authortypes = array();
        foreach ($this->deadlines as $deadline) {
            $search_authortypes[] = $deadline->getAuthorTypeId();
        }
        $authortype_data = Api::getInstance()->setUser($auth_key)->get('authortype?id=' . implode(',', $search_authortypes), true);
        $authortypes = ApiDoctrine::createQuickObjectArray($authortype_data['body']);
        $this->authortypes = array();
        foreach ($authortypes as $authortype) {
            $this->authortypes[$authortype->getIncremented()] = $authortype;
        }

        // Get the EpisodeAssignments of the Subreddit's unreleased Episodes
        $search_assignments = array();
        foreach ($this->episodes as $episode) {
            $search_assignments[] = $episode->getIncremented();
        }
        $assignment_data = Api::getInstance()->setUser($auth_key)->get('episodeassignment?episode_id='
                . implode(',', $search_assignments) . '&missed_deadline=0', true);
        $assignments = ApiDoctrine::createQuickObjectArray($assignment_data['body']);

        // Now we organize the EpisodeAssignments by Episode and AuthorType
        $this->assignments = array();
        foreach ($this->episodes as $episode) {
            $this->assignments[$episode->getIncremented()] = array();
        }
        $this->assigned_author_types = array();
        $this->assigned_episodes = array();
        foreach ($assignments as $assignment) {
            $this->assignments[$assignment->getEpisodeId()][$assignment->getAuthorTypeId()] = $assignment;
            if ($this->getUser()->isAuthenticated() && $assignment->getSfGuardUserId() == $this->getUser()->getApiUserId()) {
                // Here we mark which AuthorTypes and Episodes a user already is assigned for
                $this->assigned_author_types[] = $assignment->getAuthorTypeId();
                $this->assigned_episodes[] = $assignment->getEpisodeId();
            }
        }
    }

    public function executeDeadlines(sfWebRequest $request)
    {
        $auth_key = $this->getUser()->getApiAuthKey();
        $this->getSubredditId($request);

        $deadline_data = Api::getInstance()->setUser($auth_key)->get('subredditdeadline?subreddit_id=' . $this->subreddit_id, true);
        $this->deadlines = ApiDoctrine::createQuickObjectArray($deadline_data['body']);

        $this->deadline_display = array();
        foreach ($this->deadlines as $deadline) {
            $seconds = $deadline->getSeconds();
            $days = (int) ($seconds / 86400);
            $plural = $days > 1 ? 'days' : 'day';
            $hours = (int) (($seconds - ($days * 86400)) / 3600);
            $mins = (int) (($seconds - $days * 86400 - $hours * 3600) / 60);
            $secs = (int) ($seconds - ($days * 86400) - ($hours * 3600) - ($mins * 60));
            $display = ($days ? "$days $plural" : '')
                    . ($hours ? ", $hours hours" : '')
                    . ($mins ? ", $mins minutes" : '')
                    . ($secs ? ", $secs seconds" : '');
            if ($seconds == 0)
                $display = "No deadline";
            $this->deadline_display[$deadline->getIncremented()] = $display;
        }

        $membership_data = Api::getInstance()->setUser($auth_key)->get('subredditmembership?sf_guard_user_id=' . $this->getUser()->getApiUserId() . '&subreddit_id=' . $this->subreddit_id, true);
        $membership = is_array($membership_data['body']) && array_key_exists(0, $membership_data['body']) ? ApiDoctrine::createQuickObject($membership_data['body'][0]) : null;
        $this->editable = $membership && in_array($membership->getMembership()->getType(), array(
                    'admin',
                )) ? true : false;
    }

    public function executeAdd_deadline(sfWebRequest $request)
    {
        $auth_key = $this->getUser()->getApiAuthKey();
        $this->getSubredditId($request);

        // Check if the current user has permission to edit the deadline.
        $membership_data = Api::getInstance()->setUser($auth_key)->get('subredditmembership?sf_guard_user_id=' . $this->getUser()->getApiUserId() . '&subreddit_id=' . $this->subreddit_id, true);
        $membership = is_array($membership_data['body']) && array_key_exists(0, $membership_data['body']) ? ApiDoctrine::createQuickObject($membership_data['body'][0]) : null;
        // @todo: uncomment the following lines so that the deadline editing is limited.
        /* $this->forward404Unless($membership && in_array($membership->getMembership()->getType(),
          array(
          'admin',
          )));
         */

        $deadline = new Deadline();
        $deadline->setSubredditId($this->subreddit_id);

        $this->form = new DeadlineForm($deadline);
        unset($this->form['subreddit_id']);
    }

    public function executeEdit_deadline(sfWebRequest $request)
    {
        $auth_key = $this->getUser()->getApiAuthKey();
        $deadline_data = Api::getInstance()->setUser($auth_key)->get('subredditdeadline/' . $request->getParameter('id'), true);
        $deadline = ApiDoctrine::createObject('Deadline', $deadline_data['body']);
        $this->forward404Unless($deadline && $deadline->getIncremented());

        $subreddit_data = Api::getInstance()->setUser($auth_key)->get('subreddit/' . $deadline->getSubredditId(), true);
        $this->subreddit = ApiDoctrine::createQuickObject($subreddit_data['body']);

        // Check if the current user has permission to edit the deadline.
        $membership_data = Api::getInstance()->setUser($auth_key)->get('subredditmembership?sf_guard_user_id=' . $this->getUser()->getApiUserId() . '&subreddit_id=' . $this->subreddit_id, true);
        $membership = is_array($membership_data['body']) && array_key_exists(0, $membership_data['body']) ? ApiDoctrine::createQuickObject($membership_data['body'][0]) : null;
        // @todo: uncomment the following lines so that the deadline editing is limited.
        /* $this->forward404Unless($membership && in_array($membership->getMembership()->getType(),
          array(
          'admin',
          )));
         */

        $this->form = new DeadlineForm($deadline);
        unset($this->form['subreddit_id']);
    }

    public function executeDelete_deadline(sfWebRequest $request)
    {
        $auth_key = $this->getUser()->getApiAuthKey();
        $deadline_data = Api::getInstance()->setUser($auth_key)->get('subredditdeadline/' . $request->getParameter('id'), true);
        $deadline = ApiDoctrine::createObject('Deadline', $deadline_data['body']);
        $this->forward404Unless($deadline->getIncremented());

        $subreddit_data = Api::getInstance()->setUser($auth_key)->get('subreddit/' . $deadline->getSubredditId(), true);
        $this->subreddit = ApiDoctrine::createQuickObject($subreddit_data['body']);

        // Check if the current user has permission to edit the deadline.
        $membership_data = Api::getInstance()->setUser($auth_key)->get('subredditmembership?sf_guard_user_id=' . $this->getUser()->getApiUserId() . '&subreddit_id=' . $this->subreddit_id, true);
        $membership = is_array($membership_data['body']) && array_key_exists(0, $membership_data['body']) ? ApiDoctrine::createQuickObject($membership_data['body'][0]) : null;
        // @todo: uncomment the following lines so that the deadline editing is limited.
        /* $this->forward404Unless($membership && in_array($membership->getMembership()->getType(),
          array(
          'admin',
          )));
         */

        $result = Api::getInstance()->setUser($auth_key)->delete('subredditdeadline/' . $deadline->getId(), true);
        $success = $this->checkHttpCode($result, 'delete', 'subredditdeadline/' . $deadline->getId());
        if ($success)
            $this->getUser()->setFlash('notice', 'Deadline was deleted successfully.');

        $this->redirect('subreddit/deadlines?domain=' . $this->subreddit->getDomain());
    }

    public function executeUpdatedeadline(sfWebRequest $request)
    {
        $this->forward404Unless($this->getUser()->isAuthenticated());
        $this->forward404Unless($request->isMethod(sfRequest::POST) || $request->isMethod(sfRequest::PUT));

        $auth_key = $this->getUser()->getApiAuthKey();
        $deadline_data = Api::getInstance()->setUser($auth_key)->get('subredditdeadline/' . $request->getParameter('id'), true);
        $deadline = ApiDoctrine::createObject('Deadline', $deadline_data['body']);
        if (!$deadline || !$deadline->getId()) {
            $deadline = new Deadline();
            $deadline->setSubredditId($request->getParameter('subreddit_id'));
        }

        $this->subreddit_id = $deadline->getSubredditId();
        $subreddit_data = Api::getInstance()->setUser($auth_key)->get('subreddit/' . $this->subreddit_id, true);
        $this->subreddit = ApiDoctrine::createQuickObject($subreddit_data['body']);

        $my_membership_data = Api::getInstance()->setUser($auth_key)->get('subredditmembership?sf_guard_user_id=' . $this->getUser()->getApiUserId() . '&subreddit_id=' . $this->subreddit_id, true);
        $my_membership = is_array($my_membership_data['body']) && array_key_exists(0, $my_membership_data['body']) ? ApiDoctrine::createQuickObject($my_membership_data['body'][0]) : null;
        $this->forward404Unless($my_membership instanceof ApiDoctrineQuick && in_array($my_membership->getMembership()->getType(), array(
                    'admin',
                        )
                ));

        $this->form = new DeadlineForm($deadline);
        unset($this->form['subreddit_id']);

        $this->processDeadlineForm($request, $this->form);

        if ($deadline && $deadline->getIncremented())
            $this->setTemplate('edit_deadline');
        else
            $this->setTemplate('add_deadline');
    }

    public function executeCreatedeadline(sfWebRequest $request)
    {
        $this->forward404Unless($this->getUser()->isAuthenticated());
        $this->forward404Unless($request->isMethod(sfRequest::POST) || $request->isMethod(sfRequest::PUT));

        $auth_key = $this->getUser()->getApiAuthKey();
        $subreddit_data = Api::getInstance()->setUser($auth_key)->get('subreddit/' . $request->getParameter('subreddit_id'), true);
        $subreddit = ApiDoctrine::createObject('Subreddit', $subreddit_data['body']);
        $this->forward404Unless($this->subreddit && $this->subreddit->getId());

        $my_membership_data = Api::getInstance()->setUser($auth_key)->get('subredditmembership?sf_guard_user_id=' . $this->getUser()->getApiUserId() . '&subreddit_id=' . $this->subreddit_id, true);
        $my_membership = is_array($my_membership_data['body']) && array_key_exists(0, $my_membership_data['body']) ? ApiDoctrine::createQuickObject($my_membership_data['body'][0]) : null;
        /* @todo:  The following line should be uncommented so that membership editing can only be done by subreddit leadership. */
        $this->forward404Unless($my_membership instanceof ApiDoctrineQuick && in_array($my_membership->getMembership()->getType(), array(
                    'admin',
                        )
                ));

        $this->form = new DeadlineForm();
        unset($this->form['subreddit_id']);

        $this->processDeadlineForm($request, $this->form);

        $this->setTemplate('edit_deadline');
    }

    public function executeEdit(sfWebRequest $request)
    {
        $this->forward404Unless($this->getUser()->isAuthenticated());
        $auth_key = $this->getUser()->getApiAuthKey();
        $this->getSubredditId($request);

        $this->form = new SubredditForm($this->subreddit);
        if (!$this->getUser()->isSuperAdmin()) {
            unset($this->form['is_active']);
            unset($this->form['bucket_name']);
            unset($this->form['creation_interval']);
        }
    }

    public function executePhone(sfWebRequest $request)
    {
        $this->forward404Unless($this->getUser()->isAuthenticated());
        $auth_key = $this->getUser()->getApiAuthKey();
        $this->getSubredditId($request);

        $membership_data = Api::getInstance()->setUser($auth_key)->get('subredditmembership?sf_guard_user_id=' . $this->getUser()->getApiUserId() . '&subreddit_id=' . $this->subreddit_id, true);
        $this->membership = (array_key_exists(0, $membership_data['body']) ? ApiDoctrine::createQuickObject($membership_data['body'][0]) : null);
        $this->forward404If(!$this->membership || (!in_array($this->membership->getMembership()->getType(), array('admin'))) && (!$this->getUser()->isSuperAdmin()));

        $phone_data = Api::getInstance()->setUser($auth_key)->get('subreddittropo?subreddit_id=' . $this->subreddit_id, true);
        $this->phone_numbers = ApiDoctrine::createQuickObjectArray($phone_data['body']);

        $number = new SubredditTropoNumber();
        $number->setSubredditId($this->subreddit_id);
        $this->form = new SubredditTropoNumberForm($number);
        unset($this->form['subreddit_id']);

        if ($request->isMethod(sfRequest::POST)) {
            $this->processTropoPhoneForm($request, $this->form);
        }
    }

    public function executeRemovephone(sfWebRequest $request)
    {
        $auth_key = $this->getUser()->getApiAuthKey();
        $phone_data = Api::getInstance()->setUser($auth_key)->get('subreddittropo/' . $request->getParameter('id'), true);
        $phone_number = ApiDoctrine::createQuickObject($phone_data['body']);
        $this->forward404Unless($phone_number->getIncremented());

        $subreddit_data = Api::getInstance()->setUser($auth_key)->get('subreddit/' . $phone_number->getSubredditId(), true);
        $this->subreddit = ApiDoctrine::createQuickObject($subreddit_data['body']);

        // Check if the current user has permission to edit the deadline.
        $membership_data = Api::getInstance()->setUser($auth_key)->get('subredditmembership?sf_guard_user_id=' . $this->getUser()->getApiUserId() . '&subreddit_id=' . $this->subreddit_id, true);
        $membership = is_array($membership_data['body']) && array_key_exists(0, $membership_data['body']) ? ApiDoctrine::createQuickObject($membership_data['body'][0]) : null;
        // @todo: uncomment the following lines so that the deadline editing is limited.
        $this->forward404Unless($membership && in_array($membership->getMembership()->getType(), array(
                    'admin',
                )));

        $result = Api::getInstance()->setUser($auth_key)->delete('subreddittropo/' . $phone_number->getId(), true);
        $success = $this->checkHttpCode($result, 'delete', 'subreddittropo/' . $phone_number->getId());
        if ($success)
            $this->getUser()->setFlash('notice', 'Phone number was removed successfully.');

        $this->redirect('subreddit/phone?domain=' . $this->subreddit->getDomain());
    }

    public function executeShow(sfWebRequest $request)
    {
        $auth_key = $this->getUser()->getApiAuthKey();
        $page = $this->page = (int) $request->getParameter('page', 1);
        $this->forward404Unless(is_integer($page));
        $page = ($page == 1 || $page == 0) ? '' : '&page=' . $page;
        $this->getSubredditId($request);
        $episodes_data = Api::getInstance()->setUser($auth_key)->get('episode/released?subreddit_id=' . $this->subreddit_id . $page, true);
        $this->episodes = ApiDoctrine::createQuickObjectArray($episodes_data['body']);
        $membership_data = Api::getInstance()->setUser($auth_key)->get('subredditmembership?sf_guard_user_id=' . $this->getUser()->getApiUserId() . '&subreddit_id=' . $this->subreddit_id, true);
        $this->membership = (array_key_exists(0, $membership_data['body']) ? ApiDoctrine::createQuickObject($membership_data['body'][0]) : null);
    }

    public function executeUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($this->getUser()->isAuthenticated());
        $this->forward404Unless($request->isMethod(sfRequest::POST) || $request->isMethod(sfRequest::PUT));

        $auth_key = $this->getUser()->getApiAuthKey();

        $this->getSubredditId($request);

        $this->form = new SubredditForm($this->subreddit);

        if (!$this->getUser()->isSuperAdmin()) {
            unset($this->form['is_active']);
            unset($this->form['bucket_name']);
            unset($this->form['creation_interval']);
        }

        $this->processForm($request, $this->form);

        $this->setTemplate('edit');
    }

    public function executeDelete(sfWebRequest $request)
    {
        $this->forward404Unless($this->getUser()->isAuthenticated());
        $request->checkCSRFProtection();

        $auth_key = $this->getUser()->getApiAuthKey();

        $this->getSubredditId($request);

        //$subreddit->delete();
        $result = Api::getInstance()->setUser($auth_key)->delete('subreddit/' . $this->subreddit->getId(), true);
        $success = $this->checkHttpCode($result, 'delete', 'subreddit/' . $this->subreddit->getId());
        if ($success)
            $this->getUser()->setFlash('notice', 'Subreddit was deleted successfully.');

        $this->redirect('subreddit/index');
    }

    protected function processForm(sfWebRequest $request, sfForm $form)
    {
        $form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));
        if ($form->isValid()) {
            $auth_key = $this->getUser()->getApiAuthKey();
            if ($form->getValue('id')) {
                // Update existing item.
                $values = $form->getValues();
                $id = $form->getValue('id');
                $subreddit = $form->getObject();
                $subreddit_array = $subreddit->toArray();
                foreach ($subreddit_array as $key => $value)
                    if (array_key_exists($key, $values) && ($values[$key] == $subreddit_array[$key]))
                        unset($values[$key]);
                if (array_key_exists('is_active', $values))
                    $values['is_active'] = (bool) $values['is_active'] ? 1 : 0;
                if (count($values)) {
                    $result = Api::getInstance()->setUser($auth_key)->put('subreddit/' . $id, $values);
                    $success = $this->checkHttpCode($result, 'put', 'subreddit/' . $id, json_encode($values));
                    if ($success)
                        $this->getUser()->setFlash('notice', 'Subreddit was edited successfully.');
                    $test_subreddit = ApiDoctrine::createObject('Subreddit', $result['body']);
                    $subreddit = $test_subreddit ? $test_subreddit : $subreddit;
                }
            } else {
                // Create new item
                $values = $form->getValues();
                $subreddit = $form->getObject();
                foreach ($values as $key => $value) {
                    if (is_null($value))
                        unset($values[$key]);
                }
                $result = Api::getInstance()->setUser($auth_key)->post('subreddit', $values);
                $success = $this->checkHttpCode($result, 'post', 'subreddit', json_encode($values));
                if ($success)
                    $this->getUser()->setFlash('notice', 'Episode was created successfully.');
                $test_subreddit = ApiDoctrine::createObject('Subreddit', $result['body']);
                $subreddit = $test_subreddit ? $test_subreddit : $subreddit;
                if (is_null($subreddit->getIncremented()))
                    $this->redirect('subreddit');
            }

            $this->redirect('subreddit/edit?id=' . $subreddit->getId());
        }
    }

    protected function processDeadlineForm(sfWebRequest $request, sfForm $form)
    {
        $form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));
        if ($form->isValid()) {
            $auth_key = $this->getUser()->getApiAuthKey();
            if ($form->getValue('id')) {
                // Update existing item.
                $values = $form->getValues();
                $id = $form->getValue('id');
                $deadline = $form->getObject();
                $deadline_array = $deadline->toArray();
                foreach ($deadline_array as $key => $value)
                    if (array_key_exists($key, $values) && ($values[$key] == $deadline_array[$key]))
                        unset($values[$key]);
                if (array_key_exists('restricted_until_previous_misses_deadline', $values))
                    $values['restricted_until_previous_misses_deadline'] = (bool) $values['restricted_until_previous_misses_deadline'] ? 1 : 0;
                if (count($values)) {
                    $result = Api::getInstance()->setUser($auth_key)->put('subredditdeadline/' . $id, $values);
                    $success = $this->checkHttpCode($result, 'put', 'subredditdeadline/' . $id, json_encode($values));
                    if ($success)
                        $this->getUser()->setFlash('notice', 'Deadline was edited successfully.');
                }
            } else {
                // Create new item
                $values = $form->getValues();
                $deadline = $form->getObject();
                $subreddit_id = $request->getParameter('subreddit_id');
                foreach ($values as $key => $value) {
                    if (is_null($value))
                        unset($values[$key]);
                }
                $values['subreddit_id'] = $subreddit_id;
                $result = Api::getInstance()->setUser($auth_key)->post('subredditdeadline', $values);
                $success = $this->checkHttpCode($result, 'post', 'subredditdeadline', json_encode($values));
                if ($success) {
                    $this->getUser()->setFlash('notice', 'Deadline was created successfully.');
                }
                $deadline = ApiDoctrine::createQuickObject(
                                $result['body']);
                if (!$deadline || !$deadline->getIncremented())
                    $this->redirect('subreddit/deadlines?id=' . $subreddit_id);
            }



            $this->redirect('subreddit/edit_deadline?id=' . $id);
        }
    }

    protected function processTropoPhoneForm(sfWebRequest $request, sfForm $form)
    {
        $form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));
        if ($form->isValid()) {
            $auth_key = $this->getUser()->getApiAuthKey();
            // Create new item
            $values = $form->getValues();
            $deadline = $form->getObject();
            foreach ($values as $key => $value) {
                if (is_null($value))
                    unset($values[$key]);
            }
            $values['subreddit_id'] = $this->subreddit_id;
            $result = Api::getInstance()->setUser($auth_key)->post('subreddittropo', $values);
            $success = $this->checkHttpCode($result, 'post', 'subreddittropo', json_encode($values));
            if ($success) {
                $this->getUser()->setFlash('notice', 'Phone number was added successfully.');
            }
            $this->redirect('subreddit/phone?domain=' . $this->subreddit->getDomain());
        }
    }

    protected function processMembershipForm(sfWebRequest $request, sfForm $form)
    {
        $form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));
        if ($form->isValid()) {
            $auth_key = $this->getUser()->getApiAuthKey();
            if ($form->getValue('id')) {
                // Update existing item.
                $values = $form->getValues();
                $id = $form->getValue('id');
                $membership = $form->getObject();
                $membership_array = $membership->toArray();
                foreach ($membership_array as $key => $value)
                    if (array_key_exists($key, $values) && ($values[$key] == $membership_array[$key]))
                        unset($values[$key]);
                if (array_key_exists('display_membership', $values))
                    $values['display_membership'] = (bool) $values['display_membership'] ? 1 : 0;
                if (count($values)) {
                    $result = Api::getInstance()->setUser($auth_key)->put('subredditmembership/' . $id, $values);
                    $success = $this->checkHttpCode($result, 'put', 'subredditmembership/' . $id, json_encode($values));
                    if ($success)
                        $this->getUser()->setFlash('notice', 'Membership was edited successfully.');
                }
            }
            $this->redirect('subreddit/membership?id=' . $id);
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
