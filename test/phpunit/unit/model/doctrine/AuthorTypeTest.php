<?php

require_once dirname(__FILE__) . '/../../../bootstrap/unit.php';

class AuthorTypeTest extends sfPHPUnitBaseTestCase
{

    public function testCreate()
    {
        $test_description = 4321;
        $t = new AuthorType();
        $t->setDescription($test_description);
        $this->assertTrue($t instanceof AuthorType);
        $this->assertEquals($t->__toString(), $test_description);
    }
}