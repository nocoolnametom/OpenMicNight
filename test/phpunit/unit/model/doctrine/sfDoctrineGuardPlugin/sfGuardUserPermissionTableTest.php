<?php
require_once dirname(__FILE__).'/../../../../bootstrap/unit.php';

class sfGuardUserPermissionTableTest extends sfPHPUnitBaseTestCase
{
    public function testCreate()
    {
        $t = sfGuardUserPermissionTable::getInstance();
        $this->assertTrue($t instanceof Doctrine_Table);
    }
}