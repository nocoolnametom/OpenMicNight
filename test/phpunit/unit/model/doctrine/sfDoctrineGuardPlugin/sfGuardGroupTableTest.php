<?php
require_once dirname(__FILE__).'/../../../../bootstrap/unit.php';

class sfGuardGroupTableTest extends sfPHPUnitBaseTestCase
{
    public function testCreate()
    {
        $t = sfGuardGroupTable::getInstance();
        $this->assertTrue($t instanceof Doctrine_Table);
    }
}