<?php

require_once dirname(__FILE__) . '/../../../bootstrap/unit.php';

class SubredditTest extends sfPHPUnitBaseTestCase
{

    /**
     * Tests for success at creating the object.
     */
    public function testCreate()
    {
        $test_name = 'test';
        $t = new Subreddit();
        $t->setName($test_name);
        $this->assertTrue($t instanceof Subreddit);
        $this->assertEquals($test_name, $t->__toString());
        $t->delete();
    }

    /**
     * Tests whether the Subreddit episode schedule is correctly transformed
     * into a CronExpression object.
     */
    public function testGetEpisodeScheduleAsCronExpression()
    {
        $subreddit = new Subreddit();
        $subreddit->setEpisodeScheduleCronFormatted('0 0 1 * *');
        $episode_schedule = $subreddit->getEpisodeScheduleAsCronExpression();

        $this->assertTrue($episode_schedule instanceof Cron\CronExpression);
    }

    /**
     * Tests whether the Subreddit creation schedule is correctly transformed
     * into a CronExpression object.
     */
    public function testGetCreationScheduleAsCronExpression()
    {
        $subreddit = new Subreddit();
        $subreddit->setCreateNewEpisodesCronFormatted('0 0 1 * *');
        $creation_schedule = $subreddit->getCreationScheduleAsCronExpression();
        $this->assertTrue($creation_schedule instanceof Cron\CronExpression);
    }

    /**
     * Tests whether the Subreddit episode interval is correctly transformed
     * into a DateInterval object.
     */
    public function testGetEpisodeItervalAsDateInterval()
    {
        $subreddit = new Subreddit();
        $subreddit->setEpisodeScheduleCronFormatted('0 0 1 * *');
        $episode_interval = $subreddit->getEpisodeItervalAsDateInterval();
        $this->assertTrue($episode_interval instanceof DateInterval);
    }

    /**
     * Tests whether the Subreddit creatiion schedule is correctly transformed
     * into a DateInterval object.
     */
    public function testGetCreationIntervalAsDateInterval()
    {
        $subreddit = new Subreddit();
        $subreddit->setCreateNewEpisodesCronFormatted('0 0 1 * *');
        $creation_internal = $subreddit->getCreationIntervalAsDateInterval();
        $this->assertTrue($creation_internal instanceof DateInterval);
    }

    /**
     * Tests whether we can generate a collection of Episodes to be saved.
     * These Episodes are produced using the creation and episode schedules of
     * the Subreddit.
     */
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