<?php

require_once dirname(__FILE__) . '/../../../bootstrap/unit.php';

class sfGuardUserSubredditMembershipTableTest extends sfPHPUnitBaseTestCase
{

    public function testCreate()
    {
        $t = sfGuardUserSubredditMembershipTable::getInstance();
        $this->assertTrue($t instanceof Doctrine_Table);
    }

    public function testGetFirstByUserSubredditAndMemberships()
    {
        $user = new sfGuardUser();
        $subreddit = new Subreddit();
        $membership = MembershipTable::getInstance()
                ->findOneBy('type', 'user');

        $user_subreddit_membership = new sfGuardUserSubredditMembership();
        $user_subreddit_membership->setSfGuardUser($user);
        $user_subreddit_membership->setSubreddit($subreddit);
        $user_subreddit_membership->setMembership($membership);
        $user_subreddit_membership->save();

        $retrieved_object = sfGuardUserSubredditMembershipTable::getInstance()
                ->getFirstByUserSubredditAndMemberships(
                $user->getIncremented(), $subreddit->getIncremented(),
                array($membership->getType())
        );

        $this->assertEquals(
                $retrieved_object->getIncremented(),
                $user_subreddit_membership->getIncremented()
        );

        $user_subreddit_membership->delete();
        $subreddit->delete();
        $user->delete();
    }

}