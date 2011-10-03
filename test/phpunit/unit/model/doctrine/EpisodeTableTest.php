<?php

require_once dirname(__FILE__) . '/../../../bootstrap/unit.php';

class EpisodeTableTest extends sfPHPUnitBaseTestCase
{

    public function testCreate()
    {
        $t = EpisodeTable::getInstance();
        $this->assertTrue($t instanceof Doctrine_Table);
    }

    public function testGetOneEpisodeReleasedWithinSeconds()
    {
        // Create Test Subreddit
        $subreddit = new Subreddit();
        $subreddit->save();

        // Create Test Episode
        $episode = new Episode();
        $episode->setSubreddit($subreddit);
        $episode->save();

        $seconds_within = 15;

        // Run table query to grab episode without ID
        $random_episode = EpisodeTable::getInstance()
                ->getOneEpisodeReleasedWithinSeconds($seconds_within);
        $this->assertTrue($random_episode instanceof Episode);

        // Run table query to grab episode WITH ID
        $random_episode = EpisodeTable::getInstance()
                ->getOneEpisodeReleasedWithinSeconds($seconds_within,
                                                     $episode->getIncremented());
        $this->assertTrue($random_episode instanceof Episode);

        // Delete episode
        $episode->delete();

        // Delete subreddit.
        $subreddit->delete();
    }

}