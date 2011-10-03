<?php
require_once dirname(__FILE__).'/../../../../bootstrap/unit.php';

class sfGuardGroupPermissionTableTest extends sfPHPUnitBaseTestCase
{
    public function testCreate()
    {
        $t = sfGuardGroupPermissionTable::getInstance();
        $this->assertTrue($t instanceof Doctrine_Table);
    }
}