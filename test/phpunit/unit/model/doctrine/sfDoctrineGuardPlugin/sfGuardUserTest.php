<?php

require_once dirname(__FILE__) . '/../../../../bootstrap/unit.php';

class sfGuardUserTest extends sfPHPUnitBaseTestCase
{

    /**
     * Tests for success at creating the object.
     */
    public function testCreate()
    {
        $t = new sfGuardUser();
        $this->assertTrue($t instanceof sfGuardUser);
    }

}