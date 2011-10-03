<?php
require_once dirname(__FILE__).'/../../../../bootstrap/unit.php';

class sfGuardRememberKeyTest extends sfPHPUnitBaseTestCase
{
  public function testCreate()
  {
    $t = new sfGuardRememberKey();
    $this->assertTrue($t instanceof sfGuardRememberKey);
  }
}