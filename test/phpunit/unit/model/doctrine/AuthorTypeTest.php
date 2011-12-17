<?php

require_once dirname(__FILE__) . '/../../../bootstrap/unit.php';

class AuthorTypeTest extends sfPHPUnitBaseTestCase
{

    /**
     * Tests for success at creating the object.
     */
    public function testCreate()
    {
        $test_type = "killer_whale";
        $nice_type = "Killer Whale";
        $t = new AuthorType();
        $t->setType($test_type);
        $this->assertTrue($t instanceof AuthorType);
        $this->assertEquals($t->__toString(), $nice_type);
    }

}