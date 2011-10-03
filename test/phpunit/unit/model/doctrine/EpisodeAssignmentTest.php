<?php
require_once dirname(__FILE__).'/../../../bootstrap/unit.php';

class EpisodeAssignmentTest extends sfPHPUnitBaseTestCase
{
public function testCreate()
  {
    $t = new EpisodeAssignment();
    $this->assertTrue($t instanceof EpisodeAssignment);
  }
}