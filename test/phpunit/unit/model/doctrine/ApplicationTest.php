<?php
require_once dirname(__FILE__).'/../../../bootstrap/unit.php';

class ApplicationTest extends sfPHPUnitBaseTestCase
{
  public function testCreate()
  {
    $t = new Application();
    $this->assertTrue($t instanceof Application);
  }
}