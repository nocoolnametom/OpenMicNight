<?php

/**
 * episode actions.
 *
 * @package    OpenMicNight
 * @subpackage episode
 * @author     Tom Doggett
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z xavier $
 * @see        class::autoepisodeActions
 */
class episodeActions extends autoepisodeActions
{

    public function checkApiAuth($parameters, $content = null)
    {
        parent::checkApiAuth($parameters, $content);
        $this->getUser()->setParams($parameters);
        if (!$this->getUser()->apiIsAuthorized())
            throw new sfException('API authorization failed.', 401);
        return true;
    }

    public function getUpdateValidators()
    {
        $validators = parent::getUpdateValidators();
        $validators['subreddit_id'] = new sfValidatorDoctrineChoice(array(
                    'model' => Doctrine_Core::getTable('Episode')
                            ->getRelation('Subreddit')
                            ->getAlias(),
                    'required' => false,
                ));
        $validators['release_date'] = new sfValidatorDateTime(array(
                    'required' => false,
                ));
        return $validators;
    }

    public function validateUpdate($payload, sfWebRequest $request = null)
    {
        parent::validateUpdate($payload, $request);

        $params = $this->parsePayload($payload);

        $user = $this->getUser()->getGuardUser();
        if (!$user)
            throw new sfException('Action requires an auth token.', 401);

        $primaryKey = $request->getParameter('id');
        $episode = EpisodeTable::getInstance()->find($primaryKey);

        if (!$this->getUser()->isSuperAdmin()) {
            $admin = sfGuardUserSubredditMembershipTable::getInstance()
                    ->getFirstByUserSubredditAndMemberships($user->getIncremented(),
                                                            $episode->getSubredditId(),
                                                            array('admin'));
            $moderator = sfGuardUserSubredditMembershipTable::getInstance()
                    ->getFirstByUserSubredditAndMemberships($user->getIncremented(),
                                                            $episode->getSubredditId(),
                                                            array('moderator'));
            if (!$admin) {
                if (array_key_exists('sf_guard_user_id', $params)
                        && $params['sf_guard_user_id'] != $user->getIncremented())
                    throw new sfException('You are not allowed to change the User of the Episode!', 403);
                if (array_key_exists('approved_by', $params)
                        && !$moderator
                        && $params['approved_by'] != $user->getIncremented())
                    throw new sfException('You are not allowed to add approval for the Episode!', 403);
            }
        }
    }

    public function validateDelete($payload, sfWebRequest $request = null)
    {
        parent::validateDelete($payload, $request);
        
        $params = $this->parsePayload($payload);

        $user = $this->getUser()->getGuardUser();
        if (!$user)
            throw new sfException('Action requires an auth token.', 401);

        $primaryKey = $request->getParameter('id');
        $episode = EpisodeTable::getInstance()->find($primaryKey);

        if (!$this->getUser()->isSuperAdmin()) {
            throw new sfException('You are not allowed to delete Episodes!', 403);
        }
    }
}
