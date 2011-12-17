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
        $user->setEmailAddress(rand(0, 1000));
        $user->setUsername(rand(0, 1000));
        $user->setIsValidated(1);
        $user->save();
        $subreddit = new Subreddit();
        $subreddit->setName(rand(0, 1000));
        $subreddit->setDomain(rand(0, 1000));
        $subreddit->save();
        $membership = MembershipTable::getInstance()
                ->findOneByType('user');
        $second_membership = MembershipTable::getInstance()
                ->findOneByType('admin');

        $user_subreddit_membership = new sfGuardUserSubredditMembership();
        $user_subreddit_membership->setSfGuardUserId($user->getIncremented());
        $user_subreddit_membership->setSubredditId($subreddit->getIncremented());
        $user_subreddit_membership->setMembership($membership);
        $user_subreddit_membership->save();
        
        $second_user_subreddit_membership = new sfGuardUserSubredditMembership();
        $second_user_subreddit_membership->setSfGuardUserId($user->getIncremented());
        $second_user_subreddit_membership->setSubredditId($subreddit->getIncremented());
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