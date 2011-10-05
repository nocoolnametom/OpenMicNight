<?php
require_once dirname(__FILE__).'/../../../bootstrap/unit.php';

class EpisodeAssignmentTableTest extends sfPHPUnitBaseTestCase
{
    public function testCreate()
    {
        $t = EpisodeAssignmentTable::getInstance();
        $this->assertTrue($t instanceof Doctrine_Table);
    }
    
    /* @todo: Finish test */
    public function testDeleteBySubredditIdAndUserId()
    {
        ;
    }
    
    /* @todo: Finish test */
    public function testGetFirstByUserAuthorTypeAndSubreddit()
    {
        ;
    }
}