<?php
require_once dirname(__FILE__) . '/../../../bootstrap/unit.php';

class AuthFailureTableTest extends sfPHPUnitBaseTestCase
{
    /**
     * Tests for success at creating the object.
     */
    public function testCreate()
    {
        $t = AuthFailureTable::getInstance();
        $this->assertTrue($t instanceof Doctrine_Table);
    }
}