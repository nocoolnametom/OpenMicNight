<?php
require_once dirname(__FILE__).'/../../../../bootstrap/unit.php';

class sfGuardForgotPasswordTest extends sfPHPUnitBaseTestCase
{
  public function testCreate()
  {
    $t = new sfGuardForgotPassword();
    $this->assertTrue($t instanceof sfGuardForgotPassword);
  }
}