<?php

require_once dirname(__FILE__) . '/../../../bootstrap/unit.php';

class SubredditTableTest extends sfPHPUnitBaseTestCase
{

    /**
     * Tests for success at creating the object.
     */
    public function testCreate()
    {
        $t = SubredditTable::getInstance();
        $this->assertTrue($t instanceof Doctrine_Table);
    }

    /**
     * This test is here merely to satisfy code coverage demands.  It takes a
     * sub-array from a multi-dimensional array and returns just the value.
     * This is to translate the multi-dimensional array to a one-dimensional
     * array.  This tests whether the translation is occuring correctly.
     */
    public function testGrabSubredditIdFromArray()
    {
        $test_id = 2;
        $an_array = array("subreddit_id" => $test_id);
        $not_an_array = $test_id;

        $this->assertEquals($test_id, SubredditTable::grabSubredditIdFromArray($an_array));
        $this->assertTrue(is_null(SubredditTable::grabSubredditIdFromArray($not_an_array)));
    }

    /**
     * Tests whether the 
     * SubredditTabel::getSubredditsNotNeedingEpisodeGeneration() function is
     * returning Subreddit correctly.  It's supposed to grab all Subreddits that
     * *have* Episodes with a release date after one interval of the creation
     * interval.
     */
    public function testGetSubredditsNotNeedingEpisodeGeneration()
    {
        // Create Test Subreddit
        $subreddit = new Subreddit();
        $subreddit->setName('test1');
        $subreddit->setDomain('test1');
        $subreddit->setCreateNewEpisodesCronFormatted('0 0 1 * *');
        $subreddit->setEpisodeScheduleCronFormatted('0 0 1 * *');
        $subreddit->setCreationInterval('0');
        $subreddit->save();

        // We know that a new subreddit with no episodes will NOT be in this list
        $subreddit_not_found = true;
        $subreddits = SubredditTable::getInstance()->getSubredditsNotNeedingEpisodeGeneration();
        $this->assertTrue(is_array($subreddits));
        foreach ($subreddits as $found) {
            if ($subreddit->getIncremented() == $found)
                $subreddit_not_found = false;
        }

        $subreddit->delete();

        $this->assertTrue($subreddit_not_found);
    }

    /**
     * Similar to the testGetSubredditsNotNeedingEpisodeGeneration() test above,
     * this tests whether we can grab the inevrse: all of the Subreddits that
     * *don't* have future Episodes beyond one interval of the creation
     * interval.
     */
    public function testGetSubredditsNeedingEpisodeGeneration()
    {
        // Create Test Subreddit
        $subreddit = new Subreddit();
        $subreddit->setName('test2');
        $subreddit->setDomain('test2');
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
        $this->assertTrue($subreddits[0] == $subreddit);

        $subreddit->delete();
    }

}