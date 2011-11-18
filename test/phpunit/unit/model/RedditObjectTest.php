<?php

require_once dirname(__FILE__) . '/../../bootstrap/unit.php';

class RedditObjectTest extends sfPHPUnitBaseTestCase
{

    /**
     * Tests for success at creating the object.
     */
    public function testCreate()
    {
        /*$t = new RedditObject();
        $this->assertTrue($t instanceof RedditObject);*/
        
        $reddit_location = 'http://www.reddit.com/r/atheism';
        
        $reddit = new RedditObject($reddit_location);
        $this->assertTrue($reddit instanceof RedditObject);
        $reddit->appendData();
        ValidationTable::getInstance()->storeNewKeys($reddit->getComments());
    }

}
