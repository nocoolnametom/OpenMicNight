<?php
require_once dirname(__FILE__).'/../../../bootstrap/unit.php';

class ValidationTableTest extends sfPHPUnitBaseTestCase
{
    public function testCreate()
    {
        $t = ValidationTable::getInstance();
        $this->assertTrue($t instanceof Doctrine_Table);
    }
}