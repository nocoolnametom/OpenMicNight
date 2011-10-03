<?php
require_once dirname(__FILE__).'/../../../bootstrap/unit.php';

class MembershipTableTest extends sfPHPUnitBaseTestCase
{
    public function testCreate()
    {
        $t = MembershipTable::getInstance();
        $this->assertTrue($t instanceof Doctrine_Table);
    }
}