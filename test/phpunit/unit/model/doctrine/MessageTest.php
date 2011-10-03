<?php
require_once dirname(__FILE__).'/../../../bootstrap/unit.php';

class MessageTest extends sfPHPUnitBaseTestCase
{
  public function testCreate()
  {
    $test_text = 'test text';
    $t = new Message();
    $t->setText($test_text);
    $this->assertTrue($t instanceof Message);
    $this->assertEquals($t->__toString(), $test_text);
  }
}