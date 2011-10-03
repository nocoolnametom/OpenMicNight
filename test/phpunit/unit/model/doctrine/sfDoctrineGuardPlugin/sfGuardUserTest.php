<?php
require_once dirname(__FILE__).'/../../../../bootstrap/unit.php';

class sfGuardUserTest extends sfPHPUnitBaseTestCase
{
  public function testCreate()
  {
    $t = new sfGuardUser();
    $this->assertTrue($t instanceof sfGuardUser);
  }
}