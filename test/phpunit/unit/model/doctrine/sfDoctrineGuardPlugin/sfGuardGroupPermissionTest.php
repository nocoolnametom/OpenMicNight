<?php
require_once dirname(__FILE__).'/../../../../bootstrap/unit.php';

class sfGuardGroupPermissionTest extends sfPHPUnitBaseTestCase
{
  public function testCreate()
  {
    $t = new sfGuardGroupPermission();
    $this->assertTrue($t instanceof sfGuardGroupPermission);
  }
}