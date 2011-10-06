<?php

require_once dirname(__FILE__) . '/../../../bootstrap/unit.php';

class EpisodeAssignmentTableTest extends sfPHPUnitBaseTestCase
{

    public function testCreate()
    {
        $t = EpisodeAssignmentTable::getInstance();
        $this->assertTrue($t instanceof Doctrine_Table);
    }
    
    public function testGrabIdFromArray()
    {
        $test_id = 2;
        $an_array = array("id" => $test_id);
        $not_an_array = $test_id;
        
        $this->assertEquals($test_id, EpisodeAssignmentTable::grabIdFromArray($an_array));
        $this->assertTrue(is_null(EpisodeAssignmentTable::grabIdFromArray($not_an_array)));
    }

    /* @todo: Finish test */

    public function testDeleteBySubredditIdAndUserId()
    {
        // Create two episode assignments: one for a past Episode, one for a future
        $subreddit = new Subreddit();
        $subreddit->setName(rand(0, 10000));
        $subreddit->save();

        $user = new sfGuardUser();
        $user->setUsername(rand(0, 10000));
        $user->setEmailAddress(rand(0, 10000));
        $user->save();

        $first = AuthorTypeTable::getInstance()
                ->findOneBy('type', 'first');
        $this->assertTrue($first instanceof AuthorType);
        $this->assertNotEquals(null, $first->getIncremented());

        $past_episode_one = new Episode();
        $past_episode_one->setReleaseDate(date('Y-m-d H:i:s', time() - 200000));
        $past_episode_one->setSubreddit($subreddit);
        $past_episode_one->save();
        $past_episode_two = new Episode();
        $past_episode_two->setReleaseDate(date('Y-m-d H:i:s', time() - 100000));
        $past_episode_two->setSubreddit($subreddit);
        $past_episode_two->setSfGuardUser($user);
        $past_episode_two->save();
        $future_episode = new Episode();
        $future_episode->setReleaseDate(date('Y-m-d H:i:s', time() + 100000));
        $future_episode->setSubreddit($subreddit);
        $future_episode->save();

        $past_assignment_one = new EpisodeAssignment();
        $past_assignment_one->setEpisode($past_episode_one);
        $past_assignment_one->setMissedDeadline(true);
        $past_assignment_one->setSfGuardUser($user);
        $past_assignment_one->setAuthorType($first);
        $past_assignment_one->save();
        $past_assignment_two = new EpisodeAssignment();
        $past_assignment_two->setEpisode($past_episode_two);
        $past_assignment_two->setMissedDeadline(false);
        $past_assignment_two->setSfGuardUser($user);
        $past_assignment_two->setAuthorType($first);
        $past_assignment_two->save();
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

        $test_past_one = EpisodeAssignmentTable::getInstance()
                ->find($past_assignment_one->getIncremented());
        $test_past_two = EpisodeAssignmentTable::getInstance()
                ->find($past_assignment_two->getIncremented());
        $test_future = EpisodeAssignmentTable::getInstance()
                ->find($future_assignment->getIncremented());

        $this->assertTrue($test_past_one instanceof EpisodeAssignment, 'past_one ' . $past_assignment_one->getIncremented());
        $this->assertTrue($test_past_two instanceof EpisodeAssignment, 'past_two ' . $past_assignment_two->getIncremented());
        $this->assertFalse($test_future instanceof EpisodeAssignment, 'future ' . $future_assignment->getIncremented());

        // Cleanup
        if ($future_assignment)
            $future_assignment->delete();
        $past_assignment_one->delete();
        $past_assignment_two->delete();
        $past_episode_one->delete();
        $past_episode_two->delete();
        $future_episode->delete();
        $user->delete();
        $subreddit->delete();
    }

    /* @todo: Finish test */

    public function testGetFirstByUserAuthorTypeAndSubreddit()
    {
        // Create two episode assignments: one for a past Episode, one for a future
        $subreddit = new Subreddit();
        $subreddit->setName(rand(0, 10000));
        $subreddit->save();

        $user = new sfGuardUser();
        $user->setUsername(rand(0, 10000));
        $user->setEmailAddress(rand(0, 10000));
        $user->save();

        $first = AuthorTypeTable::getInstance()
                ->findOneBy('type', 'first');
        $understudy = AuthorTypeTable::getInstance()
                ->findOneBy('type', 'understudy');
        $this->assertTrue($first instanceof AuthorType);
        $this->assertNotEquals(null, $first->getIncremented());
        $this->assertTrue($understudy instanceof AuthorType);
        $this->assertNotEquals(null, $understudy->getIncremented());

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
        $user->delete();
        $subreddit->delete();
    }

}