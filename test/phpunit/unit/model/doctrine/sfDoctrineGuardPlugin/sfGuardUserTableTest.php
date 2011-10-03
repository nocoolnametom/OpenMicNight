<?php

require_once dirname(__FILE__) . '/../../../../bootstrap/unit.php';

class sfGuardUserTableTest extends sfPHPUnitBaseTestCase
{

    public function testCreate()
    {
        $t = sfGuardUserTable::getInstance();
        $this->assertTrue($t instanceof Doctrine_Table);
    }

}