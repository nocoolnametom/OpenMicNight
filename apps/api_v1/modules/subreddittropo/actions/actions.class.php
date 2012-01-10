<?php

/**
 * subreddittropo actions.
 *
 * @package    OpenMicNight
 * @subpackage subreddittropo
 * @author     Tom Doggett
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z xavier $
 * @see        class::autosubreddittropoActions
 */
class subreddittropoActions extends autosubreddittropoActions
{

    public function checkApiAuth($parameters, $content = null)
    {
        parent::checkApiAuth($parameters, $content);
        $this->getUser()->setParams($parameters);
        if (!$this->getUser()->apiIsAuthorized())
            throw new sfException('API authorization failed.', 401);
        return true;
    }

    /**
     * Returns the list of validators for an update request.
     * @return  array  an array of validators
     */
    public function getUpdateValidators()
    {
        $validators =  $this->getCreateValidators();
        $validators['subreddit_id'] = new sfValidatorDoctrineChoice(array(
            'model' => Doctrine_Core::getTable('SubredditTropoNumber')->getRelation('Subreddit')->getAlias(),
            'required' => false
            ));
        return $validators;
    }

    public function validateCreate($payload, sfWebRequest $request = null)
    {
        $params = $this->parsePayload($payload);

        $user = $this->getUser()->getGuardUser();
        if (!$user)
            throw new sfException('Action requires an auth token.', 401);

        $subreddit_id = $params['subreddit_id'];

        $admin = sfGuardUserSubredditMembershipTable::getInstance()
                ->getFirstByUserSubredditAndMemberships($user->getIncremented(),
                                                        $subreddit_id,
                                                        array(
            'admin',
                ));

        if (!$admin && !$this->getUser()->isSuperAdmin())
            throw new sfException("Your user does not have permissions to "
                    . "create new Tropo phone numbers in this Subreddit.", 403);

        parent::validateCreate($payload, $request);
    }

    public function validateDelete($payload, sfWebRequest $request = null)
    {
        parent::validateDelete($payload, $request);

        $primaryKey = $request->getParameter('id');

        $user = $this->getUser()->getGuardUser();
        if (!$user)
            throw new sfException('Action requires an auth token.', 401);

        $subreddittropo = SubredditTropoNumberTable::getInstance()->find($primaryKey);

        $subreddit_id = $subreddittropo->getSubredditId();

        $admin = sfGuardUserSubredditMembershipTable::getInstance()
                ->getFirstByUserSubredditAndMemberships($user->getIncremented(),
                                                        $subreddit_id,
                                                        array(
            'admin',
                ));

        if (!$admin && !$this->getUser()->isSuperAdmin())
            throw new sfException("Your user does not have permissions to "
                    . "delete Tropo phone numbers in this Subreddit.", 403);
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

        $subreddittropo = SubredditTropoNumber::getInstance()->find($primaryKey);

        $subreddit_id = $subreddittropo->getSubredditId();

        $admin = sfGuardUserSubredditMembershipTable::getInstance()
                ->getFirstByUserSubredditAndMemberships($user->getIncremented(),
                                                        $subreddit_id,
                                                        array(
            'admin',
                ));

        if (!$admin && !$this->getUser()->isSuperAdmin())
            throw new sfException("Your user does not have permissions to "
                    . "update Tropo phone numbers in this Subreddit.", 403);
    }
}
