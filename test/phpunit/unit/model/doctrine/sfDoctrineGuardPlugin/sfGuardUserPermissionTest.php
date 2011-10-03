<?php
require_once dirname(__FILE__).'/../../../../bootstrap/unit.php';

class sfGuardUserPermissionTest extends sfPHPUnitBaseTestCase
{
  public function testCreate()
  {
    $t = new sfGuardUserPermission();
    $this->assertTrue($t instanceof sfGuardUserPermission);
  }
}