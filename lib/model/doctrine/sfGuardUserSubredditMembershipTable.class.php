<?php

/**
 * sfGuardUserSubredditMembershipTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class sfGuardUserSubredditMembershipTable extends Doctrine_Table
{

    /**
     * Returns an instance of this class.
     *
     * @return object sfGuardUserSubredditMembershipTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('sfGuardUserSubredditMembership');
    }

    public function getFirstByUserSubredditAndMemberships($user_id, $subreddit_id,
                                                    $memberships = array())
    {
        $subreddit_membership = $this->createQuery()
                ->leftJoin('sfGuardUserSubredditMembership.Membership')
                ->where('sfGuardUserSubredditMembership.sf_guard_user_id = ?',
                        $user_id)
                ->andWhere('sfGuardUserSubredditMembership.subreddit_id = ?',
                           $subreddit_id)
                ->andWhereIn('Membership.type', $memberships)
                ->execute()
                ->getFirst();
        return $subreddit_membership;
    }

}