<?php
require_once dirname(__FILE__).'/../../../../bootstrap/unit.php';

class sfGuardGroupTest extends sfPHPUnitBaseTestCase
{
  public function testCreate()
  {
    $t = new sfGuardGroup();
    $this->assertTrue($t instanceof sfGuardGroup);
  }
}