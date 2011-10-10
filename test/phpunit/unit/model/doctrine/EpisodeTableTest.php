<?php

require_once dirname(__FILE__) . '/../../../bootstrap/unit.php';

class EpisodeTableTest extends sfPHPUnitBaseTestCase
{

    /**
     * Tests for success at creating the object.
     */
    public function testCreate()
    {
        $t = EpisodeTable::getInstance();
        $this->assertTrue($t instanceof Doctrine_Table);
    }

    /**
     * Tests whether we can grab an Episode that is due to be released within a
     * specified length of time from now.
     */
    public function testGetOneEpisodeReleasedWithinSeconds()
    {
        // Create Test Subreddit
        $subreddit = new Subreddit();
        $subreddit->setName(rand(0, 1000));
        $subreddit->save();

        // Create Test Episode
        $episode = new Episode();
        $episode->setReleaseDate(date('Y-m-d H:i:s', time() + 100));
        $episode->setSubreddit($subreddit);
        $episode->save();
        
        $another_episode = new Episode();
        $another_episode->setReleaseDate(date('Y-m-d H:i:s', time() + 500));
        $another_episode->setSubreddit($subreddit);
        $another_episode->save();

        $seconds_within = 200;

        // Run table query to grab episode without ID
        $random_episode = EpisodeTable::getInstance()
                ->getOneEpisodeReleasedWithinSeconds($seconds_within);
        $this->assertTrue($random_episode instanceof Episode);

        // Run table query to grab episode WITH ID
        $random_episode = EpisodeTable::getInstance()
                ->getOneEpisodeReleasedWithinSeconds($seconds_within, $episode->getIncremented());
        $this->assertTrue($random_episode instanceof Episode);
        
        // Run table query to grab anotherepisode WITH ID
        $random_episode = EpisodeTable::getInstance()
                ->getOneEpisodeReleasedWithinSeconds($seconds_within, $another_episode->getIncremented());
        $this->assertFalse($random_episode instanceof Episode);

        // Delete episodes
        $another_episode->delete();
        $episode->delete();

        // Delete subreddit.
        $subreddit->delete();
    }

}