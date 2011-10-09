<?php

require_once dirname(__FILE__) . '/../../../bootstrap/unit.php';

class EpisodeTest extends sfPHPUnitBaseTestCase
{

    private $subreddit;
    private $user;
    private $after_deadline_user;
    private $dark_horse_user;
    private $approver;
    private $episode;
    private $episode_filename;
    private $unapproved_file_lcoation;
    private $aws;
    private $first_application;
    private $second_application;
    private $third_application;
    private $first_deadline;
    private $second_deadline;
    private $third_deadline;
    private $first_membership;
    private $second_membership;
    private $third_membership;
    private $fourth_membership;
    private $first_ep_assignment;
    private $second_ep_assignment;
    private $third_ep_assignment;

    /**
     * We set up the following situation:
     * We have three users, each of different AuthorTypes, who have 
     * EpisodeAssignments with the Episode in question.  The deadline for the 
     * first user has passed, but the deadline for the second has not.  The 
     * third user is in an AuthorType that should prevent creating an 
     * AuthorType for the Episode before the second user's deadline passes, even
     * though that User is an admin.
     * 
     * The second user (the one who is able to submit their episode) is a
     * moderator, but she should not be able to approve her own episode.
     */
    public function setUp()
    {
        ProjectConfiguration::registerAws();
        $original_filename = '1234567890abcde.mp3';

        $this->episode_filename = 'abcde0123456789.mp3';
        $this->unapproved_file_lcoation = sfConfig::get('sf_data_dir') . '/temp/';
        if (!copy($this->unapproved_file_lcoation . $original_filename, $this->unapproved_file_lcoation . $this->episode_filename)) {
            echo "failed to copy $this->unapproved_file_lcoation$original_filename...\n";
        }
        $this->aws = new AmazonS3();

        $this->subreddit = new Subreddit();
        $this->subreddit->setName(rand(0, 1000));
        $this->subreddit->save();

        $first = AuthorTypeTable::getInstance()->findOneByType('first');
        $understudy = AuthorTypeTable::getInstance()->findOneByType('understudy');
        $dark_horse = AuthorTypeTable::getInstance()->findOneByType('dark_horse');

        $this->first_application = new Application();
        $this->first_application->setAuthorType($first);
        $this->first_application->setSubreddit($this->subreddit);
        $this->first_application->save();
        $this->second_application = new Application();
        $this->second_application->setAuthorType($understudy);
        $this->second_application->setSubreddit($this->subreddit);
        $this->second_application->save();
        $this->third_application = new Application();
        $this->third_application->setAuthorType($dark_horse);
        $this->third_application->setSubreddit($this->subreddit);
        $this->third_application->setRestrictedUntilPreviousMissesDeadline(true);
        $this->third_application->save();

        $this->first_deadline = new Deadline();
        $this->first_deadline->setAuthorType($first);
        $this->first_deadline->setSeconds(1000);
        $this->first_deadline->setSubreddit($this->subreddit);
        $this->first_deadline->save();
        $this->second_deadline = new Deadline();
        $this->second_deadline->setAuthorType($understudy);
        $this->second_deadline->setSeconds(500);
        $this->second_deadline->setSubreddit($this->subreddit);
        $this->second_deadline->save();
        $this->third_deadline = new Deadline();
        $this->third_deadline->setAuthorType($dark_horse);
        $this->third_deadline->setSeconds(100);
        $this->third_deadline->setSubreddit($this->subreddit);
        $this->third_deadline->save();

        $this->user = new sfGuardUser();
        $this->user->setEmailAddress(rand(0, 1000));
        $this->user->setUsername(rand(0, 1000));
        $this->user->save();

        $this->after_deadline_user = new sfGuardUser();
        $this->after_deadline_user->setEmailAddress(rand(0, 1000));
        $this->after_deadline_user->setUsername(rand(0, 1000));
        $this->after_deadline_user->save();

        $this->dark_horse_user = new sfGuardUser();
        $this->dark_horse_user->setEmailAddress(rand(0, 1000));
        $this->dark_horse_user->setUsername(rand(0, 1000));
        $this->dark_horse_user->save();

        $this->approver = new sfGuardUser();
        $this->approver->setEmailAddress(rand(0, 1000));
        $this->approver->setUsername(rand(0, 1000));
        $this->approver->save();

        $moderator = MembershipTable::getInstance()->findOnebyType('moderator');
        $admin = MembershipTable::getInstance()->findOnebyType('admin');
        $member = MembershipTable::getInstance()->findOnebyType('user');
        
        $this->first_membership = new sfGuardUserSubredditMembership();
        $this->first_membership->setMembership($member);
        $this->first_membership->setSubreddit($this->subreddit);
        $this->first_membership->setSfGuardUser($this->after_deadline_user);
        $this->first_membership->save();
        $this->second_membership = new sfGuardUserSubredditMembership();
        $this->second_membership->setMembership($member);
        $this->second_membership->setSubreddit($this->subreddit);
        $this->second_membership->setSfGuardUser($this->user);
        $this->second_membership->save();
        $this->third_membership = new sfGuardUserSubredditMembership();
        $this->third_membership->setMembership($admin);
        $this->third_membership->setSubreddit($this->subreddit);
        $this->third_membership->setSfGuardUser($this->dark_horse_user );
        $this->third_membership->save();
        $this->fourth_membership = new sfGuardUserSubredditMembership();
        $this->fourth_membership->setMembership($moderator);
        $this->fourth_membership->setSubreddit($this->subreddit);
        $this->fourth_membership->setSfGuardUser($this->approver);
        $this->fourth_membership->save();

        $this->episode = new Episode();
        $this->episode->setReleaseDate(date('Y-m-d H:i:s', time() + 20000));
        $this->episode->setAudioFile($this->episode_filename);
        $this->episode->setNiceFilename('14 . W Will Rock You');
        //$this->episode->setSfGuardUserId($this->user);
        $this->episode->setDescription('This is a test.');
        $this->episode->setTitle('Test Episode');
        $this->episode->setIsNsfw(false);
        $this->episode->setSubreddit($this->subreddit);
        $this->episode->save();

        $this->first_ep_assignment = new EpisodeAssignment();
        $this->first_ep_assignment->setEpisodeId($this->episode->getIncremented());
        $this->first_ep_assignment->setAuthorType($first);
        $this->first_ep_assignment->setSfGuardUser($this->after_deadline_user);
        $this->first_ep_assignment->save();
        
        $this->second_ep_assignment = new EpisodeAssignment();
        $this->second_ep_assignment->setEpisodeId($this->episode->getIncremented());
        $this->second_ep_assignment->setAuthorType($understudy);
        $this->second_ep_assignment->setSfGuardUser($this->user);
        $this->second_ep_assignment->save();
        
        $this->third_ep_assignment = new EpisodeAssignment();
        $this->third_ep_assignment->setEpisodeId($this->episode->getIncremented());
        $this->third_ep_assignment->setAuthorType($dark_horse);
        $this->third_ep_assignment->setSfGuardUser($this->dark_horse_user);
        try {
            $this->third_ep_assignment->save();
        } catch (sfException $exception) {
            unset($exception);
        }

        $this->episode->setReleaseDate(date('Y-m-d H:i:s', time() + 750));
        $this->episode->save();
    }

    /**
     * Tests for success at creating the object.
     */
    public function testCreate()
    {
        $test_title = 'test title';
        $t = new Episode();
        $t->setTitle($test_title);
        $this->assertTrue($t instanceof Episode);
        $this->assertEquals($t->__toString(), $test_title);
    }

    /**
     * Tests setting the Episode as submitted.  Since it's currently saved by a
     * user beyond deadline, this submission should error out..
     */
    public function testSubmissionOfEpsiode()
    {
        /* Now that the Episode is set up, let's ensure that some of the process
         * of submitting and approving (and unapproving and unsubmitting [you
         * can't unsubmit]) work.
         */
        
        // Try to submit without a user (and fail).
        $this->episode->setIsSubmitted(true);
        $this->episode->save();
        $this->assertFalse($this->episode->getIsSubmitted());
        
        // Set up a user beyond Deadline and try to submit (and fail).
        $this->episode->setSfGuardUserId($this->after_deadline_user->getIncremented());
        $this->episode->save();
        $this->episode->setIsSubmitted(true);
        $this->episode->save();
        $this->assertFalse($this->episode->getIsSubmitted());
        
        // Set up a user within Deadline and try to submit (and succeed).
        $this->episode->setSfGuardUserId($this->user->getIncremented());
        $this->episode->save();
        $this->episode->setIsSubmitted(true);
        $this->episode->save();
        $this->assertTrue($this->episode->getIsSubmitted());
        $this->assertTrue(strlen($this->episode->getSubmittedAt()) > 0);
        
        // Try to unsubmit the episode (and fail).
        $this->episode->setIsSubmitted(false);
        $this->episode->save();
        $this->assertTrue($this->episode->getIsSubmitted());
        $this->assertTrue(strlen($this->episode->getSubmittedAt()) > 0);
    }

    /**
     * Tests moving the episode file to Amazon when approved, and moving the
     * file back from Amazon when the Episode is unapproved (which should be
     * very rare).
     */
    public function testApprovalOfEpsiode()
    {
        /* Now that the Episode is set up, let's ensure that some of the process
         * of submitting and approving (and unapproving and unsubmitting [you
         * can't unsubmit]) work.
         */


        // Cannot save an Approver without having a User and a Submission Date.
        $this->episode->setApprovedBy($this->approver->getIncremented());
        $this->episode->save();
        $this->assertEquals(null, $this->episode->getApprovedBy());
    }

    public function tearDown()
    {

        if ($this->first_membership)
            $this->first_membership->delete();
        if ($this->second_membership)
            $this->second_membership->delete();
        if ($this->third_membership)
            $this->third_membership->delete();
        if ($this->fourth_membership)
            $this->fourth_membership->delete();
        if ($this->first_ep_assignment)
            $this->first_ep_assignment->delete();
        if ($this->second_ep_assignment)
            $this->second_ep_assignment->delete();
        if ($this->third_ep_assignment)
            $this->third_ep_assignment->delete();
        if ($this->episode)
            $this->episode->delete();
        if ($this->approver)
            $this->approver->delete();
        if ($this->dark_horse_user)
            $this->dark_horse_user->delete();
        if ($this->after_deadline_user)
            $this->after_deadline_user->delete();
        if ($this->user)
            $this->user->delete();
        if ($this->first_deadline)
            $this->first_deadline->delete();
        if ($this->second_deadline)
            $this->second_deadline->delete();
        if ($this->third_deadline)
            $this->third_deadline->delete();
        if ($this->first_application)
            $this->first_application->delete();
        if ($this->second_application)
            $this->second_application->delete();
        if ($this->third_application)
            $this->third_application->delete();
        if ($this->subreddit)
            $this->subreddit->delete();
    }

}
