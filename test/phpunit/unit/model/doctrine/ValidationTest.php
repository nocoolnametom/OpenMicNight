<?php

require_once dirname(__FILE__) . '/../../../bootstrap/unit.php';

class ValidationTest extends sfPHPUnitBaseTestCase
{

    /**
     * Tests for success at creating the object.
     */
    public function testCreate()
    {
        $t = new Validation();
        $this->assertTrue($t instanceof Validation);
    }

}