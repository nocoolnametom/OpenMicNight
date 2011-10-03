<?php
require_once dirname(__FILE__).'/../../../../bootstrap/unit.php';

class sfGuardUserGroupTest extends sfPHPUnitBaseTestCase
{
  public function testCreate()
  {
    $t = new sfGuardUserGroup();
    $this->assertTrue($t instanceof sfGuardUserGroup);
  }
}