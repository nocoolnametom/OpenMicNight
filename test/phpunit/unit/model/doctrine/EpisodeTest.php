<?php
require_once dirname(__FILE__).'/../../../bootstrap/unit.php';

class EpisodeTest extends sfPHPUnitBaseTestCase
{
  public function testCreate()
  {
    $test_title = 'test title';
    $t = new Episode();
    $t->setTitle($test_title);
    $this->assertTrue($t instanceof Episode);
    $this->assertEquals($t->__toString(), $test_title);
  }
}