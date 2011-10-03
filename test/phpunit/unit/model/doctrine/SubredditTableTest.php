<?php

require_once dirname(__FILE__) . '/../../../bootstrap/unit.php';

class SubredditTableTest extends sfPHPUnitBaseTestCase
{

    public function testCreate()
    {
        $t = SubredditTable::getInstance();
        $this->assertTrue($t instanceof Doctrine_Table);
    }

    public function testGetSubredditsNeedingEpisodeGeneration()
    {
        // Create Test Subreddit
        $subreddit = new Subreddit();
        $subreddit->setName('test');
        $subreddit->setCreateNewEpisodesCronFormatted('0 0 1 * *');
        $subreddit->setEpisodeScheduleCronFormatted('0 0 1 * *');
        $subreddit->setCreationInterval('0');
        $subreddit->save();

        // Test against all Subreddits
        $subreddits = SubredditTable::getInstance()
                ->getSubredditsNeedingEpisodeGeneration();
        $this->assertTrue(!empty($subreddits));
        $this->assertTrue($subreddits[0] instanceof Subreddit);

        // Test against Test Subreddit
        $subreddits = SubredditTable::getInstance()
                ->getSubredditsNeedingEpisodeGeneration($subreddit->getName());
        $this->assertTrue(!empty($subreddits));
        $this->assertTrue($subreddits[0] instanceof Subreddit);
        $this->assertTrue($subreddits[0] == $subreddit, $subreddits[0]->getIncremented() . ' ' . $subreddit->getIncremented());

        $subreddit->delete();
    }

}