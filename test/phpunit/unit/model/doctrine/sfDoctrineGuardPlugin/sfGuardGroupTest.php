<?php

require_once dirname(__FILE__) . '/../../../../bootstrap/unit.php';

class sfGuardGroupTest extends sfPHPUnitBaseTestCase
{

    /**
     * Tests for success at creating the object.
     */
    public function testCreate()
    {
        $t = new sfGuardGroup();
        $this->assertTrue($t instanceof sfGuardGroup);
    }

}