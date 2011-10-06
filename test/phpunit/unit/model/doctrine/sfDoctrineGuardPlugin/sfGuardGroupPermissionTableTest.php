<?php

require_once dirname(__FILE__) . '/../../../../bootstrap/unit.php';

class sfGuardGroupPermissionTableTest extends sfPHPUnitBaseTestCase
{

    /**
     * Tests for success at creating the object.
     */
    public function testCreate()
    {
        $t = sfGuardGroupPermissionTable::getInstance();
        $this->assertTrue($t instanceof Doctrine_Table);
    }

}