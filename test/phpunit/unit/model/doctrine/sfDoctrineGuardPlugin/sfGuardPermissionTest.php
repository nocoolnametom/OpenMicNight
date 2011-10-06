<?php

require_once dirname(__FILE__) . '/../../../../bootstrap/unit.php';

class sfGuardPermissionTest extends sfPHPUnitBaseTestCase
{

    /**
     * Tests for success at creating the object.
     */
    public function testCreate()
    {
        $t = new sfGuardPermission();
        $this->assertTrue($t instanceof sfGuardPermission);
    }

}