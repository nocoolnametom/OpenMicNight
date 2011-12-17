<?php

require_once dirname(__FILE__) . '/../../../bootstrap/unit.php';

class EpisodeAssignmentTest extends sfPHPUnitBaseTestCase
{

    /**
     * Tests for success at creating the object.
     */
    public function testCreate()
    {
        $t = new EpisodeAssignment();
        $this->assertTrue($t instanceof EpisodeAssignment);
    }

    /**
     * Blocked users should not be able to sign up for an Episode.
     */
    public function testBlockSaveForBlockedUsers()
    {
        $subreddit = new Subreddit();
        $subreddit->setName(rand(0, 10000));
        $subreddit->setDomain(rand(0, 10000));
        $subreddit->save();

        $user = new sfGuardUser();
        $user->setUsername(rand(0, 10000));
        $user->setEmailAddress(rand(0, 10000));
        $user->setIsValidated(1);
        $user->save();

        $first = AuthorTypeTable::getInstance()
                ->findOneByType('squid');
        
        $deadline = new Deadline();
        $deadline->setSubreddit($subreddit);
        $deadline->setAuthorType($first);
        $deadline->setSeconds(0);
        $deadline->save();

        $episode = new Episode();
        $episode->setReleaseDate(date('Y-m-d H:i:s', time() + 100000));
        $episode->setSubreddit($subreddit);
        $episode->save();

        // Establish conditions to trip up test.
        $blocked = MembershipTable::getInstance()
                ->findOneByType('blocked');
        $user_membership = new sfGuardUserSubredditMembership();
        $user_membership->setSfGuardUser($user);
        $user_membership->setSubreddit($subreddit);
        $user_membership->setMembership($blocked);
        $user_membership->save();

        // Try to save episode assignment.
        $assignment = new EpisodeAssignment();
        $assignment->setEpisode($episode);
        $assignment->setSfGuardUser($user);
        $assignment->setAuthorType($first);
        $exception_thrown = false;
        try {
            $assignment->save();
        } catch (sfException $exception) {
            $this->assertEquals(101, $exception->getCode());
            $exception_thrown = true;
            unset($exception);
        }

        // Remvoe test data.
        $episode->delete();
        $user->delete();
        $deadline->delete();
        $subreddit->delete();

        $this->assertTrue($exception_thrown);
    }

    /**
     * Only one sfGuardUser can sign up for one Episode with the same 
     * AuthorType for each Application period.
     */
    public function testBlockSaveWithExistingEpisodeAssignment()
    {
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
                ->findOneByType('squid');

        $episode = new Episode();
        $episode->setReleaseDate(date('Y-m-d H:i:s', time() + 100000));
        $episode->setSubreddit($subreddit);
        $episode->save();
        
        $deadline = new Deadline();
        $deadline->setSubreddit($subreddit);
        $deadline->setAuthorType($first);
        $deadline->setSeconds(0);
        $deadline->save();

        // Establish conditions to trip up test.
        $new_user = new sfGuardUser();
        $new_user->setUsername(rand(0, 10000));
        $new_user->setEmailAddress(rand(0, 10000));
        $new_user->setIsValidated(1);
        $new_user->save();
        $existing_assignment = new EpisodeAssignment();
        $existing_assignment->setEpisode($episode);
        $existing_assignment->setSfGuardUser($new_user);
        $existing_assignment->setAuthorType($first);
        $existing_assignment->save();

        // Try to save episode assignment.
        $assignment = new EpisodeAssignment();
        $assignment->setEpisode($episode);
        $assignment->setSfGuardUser($user);
        $assignment->setAuthorType($first);
        $exception_thrown = false;
        try {
            $assignment->save();
        } catch (sfException $exception) {
            $this->assertEquals(103, $exception->getCode());
            $exception_thrown = true;
            unset($exception);
        }

        // Remove test data.
        $new_user->delete();
        $existing_assignment->delete();
        if ($assignment->getIncremented())
            $assignment->delete();
        $episode->delete();
        $user->delete();
        $deadline->delete();
        $subreddit->delete();

        $this->assertTrue($exception_thrown);
    }

    /**
     * Only one sfGuardUser can sign up for one Episode with the same 
     * AuthorType for each Application period.
     */
    public function testBlockSaveForExistingAssignmentOnOtherUnreleasedEpisode()
    {
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
                ->findOneByType('squid');

        $episode = new Episode();
        $episode->setReleaseDate(date('Y-m-d H:i:s', time() + 100000));
        $episode->setSubreddit($subreddit);
        $episode->save();
        
        $deadline = new Deadline();
        $deadline->setSubreddit($subreddit);
        $deadline->setAuthorType($first);
        $deadline->setSeconds(0);
        $deadline->save();

        // Establish conditions to trip up test.
        $existing_episode = new Episode();
        $existing_episode->setReleaseDate(date('Y-m-d H:i:s', time() + 100000));
        $existing_episode->setSubreddit($subreddit);
        $existing_episode->save();
        $existing_assignment = new EpisodeAssignment();
        $existing_assignment->setEpisode($existing_episode);
        $existing_assignment->setSfGuardUser($user);
        $existing_assignment->setAuthorType($first);
        $existing_assignment->save();

        // Try to save episode assignment.
        $assignment = new EpisodeAssignment();
        $assignment->setEpisode($episode);
        $assignment->setSfGuardUser($user);
        $assignment->setAuthorType($first);
        $exception_thrown = false;
        try {
            $assignment->save();
        } catch (sfException $exception) {
            $this->assertEquals(102, $exception->getCode());
            $exception_thrown = true;
            unset($exception);
        }

        // Remove test data.
        $existing_episode->delete();
        $existing_assignment->delete();
        if ($assignment->getIncremented())
            $assignment->delete();
        $episode->delete();
        $user->delete();
        $deadline->delete();
        $subreddit->delete();

        $this->assertTrue($exception_thrown);
    }

    /**
     *  The EpisodeAssignment must be within the deadline for the AuthorType.
     */
    public function testBlockSaveForPastDeadlineForAuthorType()
    {
        $subreddit = new Subreddit();
        $subreddit->setName(rand(0, 10000));
        $subreddit->setDomain(rand(0, 10000));
        $subreddit->save();

        $user = new sfGuardUser();
        $user->setUsername(rand(0, 10000));
        $user->setEmailAddress(rand(0, 10000));
        $user->setIsValidated(1);
        $user->save();

        $first = AuthorTypeTable::getInstance()
                ->findOneByType('squid');

        $episode = new Episode();
        $episode->setReleaseDate(date('Y-m-d H:i:s', time() + 100000));
        $episode->setSubreddit($subreddit);
        $episode->save();


        // Establish conditions to trip up test.
        $deadline = new Deadline();
        $deadline->setSubreddit($subreddit);
        $deadline->setAuthorType($first);
        $deadline->setSeconds(1000000);
        $deadline->save();

        // Try to save episode assignment.
        $assignment = new EpisodeAssignment();
        $assignment->setEpisode($episode);
        $assignment->setSfGuardUser($user);
        $assignment->setAuthorType($first);
        $exception_thrown = false;
        try {
            $assignment->save();
        } catch (sfException $exception) {
            $this->assertEquals(104, $exception->getCode());
            $exception_thrown = true;
            unset($exception);
        }

        // Remvoe test data.
        $deadline->delete();
        if ($assignment->getIncremented())
            $assignment->delete();
        $episode->delete();
        $user->delete();
        $subreddit->delete();

        $this->assertTrue($exception_thrown);
    }

    /**
     * If the current AuthorTpe is flagged to not be available (see the
     * Application tests for more info), then we test whether this prevents
     * saving the EpisodeAssignment here.
     */
    public function testBlockSaveForBlockedAuthorTypeBeforePreviousDeadline()
    {
        $subreddit = new Subreddit();
        $subreddit->setName(rand(0, 10000));
        $subreddit->setDomain(rand(0, 10000));
        $subreddit->save();

        $user = new sfGuardUser();
        $user->setUsername(rand(0, 10000));
        $user->setEmailAddress(rand(0, 10000));
        $user->setIsValidated(1);
        $user->save();

        $first = AuthorTypeTable::getInstance()
                ->findOneByType('squid');
        $understudy = AuthorTypeTable::getInstance()
                ->findOneByType('shark');

        $episode = new Episode();
        $episode->setReleaseDate(date('Y-m-d H:i:s', time() + 100000));
        $episode->setSubreddit($subreddit);
        $episode->save();


        // Establish conditions to trip up test.
        // Create two Deadlines: one for first, one for understudy
        $first_deadline = new Deadline();
        $first_deadline->setSubreddit($subreddit);
        $first_deadline->setAuthorType($first);
        $first_deadline->setSeconds(1000000);
        $first_deadline->save();
        $understudy_deadline = new Deadline();
        $understudy_deadline->setSubreddit($subreddit);
        $understudy_deadline->setAuthorType($understudy);
        $understudy_deadline->setSeconds(10);
        $understudy_deadline->save();

        // Try to save episode assignment.
        $assignment = new EpisodeAssignment();
        $assignment->setEpisode($episode);
        $assignment->setSfGuardUser($user);
        $assignment->setAuthorType($understudy);
        $exception_thrown = false;
        try {
            $assignment->save();
            $this->assertFalse(true, "Why are we here?");
        } catch (sfException $exception) {
            $this->assertEquals(105, $exception->getCode());
            $exception_thrown = true;
            unset($exception);
        }

        // Remvoe test data.
        $first_deadline->delete();
        $understudy_deadline->delete();
        if ($assignment->getIncremented())
            $assignment->delete();
        $episode->delete();
        $user->delete();
        $subreddit->delete();

        $this->assertTrue($exception_thrown);
    }

    /**
     * We've combined the process for preventing saving with a specific call for
     * deletion to aid in handling errors and dealing with unsaved objects.
     * This tests whether the exception is thrown
     */
    public function testdeleteWithException()
    {
        $subreddit = new Subreddit();
        $subreddit->setName(rand(0, 10000));
        $subreddit->setDomain(rand(0, 10000));
        $subreddit->save();

        $user = new sfGuardUser();
        $user->setUsername(rand(0, 10000));
        $user->setEmailAddress(rand(0, 10000));
        $user->setIsValidated(1);
        $user->save();

        $first = AuthorTypeTable::getInstance()
                ->findOneByType('squid');

        $episode = new Episode();
        $episode->setReleaseDate(date('Y-m-d H:i:s', time() + 100000));
        $episode->setSubreddit($subreddit);
        $episode->save();
        
        $deadline = new Deadline();
        $deadline->setSubreddit($subreddit);
        $deadline->setAuthorType($first);
        $deadline->setSeconds(0);
        $deadline->save();
        
        $assignment = new EpisodeAssignment();
        $assignment->setSfGuardUser($user);
        $assignment->setEpisode($episode);
        $assignment->setAuthorType($first);
        $assignment->save();

        // Delete with exception
        $exception_thrown = false;
        $exception_code = 987654321;
        try {
            $assignment->deleteWithException("Test exception", $exception_code);
        } catch (sfException $exception) {
            $this->assertEquals($exception_code, $exception->getCode());
            unset($exception);
            $exception_thrown = true;
        }
        $this->assertTrue($exception_thrown);

        // Delete the rest of the objects
        $episode->delete();
        $user->delete();
        $subreddit->delete();
    }

}