<?php

require_once dirname(__FILE__) . '/../../../bootstrap/unit.php';

class DeadlineTest extends sfPHPUnitBaseTestCase
{

    /**
     * Tests for success at creating the object.
     */
    public function testCreate()
    {
        $test_seconds = 4321;
        $t = new Deadline();
        $t->setSeconds($test_seconds);
        $this->assertTrue($t instanceof Deadline);
        $this->assertEquals($t->__toString(), $test_seconds);
    }

}