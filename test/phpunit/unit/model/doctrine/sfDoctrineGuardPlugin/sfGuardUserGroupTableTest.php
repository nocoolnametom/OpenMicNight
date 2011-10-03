<?php
require_once dirname(__FILE__).'/../../../../bootstrap/unit.php';

class sfGuardUserGroupTableTest extends sfPHPUnitBaseTestCase
{
    public function testCreate()
    {
        $t = sfGuardUserGroupTable::getInstance();
        $this->assertTrue($t instanceof Doctrine_Table);
    }
}