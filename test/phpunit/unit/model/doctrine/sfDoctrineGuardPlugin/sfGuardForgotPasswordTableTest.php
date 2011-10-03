<?php
require_once dirname(__FILE__).'/../../../../bootstrap/unit.php';

class sfGuardForgotPasswordTableTest extends sfPHPUnitBaseTestCase
{
    public function testCreate()
    {
        $t = sfGuardForgotPasswordTable::getInstance();
        $this->assertTrue($t instanceof Doctrine_Table);
    }
}