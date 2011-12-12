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

    protected function getSubredditId(sfWebRequest $request)
    {
        $this->forward404Unless($request->getParameter('id') || $request->getParameter('domain'));
        if ($request->getParameter('domain')) {
            $auth_key = $this->getUser()->getApiAuthKey();
            $domain = $request->getParameter('domain');
            $subreddit_data = Api::getInstance()->setUser($auth_key)->get('subreddit?domain=' . urlencode($domain),
                                                                                                          true);
            $subreddits = ApiDoctrine::createQuickObjectArray($subreddit_data['body']);
            $this->forward404Unless(count($subreddits) && $subreddits[0]->getIncremented());
            $subreddit_id = $subreddits[0]->getId();
        } else {
            $subreddit_id = $request->getParameter('id');
        }
        return $subreddit_id;
    }

    public function executeUsers(sfWebRequest $request)
    {
        $auth_key = $this->getUser()->getApiAuthKey();
        $subreddit_id = $this->getSubredditId($request);
        $members_data = Api::getInstance()->setUser($auth_key)->get('subredditmembership?subreddit_id=' . $subreddit_id,
                                                                    true);
        $this->members = ApiDoctrine::createQuickObjectArray($members_data['body']);

        $membership_data = Api::getInstance()->setUser($auth_key)->get('subredditmembership?sf_guard_user_id=' . $this->getUser()->getApiUserId() . '&subreddit_id=' . $subreddit_id,
                                                                       true);
        $membership = is_array($membership_data['body']) && array_key_exists(0,
                                                                             $membership_data['body'])
                    ? ApiDoctrine::createQuickObject($membership_data['body'][0])
                    : null;

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
        $membership_data = Api::getInstance()->setUser($auth_key)->get('subredditmembership/' . $membership_id,
                                                                       true);
        $membership_data = $membership_data['body'];
        $membership = !empty($membership_data) ? ApiDoctrine::createQuickObject($membership_data)
                    : null;
        $this->forward404Unless((int) $membership->getIncremented() != 0);
        $this->username = $membership->getsfGuardUser()->getUsername();
        $membership_object = ApiDoctrine::createObject('sfGuardUserSubredditMembership',
                                                       $membership_data);
        $this->form = new sfGuardUserSubredditMembershipForm($membership_object);
        unset($this->form['sf_guard_user_id'], $this->form['subreddit_id'],
              $this->form['display_membership']);

        $this->subreddit_id = $membership->getSubredditId();

        $my_membership_data = Api::getInstance()->setUser($auth_key)->get('subredditmembership?sf_guard_user_id=' . $this->getUser()->getApiUserId() . '&subreddit_id=' . $this->subreddit_id,
                                                                          true);
        $my_membership = is_array($my_membership_data['body']) && array_key_exists(0,
                                                                                   $my_membership_data['body'])
                    ? ApiDoctrine::createQuickObject($my_membership_data['body'][0])
                    : null;
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
        $membership_data = Api::getInstance()->setUser($auth_key)->get('subredditmembership/' . $request->getParameter('id'),
                                                                                                                       true);
        $membership = ApiDoctrine::createObject('sfGuardUserSubredditMembership',
                                                $membership_data['body']);
        $this->forward404Unless($membership && $membership->getId());
        $subreddit_id = $membership->getSubredditId();

        $my_membership_data = Api::getInstance()->setUser($auth_key)->get('subredditmembership?sf_guard_user_id=' . $this->getUser()->getApiUserId() . '&subreddit_id=' . $subreddit_id,
                                                                          true);
        $my_membership = is_array($my_membership_data['body']) && array_key_exists(0,
                                                                                   $my_membership_data['body'])
                    ? ApiDoctrine::createQuickObject($my_membership_data['body'][0])
                    : null;
        /* @todo:  The following line should be uncommented so that membership editing can only be done by subreddit leadership. */
        /* $this->forward404Unless($my_membership instanceof ApiDoctrineQuick && in_array($my_membership->getMembership()->getType(),
          array(
          'admin',
          )
         * )); */

        $this->form = new sfGuardUserSubredditMembershipForm($membership);
        unset($this->form['sf_guard_user_id'], $this->form['subreddit_id'],
              $this->form['display_membership']);

        $this->processMembershipForm($request, $this->form);

        $this->setTemplate('edit');
    }

    public function executeIndex(sfWebRequest $request)
    {
        $auth_key = $this->getUser()->getApiAuthKey();
        $page = $this->page = (int) $request->getParameter('page', 1);
        $this->forward404Unless(is_integer($page));
        $page = ($page == 1 || $page == 0) ? '' : '?page=' . $page;
        $subreddit_data = Api::getInstance()->setUser($auth_key)->get('subreddit' . $page,
                                                                      true);
        $this->subreddits = ApiDoctrine::createQuickObjectArray($subreddit_data['body']);
    }

    public function executeEpisodes(sfWebRequest $request)
    {
        $auth_key = $this->getUser()->getApiAuthKey();
        $subreddit_id = $this->getSubredditId($request);
        $episodes_data = Api::getInstance()->setUser($auth_key)->get('episode/released?subreddit_id=' . $subreddit_id,
                                                                     true);
        $this->episodes = ApiDoctrine::createQuickObjectArray($episodes_data['body']);
    }

    public function executeJoin(sfWebREquest $request)
    {
        $auth_key = $this->getUser()->getApiAuthKey();
        $user_id = $this->getUser()->getApiUserId();
        $this->forward404Unless($auth_key && $user_id);
        $subreddit_id = $this->getSubredditId($request);
        $subreddit_data = Api::getInstance()->setUser($auth_key)->get('subreddit/' . $subreddit_id,
                                                                      true);
        $subreddit = ApiDoctrine::createQuickObject($subreddit_data['body']);
        $membership_data = Api::getInstance()->setUser($auth_key)->get('subredditmembership?sf_guard_user_id=' . $user_id . '&subreddit_id=' . $subreddit_id,
                                                                       true);
        $memberships = ApiDoctrine::createQuickObjectArray($membership_data['body']);

        // If a membership already exists we can't do anything.  Go back to the Subreddit page.
        if (count($memberships)) {
            $this->getUser()->setFlash('error',
                                       "Can't create membership; one already exists!");
        } else {

            $membership_data = Api::getInstance()->setUser($auth_key)->get('membershiptype?type=pending',
                                                                           true);
            $pending_membership = ApiDoctrine::createQuickObject($membership_data['body'][0]);

            $new_membership = array(
                'sf_guard_user_id' => $user_id,
                'subreddit_id' => $subreddit_id,
                'membership_id' => $pending_membership->getIncremented(),
            );

            $create = Api::getInstance()->setUser($auth_key)->post('subredditmembership',
                                                                   $new_membership,
                                                                   false);
            $success = $this->checkHttpCode($create);
            if ($success) {
                if ($subreddit->getPreferredUsersAreFullMembers())
                    $this->getUser()->setFlash('notice', 'Joined subreddit!');
                else
                    $this->getUser()->setFlash('notice',
                                               'Subreddit membership is pending approval.  Please wait to sign up for episodes.');
            }
        }
        $this->redirect('subreddit/show?domain=' . $subreddit->getDomain());
    }

    public function executeSignup(sfWebRequest $request)
    {
        $auth_key = $this->getUser()->getApiAuthKey();

        // Get Subreddit info
        $subreddit_id = $this->getSubredditId($request);
        $subreddit_data = Api::getInstance()->setUser($auth_key)->get('subreddit/' . $subreddit_id,
                                                                      true);
        $this->subreddit = ApiDoctrine::createQuickObject($subreddit_data['body']);

        // Get unreleased Episodes
        $episodes_data = Api::getInstance()->setUser($auth_key)->get('episode/future?subreddit_id=' . $subreddit_id,
                                                                     true);
        $this->episodes = ApiDoctrine::createQuickObjectArray($episodes_data['body']);

        // Get the Deadlines for this Subreddit
        $deadline_data = Api::getInstance()->setUser($auth_key)->get('subredditdeadline?subreddit_id=' . $subreddit_id,
                                                                     true);
        $this->deadlines = ApiDoctrine::createQuickObjectArray($deadline_data['body']);

        // Get the authortypes of the Subreddit's Deadlines
        $search_authortypes = array();
        foreach ($this->deadlines as $deadline) {
            $search_authortypes[] = $deadline->getAuthorTypeId();
        }
        $authortype_data = Api::getInstance()->get('authortype?id=' . implode(',',
                                                                              $search_authortypes),
                                                                              true);
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
        $assignment_data = Api::getInstance()->get('episodeassignment?episode_id='
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

    public function executeNew(sfWebRequest $request)
    {
        $this->forward404Unless($this->getUser()->isAuthenticated());
        $this->form = new SubredditForm();
    }

    public function executeCreate(sfWebRequest $request)
    {
        $this->forward404Unless($this->getUser()->isAuthenticated());
        $this->forward404Unless($request->isMethod(sfRequest::POST));

        $this->form = new SubredditForm();

        $this->processForm($request, $this->form);

        $this->setTemplate('new');
    }

    public function executeEdit(sfWebRequest $request)
    {
        $this->forward404Unless($this->getUser()->isAuthenticated());
        $auth_key = $this->getUser()->getApiAuthKey();
        $subreddit_data = Api::getInstance()->setUser($auth_key)->get('subreddit/' . $request->getParameter('id'),
                                                                                                            true);
        $subreddit = ApiDoctrine::createObject('Subreddit',
                                               $subreddit_data['body']);
        $this->forward404Unless($subreddit && $subreddit->getId());

        $this->form = new SubredditForm($subreddit);
    }

    public function executeShow(sfWebRequest $request)
    {
        $auth_key = $this->getUser()->getApiAuthKey();
        $page = $this->page = (int) $request->getParameter('page', 1);
        $this->forward404Unless(is_integer($page));
        $page = ($page == 1 || $page == 0) ? '' : '&page=' . $page;
        $subreddit_id = $this->getSubredditId($request);
        $subreddit_data = Api::getInstance()->setUser($auth_key)->get('subreddit/' . $subreddit_id,
                                                                      true);
        $this->subreddit = ApiDoctrine::createObject('Subreddit',
                                                     $subreddit_data['body']);
        $this->forward404Unless($this->subreddit && $this->subreddit->getId());
        $episodes_data = Api::getInstance()->setUser($auth_key)->get('episode/released?subreddit_id=' . $subreddit_id . $page,
                                                                     true);
        $this->episodes = ApiDoctrine::createQuickObjectArray($episodes_data['body']);
        $membership_data = Api::getInstance()->setUser($auth_key)->get('subredditmembership?sf_guard_user_id=' . $this->getUser()->getApiUserId() . '&subreddit_id=' . $subreddit_id,
                                                                       true);
        $this->membership = (array_key_exists(0, $membership_data['body']) ? ApiDoctrine::createQuickObject($membership_data['body'][0])
                            : null);
    }

    public function executeUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($this->getUser()->isAuthenticated());
        $this->forward404Unless($request->isMethod(sfRequest::POST) || $request->isMethod(sfRequest::PUT));

        $auth_key = $this->getUser()->getApiAuthKey();
        $subreddit_data = Api::getInstance()->setUser($auth_key)->get('subreddit/' . $request->getParameter('id'),
                                                                                                            true);
        $subreddit = ApiDoctrine::createObject('Subreddit',
                                               $subreddit_data['body']);
        $this->forward404Unless($subreddit && $subreddit->getId());

        $this->form = new SubredditForm($subreddit);

        $this->processForm($request, $this->form);

        $this->setTemplate('edit');
    }

    public function executeDelete(sfWebRequest $request)
    {
        $this->forward404Unless($this->getUser()->isAuthenticated());
        $request->checkCSRFProtection();

        $auth_key = $this->getUser()->getApiAuthKey();
        $subreddit_data = Api::getInstance()->setUser($auth_key)->get('subreddit/' . $request->getParameter('id'),
                                                                                                            true);
        $subreddit = ApiDoctrine::createObject('Subreddit',
                                               $subreddit_data['body']);
        $this->forward404Unless($subreddit && $subreddit->getId());

        //$subreddit->delete();
        $result = Api::getInstance()->setUser($auth_key)->delete('subreddit/' . $subreddit->getId(),
                                                                 true);
        $success = $this->checkHttpCode($result);
        if ($success)
            $this->getUser()->setFlash('notice',
                                       'Subreddit was deleted successfully.');

        $this->redirect('subreddit/index');
    }

    protected function processForm(sfWebRequest $request, sfForm $form)
    {
        $form->bind($request->getParameter($form->getName()),
                                           $request->getFiles($form->getName()));
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
                    $result = Api::getInstance()->setUser($auth_key)->put('subreddit/' . $id,
                                                                          $values);
                    $success = $this->checkHttpCode($result);
                    if ($success)
                        $this->getUser()->setFlash('notice',
                                                   'Subreddit was edited successfully.');
                    $test_subreddit = ApiDoctrine::createObject('Subreddit',
                                                                $result['body']);
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
                $result = Api::getInstance()->setUser($auth_key)->post('subreddit',
                                                                       $values);
                $success = $this->checkHttpCode($result);
                if ($success)
                    $this->getUser()->setFlash('notice',
                                               'Episode was created successfully.');
                $test_subreddit = ApiDoctrine::createObject('Subreddit',
                                                            $result['body']);
                $subreddit = $test_subreddit ? $test_subreddit : $subreddit;
                if (is_null($subreddit->getIncremented()))
                    $this->redirect('subreddit');
            }

            $this->redirect('subreddit/edit?id=' . $subreddit->getId());
        }
    }
    
    protected function processMembershipForm(sfWebRequest $request, sfForm $form)
    {
        $form->bind($request->getParameter($form->getName()),
                                           $request->getFiles($form->getName()));
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
                    $result = Api::getInstance()->setUser($auth_key)->put('subredditmembership/' . $id,
                                                                          $values);
                    $success = $this->checkHttpCode($result);
                    if ($success)
                        $this->getUser()->setFlash('notice',
                                                   'Membership was edited successfully.');
                }
            }
            $this->redirect('subreddit/membership?id=' . $id);
        }
    }

    protected function checkHttpCode($result)
    {
        $http_code = $result['headers']['http_code'];
        if ($http_code != 200) {
            $message = array_key_exists('message', $result['body']) ? $result['body']['message']
                        : 'An error occured.';
            $message = array_key_exists(0, $result['body']) && array_key_exists('message',
                                                                                $result['body'][0])
                        ? $result['body'][0]['message'] : $message;
            $this->getUser()->setFlash('error', "($http_code) $message");
        } else {
            $this->getUser()->setFlash('notice',
                                       'Action was completed successfully.');
        }
    }
}
