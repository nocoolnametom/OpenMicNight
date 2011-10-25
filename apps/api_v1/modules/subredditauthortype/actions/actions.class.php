<?php

/**
 * subredditauthortype actions.
 *
 * @package    OpenMicNight
 * @subpackage subredditauthortype
 * @author     Tom Doggett
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z xavier $
 * @see        class::autosubredditauthortypeActions
 */
class subredditauthortypeActions extends autosubredditauthortypeActions
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
        parent::validateCreate($payload, $request);
        
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
                    . "create new AuthorTypes in this Subreddit.", 403);
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
        
        if (!$admin && !$this->getUser()->isSuperAdmin())
            throw new sfException("Your user does not have permissions to "
                    . "delete AuthorTypes in this Subreddit.", 403);
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
                    . "update AuthorTypes in this Subreddit.", 403);
    }

}
