<?php

require_once dirname(__FILE__) . '/../../../bootstrap/unit.php';

class sfGuardUserSubredditMembershipTableTest extends sfPHPUnitBaseTestCase
{

    /**
     * Tests for success at creating the object.
     */
    public function testCreate()
    {
        $t = sfGuardUserSubredditMembershipTable::getInstance();
        $this->assertTrue($t instanceof Doctrine_Table);
    }

    /**
     * Since a User can only have one membership in a Subreddit, this tests that
     * the first returned sfGuardUserSubredditMembership is the exact same as
     * the only one made.  The limitation on sfGuardUserSubredditMemberships is
     * in place using Unique indexes in the database, so we depend upon that to
     * prevent multiple Subreddit Memberships.
     */
    public function testGetFirstByUserSubredditAndMemberships()
    {
        $user = new sfGuardUser();
        $subreddit = new Subreddit();
        $membership = MembershipTable::getInstance()
                ->findOneBy('type', 'user');
        $second_membership = MembershipTable::getInstance()
                ->findOneBy('type', 'admin');

        $user_subreddit_membership = new sfGuardUserSubredditMembership();
        $user_subreddit_membership->setSfGuardUser($user);
        $user_subreddit_membership->setSubreddit($subreddit);
        $user_subreddit_membership->setMembership($membership);
        $user_subreddit_membership->save();
        
        $second_user_subreddit_membership = new sfGuardUserSubredditMembership();
        $second_user_subreddit_membership->setSfGuardUser($user);
        $second_user_subreddit_membership->setSubreddit($subreddit);
        $second_user_subreddit_membership->setMembership($second_membership);
        $exception_thrown = false;
        try {
            $second_user_subreddit_membership->save();
        } catch (Exception $exception)
        {
            unset($exception);
            $exception_thrown = true;
        }

        $retrieved_object = sfGuardUserSubredditMembershipTable::getInstance()
                ->getFirstByUserSubredditAndMemberships(
                $user->getIncremented(), $subreddit->getIncremented(), array($membership->getType())
        );

        $this->assertEquals(
                $retrieved_object->getIncremented(), $user_subreddit_membership->getIncremented()
        );

        $user_subreddit_membership->delete();
        $subreddit->delete();
        $user->delete();
        
        $this->assertTrue($exception_thrown);
    }

}