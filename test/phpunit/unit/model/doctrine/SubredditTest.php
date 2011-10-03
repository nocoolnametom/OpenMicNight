<?php

require_once dirname(__FILE__) . '/../../../bootstrap/unit.php';

class SubredditTest extends sfPHPUnitBaseTestCase
{

    public function testCreate()
    {
        $test_name = 'test';
        $t = new Subreddit();
        $t->setName($test_name);
        $this->assertTrue($t instanceof Subreddit);
        $this->assertEquals($test_name, $t->__toString());
        $t->delete();
    }

    public function testGetEpisodeScheduleAsCronExpression()
    {
        $subreddit = new Subreddit();
        $subreddit->setEpisodeScheduleCronFormatted('0 0 1 * *');
        $episode_schedule = $subreddit->getEpisodeScheduleAsCronExpression();

        $this->assertTrue($episode_schedule instanceof Cron\CronExpression);
    }

    public function testGetCreationScheduleAsCronExpression()
    {
        $subreddit = new Subreddit();
        $subreddit->setCreateNewEpisodesCronFormatted('0 0 1 * *');
        $creation_schedule = $subreddit->getCreationScheduleAsCronExpression();
        $this->assertTrue($creation_schedule instanceof Cron\CronExpression);
    }

    public function testGetEpisodeItervalAsDateInterval()
    {
        $subreddit = new Subreddit();
        $subreddit->setEpisodeScheduleCronFormatted('0 0 1 * *');
        $episode_interval = $subreddit->getEpisodeItervalAsDateInterval();
        $this->assertTrue($episode_interval instanceof DateInterval);
    }

    public function testGetCreationIntervalAsDateInterval()
    {
        $subreddit = new Subreddit();
        $subreddit->setCreateNewEpisodesCronFormatted('0 0 1 * *');
        $creation_internal = $subreddit->getCreationIntervalAsDateInterval();
        $this->assertTrue($creation_internal instanceof DateInterval);
    }

    public function testCollectGeneratedEpisodes()
    {
        /* @todo: Test iteration lengths, not just creation of episodes. */
        
        $subreddit = new Subreddit;
        $subreddit->setEpisodeScheduleCronFormatted('0 0 * * *');
        $subreddit->setCreateNewEpisodesCronFormatted('0 0 1 * *');
        $subreddit->save();
        
        $episodes = $subreddit->collectGeneratedEpisodes();
        
        $this->assertTrue(!empty($episodes));
        $this->assertTrue($episodes[0] instanceof Episode);
        
        $subreddit->delete();
        
    }
}