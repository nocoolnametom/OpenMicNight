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

    /**
     * We set up the following situation:
     * We have three users, each of different AuthorTypes, who have 
     * EpisodeAssignments with the Episode in question.  The deadline for the 
     * first user has passed, but the deadline for the second has not.  The 
     * third user is in an AuthorType that should prevent creating an 
     * AuthorType for the Episode before the second user's deadline passes.
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
        $this->subreddit->setName(rand(0,1000));
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

        $this->episode = new Episode();
        $this->episode->setAudioFile($this->episode_filename);
        $this->episode->setNiceFilename('14 . W Will Rock You');
        $this->episode->setSfGuardUser($this->user);
        $this->episode->setReleaseDate(date('Y-m-d H:i:s', time() + 750));
        $this->episode->setDescription('This is a test.');
        $this->episode->setTitle('Test Episode');
        $this->episode->setIsNsfw(false);
        $this->episode->setSubreddit($this->subreddit);
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



        $this->episode->setApprovedBy($this->approver);
    }

    public function tearDown()
    {
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
