<?php

require_once dirname(__FILE__) . '/../../../../bootstrap/unit.php';

class sfGuardUserGroupTest extends sfPHPUnitBaseTestCase
{

    /**
     * Tests for success at creating the object.
     */
    public function testCreate()
    {
        $t = new sfGuardUserGroup();
        $this->assertTrue($t instanceof sfGuardUserGroup);
    }

}