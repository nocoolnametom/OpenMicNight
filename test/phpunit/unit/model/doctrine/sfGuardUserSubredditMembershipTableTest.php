<?php
require_once dirname(__FILE__).'/../../../bootstrap/unit.php';

class sfGuardUserSubredditMembershipTableTest extends sfPHPUnitBaseTestCase
{
    public function testCreate()
    {
        $t = sfGuardUserSubredditMembershipTable::getInstance();
        $this->assertTrue($t instanceof Doctrine_Table);
    }
}