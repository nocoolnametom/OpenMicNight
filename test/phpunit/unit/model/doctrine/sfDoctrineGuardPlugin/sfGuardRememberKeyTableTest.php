<?php
require_once dirname(__FILE__).'/../../../../bootstrap/unit.php';

class sfGuardRememberKeyTableTest extends sfPHPUnitBaseTestCase
{
    public function testCreate()
    {
        $t = sfGuardRememberKeyTable::getInstance();
        $this->assertTrue($t instanceof Doctrine_Table);
    }
}