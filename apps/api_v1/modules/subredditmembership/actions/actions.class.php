<?php

/**
 * subredditmembership actions.
 *
 * @package    OpenMicNight
 * @subpackage subredditmembership
 * @author     Tom Doggett
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z xavier $
 * @see        class::autosubredditmembershipActions
 */
class subredditmembershipActions extends autosubredditmembershipActions
{

    public function checkApiAuth($parameters, $content = null)
    {
        parent::checkApiAuth($parameters, $content);
        $this->getUser()->setParams($parameters);
        if (!$this->getUser()->apiIsAuthorized())
            throw new sfException('API authorization failed.', 401);
        return true;
    }

    public function validateCreate($payload, sfWebRequest $request = null)
    {
        $params = $this->parsePayload($payload);

        $user = $this->getUser()->getGuardUser();
        if (!$user)
            throw new sfException('Action requires an auth token.', 401);

        $subreddit_id = $params['subreddit_id'];

        $pending = MembershipTable::getInstance()->findOneByType('pending');

        $admin = sfGuardUserSubredditMembershipTable::getInstance()
                ->getFirstByUserSubredditAndMemberships($user->getIncremented(),
                                                        $subreddit_id,
                                                        array(
            'admin',
                ));

        // All non-admin users create a pending membership in a Subreddit.
        if (($params['sf_guard_user_id'] == $user->getIncremented())
                && !$admin
                && !$this->getUser()->isSuperAdmin()) {
            $params['membership_id'] = $pending->getIncremented();
        }

        if (!$admin && !$this->getUser()->isSuperAdmin())
            throw new sfException("Your user does not have permissions to "
                    . "create new user memberships in this Subreddit.", 403);

        parent::validateCreate($payload, $request);
    }

    public function validateDelete($payload, sfWebRequest $request = null)
    {
        parent::validateDelete($payload, $request);

        $params = $this->parsePayload($payload);

        $user = $this->getUser()->getGuardUser();
        if (!$user)
            throw new sfException('Action requires an auth token.', 401);

        $subredditmembership = sfGuardUserSubredditMembershipTable::getInstance()->find($primaryKey);

        $subreddit_id = $subredditmembership->getSubredditId();

        $admin = sfGuardUserSubredditMembershipTable::getInstance()
                ->getFirstByUserSubredditAndMemberships($user->getIncremented(),
                                                        $subreddit_id,
                                                        array(
            'admin',
                ));

        if (!$admin
                && !$this->getUser()->isSuperAdmin()
                && $params['sf_guard_user_id'] != $user->getIncremented())
            throw new sfException("Your user does not have permissions to "
                    . "delete user memberships in this Subreddit.", 403);
    }

    public function validateUpdate($payload, sfWebRequest $request = null)
    {
        parent::validateUpdate($payload, $request);

        $params = $this->parsePayload($payload);

        $primaryKey = $request->getParameter('id');

        $params = $this->parsePayload($payload);

        $user = $this->getUser()->getGuardUser();
        if (!$user)
            throw new sfException('Action requires an auth token.', 401);

        $subredditmembership = sfGuardUserSubredditMembershipTable::getInstance()->find($primaryKey);

        $subreddit_id = $subredditmembership->getSubredditId();

        $admin = sfGuardUserSubredditMembershipTable::getInstance()
                ->getFirstByUserSubredditAndMemberships($user->getIncremented(),
                                                        $subreddit_id,
                                                        array(
            'admin',
                ));

        if (!$admin && !$this->getUser()->isSuperAdmin())
            throw new sfException("Your user does not have permissions to "
                    . "update user memberships in this Subreddit.", 403);
    }

}
