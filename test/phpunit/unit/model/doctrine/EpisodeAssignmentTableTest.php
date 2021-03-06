<?php

require_once dirname(__FILE__) . '/../../../bootstrap/unit.php';

class EpisodeAssignmentTableTest extends sfPHPUnitBaseTestCase
{

    /**
     * Tests for success at creating the object.
     */
    public function testCreate()
    {
        $t = EpisodeAssignmentTable::getInstance();
        $this->assertTrue($t instanceof Doctrine_Table);
    }

    /**
     * This test is here merely to satisfy code coverage demands.  It takes a
     * sub-array from a multi-dimensional array and returns just the value.
     * This is to translate the multi-dimensional array to a one-dimensional
     * array.  This tests whether the translation is occruing correctly.
     */
    public function testGrabIdFromArray()
    {
        $test_id = 2;
        $an_array = array("id" => $test_id);
        $not_an_array = $test_id;

        $this->assertEquals($test_id, EpisodeAssignmentTable::grabIdFromArray($an_array));
        $this->assertTrue(is_null(EpisodeAssignmentTable::grabIdFromArray($not_an_array)));
    }

    /**
     * This tests whether the
     * EpisodeAssignmentTable::deleteBySubredditIdAndUserId() function deletes
     * all future (and only future) EpisodeAssignments in a Subreddit attached
     * to a particular User.
     */
    public function testDeleteBySubredditIdAndUserId()
    {
        // Create two episode assignments: one for a past Episode, one for a future
        $subreddit = new Subreddit();
        $subreddit->setName(rand(0, 10000));
        $subreddit->setDomain(rand(0, 10000));
        $subreddit->save();

        $user = new sfGuardUser();
        $user->setUsername(rand(0, 10000));
        $user->setEmailAddress(rand(0, 10000));
        $user->setIsValidated(true);
        $user->save();

        $first = AuthorTypeTable::getInstance()
                ->findOneBy('type', 'squid');
        $this->assertTrue($first instanceof AuthorType);
        $this->assertNotEquals(null, $first->getIncremented());
        
        $deadline = new Deadline();
        $deadline->setSeconds(0);
        $deadline->setAuthorType($first);
        $deadline->setSubreddit($subreddit);
        $deadline->save();

        $future_episode = new Episode();
        $future_episode->setReleaseDate(date('Y-m-d H:i:s', time() + 100000));
        $future_episode->setSubreddit($subreddit);
        $future_episode->save();

        $future_assignment = new EpisodeAssignment();
        $future_assignment->setEpisode($future_episode);
        $future_assignment->setSfGuardUser($user);
        $future_assignment->setAuthorType($first);
        $future_assignment->save();

        /* Now we run the EpisodeAssignmentTable::deleteBySubredditIdAndUserId()
         * function, which should leave both past assignments intact but should
         * remove the future assignment.
         */
        EpisodeAssignmentTable::getInstance()
                ->deleteBySubredditIdAndUserId(
                        $subreddit->getIncremented(), $user->getIncremented());

        $test_future = EpisodeAssignmentTable::getInstance()
                ->find($future_assignment->getIncremented());

        $this->assertFalse($test_future instanceof EpisodeAssignment, 'future ' . $future_assignment->getIncremented());

        // Cleanup
        if ($future_assignment)
            $future_assignment->delete();
        $future_episode->delete();
        $deadline->delete();
        $user->delete();
        $subreddit->delete();
    }

    /**
     * This tests whether the
     * EpisodeAssignmentTable::getFirstByUserAuthorTypeAndSubreddit() function
     * retrieves the correct EpisodeAssignment for a particular User in a
     * Subreddit based on the AuthorType used in the EpisodeAssignment.  Users
     * can sign up for one Episode per AuthorType in each Subreddit, which means
     * future Episodes (sicne we don't want the existence of past episode to
     * disqualify Users from ever signing up again).
     */
    public function testGetFirstByUserAuthorTypeAndSubreddit()
    {
        // Create two episode assignments: one for a past Episode, one for a future
        $subreddit = new Subreddit();
        $subreddit->setName(rand(0, 10000));
        $subreddit->setDomain(rand(0, 10000));
        $subreddit->save();

        $user = new sfGuardUser();
        $user->setUsername(rand(0, 10000));
        $user->setEmailAddress(rand(0, 10000));
        $user->setisValidated(1);
        $user->save();

        $first = AuthorTypeTable::getInstance()
                ->findOneBy('type', 'squid');
        $understudy = AuthorTypeTable::getInstance()
                ->findOneBy('type', 'shark');
        $this->assertTrue($first instanceof AuthorType);
        $this->assertNotEquals(null, $first->getIncremented());
        $this->assertTrue($understudy instanceof AuthorType);
        $this->assertNotEquals(null, $understudy->getIncremented());
        
        $deadline_one = new Deadline();
        $deadline_one->setSeconds(100);
        $deadline_one->setAuthorType($first);
        $deadline_one->setSubreddit($subreddit);
        $deadline_one->save();
        
        $deadline_two = new Deadline();
        $deadline_two->setSeconds(0);
        $deadline_two->setAuthorType($understudy);
        $deadline_two->setSubreddit($subreddit);
        $deadline_two->save();

        $episode_one = new Episode();
        $episode_one->setReleaseDate(date('Y-m-d H:i:s', time() + 200000));
        $episode_one->setSubreddit($subreddit);
        $episode_one->save();
        $episode_two = new Episode();
        $episode_two->setReleaseDate(date('Y-m-d H:i:s', time() + 100000));
        $episode_two->setSubreddit($subreddit);
        $episode_two->save();
        $episode_three = new Episode();
        $episode_three->setReleaseDate(date('Y-m-d H:i:s', time() + 300000));
        $episode_three->setSubreddit($subreddit);
        $episode_three->save();

        $assignment_one = new EpisodeAssignment();
        $assignment_one->setEpisode($episode_one);
        $assignment_one->setSfGuardUser($user);
        $assignment_one->setAuthorType($first);
        $assignment_one->save();
        $assignment_two = new EpisodeAssignment();
        $assignment_two->setEpisode($episode_two);
        $assignment_two->setSfGuardUser($user);
        $assignment_two->setAuthorType($understudy);
        $assignment_two->save();
        /* There should only be one Episode Assignment for future episodes per 
         * authortype per subreddit, so saving the third assignment should fail
         * --- this is covered more in EpisodeAssignmentTest.phop
         */
        $exception_thrown = false;
        $assignment_three = new EpisodeAssignment();
        $assignment_three->setEpisode($episode_three);
        $assignment_three->setSfGuardUser($user);
        $assignment_three->setAuthorType($first);
        try {
            $assignment_three->save();
        } catch (Exception $exception) {
            $exception_thrown = true;
            $this->assertEquals(102, $exception->getCode());
            unset($exception);
        }
        $this->assertTrue($exception_thrown);

        /* Since we can trust that only one assignment per authortype per user
         * per subreddit exists for future episodes (based on the failure to
         * save $assignment_three), we check now to ensure that the
         * getFirstByUserAuthorTypeAndSubreddit() function returns a valid
         * EpisodeAssignment.
         */
        $test_one = EpisodeAssignmentTable::getInstance()
                ->getFirstByUserAuthorTypeAndSubreddit(
                $assignment_one->getAuthorTypeId(), $assignment_one->getSfGuardUserId(), $assignment_one->getEpisode()->getSubredditId());
        $test_two = EpisodeAssignmentTable::getInstance()
                ->getFirstByUserAuthorTypeAndSubreddit(
                $assignment_two->getAuthorTypeId(), $assignment_two->getSfGuardUserId(), $assignment_two->getEpisode()->getSubredditId());

        $this->assertEquals($test_one->getIncremented(), $assignment_one->getIncremented());
        $this->assertEquals($test_two->getIncremented(), $assignment_two->getIncremented());


        $assignment_one->delete();
        $assignment_two->delete();
        $episode_one->delete();
        $episode_two->delete();
        $episode_three->delete();
        $deadline_one->delete();
        $deadline_two->delete();
        $user->delete();
        $subreddit->delete();
    }

}