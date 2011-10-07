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

    /**
     * Returns the first sfGuardUserSubredditMembership identified by a given,
     * User, Membership type, and Subreddit.  Since there should only be one 
     * entry in all sfGuardUserSubredditMembership for these threee identifiying
     * factors, this function should return the only
     * sfGuardUserSubredditMembership possible.
     * 
     * Returns null on not finding an sfGuardUserSubredditMembership.
     * 
     * @param int   $user_id      The incremented ID of an sfGuardUser object
     * @param int   $subreddit_id The incremented ID of a Subreddit object
     * @param array $memberships  An array of Membership types (as strings)
     * @return sfGuardUserSubredditMembership 
     */
    public function getFirstByUserSubredditAndMemberships($user_id, $subreddit_id,
                                                    $memberships = array())
    {
        $subreddit_membership = $this->createQuery()
                ->leftJoin('sfGuardUserSubredditMembership.Membership Membership')
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