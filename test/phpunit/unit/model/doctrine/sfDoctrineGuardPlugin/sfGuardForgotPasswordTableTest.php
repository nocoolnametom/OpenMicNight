<?php

require_once dirname(__FILE__) . '/../../../../bootstrap/unit.php';

class sfGuardForgotPasswordTableTest extends sfPHPUnitBaseTestCase
{

    /**
     * Tests for success at creating the object.
     */
    public function testCreate()
    {
        $t = sfGuardForgotPasswordTable::getInstance();
        $this->assertTrue($t instanceof Doctrine_Table);
    }

}