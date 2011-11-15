<?php

require_once dirname(__FILE__) . '/../../bootstrap/unit.php';

class RedditObjectTest extends sfPHPUnitBaseTestCase
{

    /**
     * Tests for success at creating the object.
     */
    public function testCreate()
    {
        $t = new RedditObject();
        $this->assertTrue($t instanceof RedditObject);
        
        $reddit_location = 'kdxhb';
        
        $json_file = 'http://www.reddit.com/comments/' . $reddit_location . '.json';
        $reddit_json = file_get_contents($json_file);
        
        $reddit = new RedditObject($reddit_json);
        $reddit->setLocation('http://www.reddit.com/comments/t3_' . $reddit_location . '/');
        $this->assertTrue($reddit instanceof RedditObject);
        $reddit->setComments();
        ValidationTable::getInstance()->storeNewKeys($reddit->getComments());
    }

}
