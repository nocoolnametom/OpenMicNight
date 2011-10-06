<?php

require_once dirname(__FILE__) . '/../../../bootstrap/unit.php';

class sfGuardUserSubredditMembershipTest extends sfPHPUnitBaseTestCase
{

    /**
     * Tests for success at creating the object.
     */
    public function testCreate()
    {
        $t = new sfGuardUserSubredditMembership();
        $this->assertTrue($t instanceof sfGuardUserSubredditMembership);
    }

    /**
     * Tests saving a sfGuardUserSubredditMembership that identifies a User as
     * "blocked" for a particular Subreddit.  When this occurs, all existing
     * future EpisodeAssignments should be deleted because blocked users cannot
     * participate with the Subreddit.
     */
    public function testSavingBlockedUser()
    {
        // Establish fake Subreddit
        $subreddit = new Subreddit();
        $subreddit->save();

        // Establish User
        $user = new sfGuardUser();
        $user->setEmailAddress(rand(0, 100000));
        $user->setUsername(rand(0, 10000));
        $user->save();
        $user_id = $user->getIncremented();
        $this->assertNotEquals(0, $user_id);

        // Establish Episode for Subreddit
        $episode = new Episode();
        $episode->setSubreddit($subreddit);
        $episode->setReleaseDate(date('Y-m-d H:i:s', time() + 34000));
        $episode->save();

        $author_type = AuthorTypeTable::getInstance()
                ->findOneBy('type', 'first');

        $episode_assignment = new EpisodeAssignment();
        $episode_assignment->setSfGuardUser($user);
        $episode_assignment->setEpisode($episode);
        $episode_assignment->setAuthorType($author_type);
        $episode_assignment->save();

        $number_of_episodes = EpisodeAssignmentTable::getInstance()->createQuery()
                ->select('COUNT(EpisodeAssignment.id)')
                ->leftJoin('Episode')
                ->where('subreddit_id = ?', $subreddit->getIncremented())
                ->andWhere('EpisodeAssignment.sf_guard_user_id = ?', $user_id)
                ->andWhere('Episode.release_date > NOW()')
                ->groupBy('EpisodeAssignment.id')
                ->count();
        $this->assertEquals(1, $number_of_episodes);

        // Establish User Membership as Blocked
        $blocked = MembershipTable::getInstance()
                ->findOneBy('type', 'blocked');
        $user_membership = new sfGuardUserSubredditMembership();
        $user_membership->setSubreddit($subreddit);
        $user_membership->setSfGuardUser($user);
        $user_membership->setMembership($blocked);

        // Save Membership
        $user_membership->save();

        // Asert that User has zero Episodes
        $number_of_episodes = EpisodeAssignmentTable::getInstance()->createQuery()
                ->select('COUNT(EpisodeAssignment.id)')
                ->leftJoin('Episode')
                ->where('subreddit_id = ?', $subreddit->getIncremented())
                ->andWhere('EpisodeAssignment.sf_guard_user_id = ?', $user_id)
                ->andWhere('Episode.release_date > NOW()')
                ->groupBy('EpisodeAssignment.id');
        $sql = $number_of_episodes->getSqlQuery();
        $number_of_episodes = $number_of_episodes->count();
        $this->assertTrue(0 == $number_of_episodes, $sql . "\n"
                . $subreddit->getIncremented() . "\n"
                . $user_id);

        // Remove User Membership
        $user_membership->delete();

        // Delete User
        $user->delete();

        // Delete Episode
        $episode->delete();

        // Delete Subreddit
        $subreddit->delete();
    }

}