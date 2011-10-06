<?php

require_once dirname(__FILE__) . '/../../../../bootstrap/unit.php';

class sfGuardForgotPasswordTest extends sfPHPUnitBaseTestCase
{

    /**
     * Tests for success at creating the object.
     */
    public function testCreate()
    {
        $t = new sfGuardForgotPassword();
        $this->assertTrue($t instanceof sfGuardForgotPassword);
    }

}