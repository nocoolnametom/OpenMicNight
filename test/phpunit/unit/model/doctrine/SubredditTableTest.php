<?php

require_once dirname(__FILE__) . '/../../../bootstrap/unit.php';

class SubredditTableTest extends sfPHPUnitBaseTestCase
{

    public function testCreate()
    {
        $t = SubredditTable::getInstance();
        $this->assertTrue($t instanceof Doctrine_Table);
    }
    
    public function testGrabSubredditIdFromArray()
    {
        $test_id = 2;
        $an_array = array("subreddit_id" => $test_id);
        $not_an_array = $test_id;
        
        $this->assertEquals($test_id, SubredditTable::grabSubredditIdFromArray($an_array));
        $this->assertTrue(is_null(SubredditTable::grabSubredditIdFromArray($not_an_array)));
    }
    
    public function testGetSubredditsNotNeedingEpisodeGeneration()
    {
        // Create Test Subreddit
        $subreddit = new Subreddit();
        $subreddit->setName('test');
        $subreddit->setCreateNewEpisodesCronFormatted('0 0 1 * *');
        $subreddit->setEpisodeScheduleCronFormatted('0 0 1 * *');
        $subreddit->setCreationInterval('0');
        $subreddit->save();
        
        // We know that a new subreddit with no episodes will NOT be in this list
        $subreddit_not_found = true;
        $subreddits = SubredditTable::getInstance()->getSubredditsNotNeedingEpisodeGeneration();
        $this->assertTrue(is_array($subreddits));
        foreach($subreddits as $found)
        {
            if ($subreddit->getIncremented() == $found)
               $subreddit_not_found = false; 
        }
        
        $subreddit->delete();
        
        $this->assertTrue($subreddit_not_found);
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