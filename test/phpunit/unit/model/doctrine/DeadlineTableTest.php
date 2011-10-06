<?php

require_once dirname(__FILE__) . '/../../../bootstrap/unit.php';

class DeadlineTableTest extends sfPHPUnitBaseTestCase
{

    /**
     * Tests for success at creating the object.
     */
    public function testCreate()
    {
        $t = DeadlineTable::getInstance();
        $this->assertTrue($t instanceof Doctrine_Table);
    }

    /* @todo: Finish test */

    public function testGetSecondsByAuthorAndSubreddit()
    {
        ;
    }

    /* @todo: Finish test */

    public function testGetFirstAuthorTypeIdBySubredditWhereDeadlineIsGreaterThan()
    {
        ;
    }

}