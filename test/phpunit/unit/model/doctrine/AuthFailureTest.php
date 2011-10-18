<?php
require_once dirname(__FILE__) . '/../../../bootstrap/unit.php';

class AuthFailureTest extends sfPHPUnitBaseTestCase
{

    /**
     * Tests for success at creating the object.
     */
    public function testCreate()
    {
        $t = new AuthFailure();
        $this->assertTrue($t instanceof AuthFailure);
    }
}