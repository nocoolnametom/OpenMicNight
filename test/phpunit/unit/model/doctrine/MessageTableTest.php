<?php

require_once dirname(__FILE__) . '/../../../bootstrap/unit.php';

class MessageTableTest extends sfPHPUnitBaseTestCase
{

    /**
     * Tests for success at creating the object.
     */
    public function testCreate()
    {
        $t = MessageTable::getInstance();
        $this->assertTrue($t instanceof Doctrine_Table);
    }

}