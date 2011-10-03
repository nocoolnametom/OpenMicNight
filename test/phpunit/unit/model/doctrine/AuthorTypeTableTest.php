<?php

require_once dirname(__FILE__) . '/../../../bootstrap/unit.php';

class AuthorTypeTableTest extends sfPHPUnitBaseTestCase
{

    public function testCreate()
    {
        $t = AuthorTypeTable::getInstance();
        $this->assertTrue($t instanceof Doctrine_Table);
    }

}