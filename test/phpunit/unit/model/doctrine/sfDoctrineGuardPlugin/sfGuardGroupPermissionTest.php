<?php

require_once dirname(__FILE__) . '/../../../../bootstrap/unit.php';

class sfGuardGroupPermissionTest extends sfPHPUnitBaseTestCase
{

    /**
     * Tests for success at creating the object.
     */
    public function testCreate()
    {
        $t = new sfGuardGroupPermission();
        $this->assertTrue($t instanceof sfGuardGroupPermission);
    }

}