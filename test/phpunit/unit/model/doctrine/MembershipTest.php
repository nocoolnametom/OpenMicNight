<?php

require_once dirname(__FILE__) . '/../../../bootstrap/unit.php';

class MembershipTest extends sfPHPUnitBaseTestCase
{

    /**
     * Tests for success at creating the object.
     */
    public function testCreate()
    {
        $test_name = 'test_name';
        $t = new Membership();
        $t->setDescription($test_name);
        $this->assertTrue($t instanceof Membership);
        $this->assertEquals($t->__toString(), $test_name);
    }

}