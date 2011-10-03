<?php
require_once dirname(__FILE__).'/../../../bootstrap/unit.php';

class MessageTableTest extends sfPHPUnitBaseTestCase
{
    public function testCreate()
    {
        $t = MessageTable::getInstance();
        $this->assertTrue($t instanceof Doctrine_Table);
    }
}