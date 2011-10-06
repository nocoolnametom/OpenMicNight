<?php

require_once dirname(__FILE__) . '/../../../../bootstrap/unit.php';

class sfGuardUserPermissionTest extends sfPHPUnitBaseTestCase
{

    /**
     * Tests for success at creating the object.
     */
    public function testCreate()
    {
        $t = new sfGuardUserPermission();
        $this->assertTrue($t instanceof sfGuardUserPermission);
    }

}