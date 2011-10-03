<?php
require_once dirname(__FILE__).'/../../../../bootstrap/unit.php';

class sfGuardPermissionTableTest extends sfPHPUnitBaseTestCase
{
    public function testCreate()
    {
        $t = sfGuardPermissionTable::getInstance();
        $this->assertTrue($t instanceof Doctrine_Table);
    }
}