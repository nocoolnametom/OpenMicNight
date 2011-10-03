<?php

require_once dirname(__FILE__) . '/../../../bootstrap/unit.php';

class ApplicationTableTest extends sfPHPUnitBaseTestCase
{

    public function testCreate()
    {
        $t = ApplicationTable::getInstance();
        $this->assertTrue($t instanceof Doctrine_Table);
    }

}