<?php
require_once dirname(__FILE__) . '/../../../bootstrap/unit.php';

class sfGuardUserAuthKeyTest extends sfPHPUnitBaseTestCase
{

    /**
     * Tests for success at creating the object.
     */
    public function testCreate()
    {
        $t = new sfGuardUserAuthKey();
        $this->assertTrue($t instanceof sfGuardUserAuthKey);
    }
}