<?php

require_once dirname(__FILE__) . '/../../../bootstrap/unit.php';

class ApplicationTableTest extends sfPHPUnitBaseTestCase
{

    public function testCreate()
    {
        $t = ApplicationTable::getInstance();
        $this->assertTrue($t instanceof Doctrine_Table);
    }

    public function testGetIfApplicationRestrictedByAuthorTypeAndSubreddit()
    {
        $first = AuthorTypeTable::getInstance()
                ->findOneBy('type', 'first');
        $understudy = AuthorTypeTable::getInstance()
                ->findOneBy('type', 'understudy');

        $subreddit = new Subreddit();
        $subreddit->save();

        $first_application = new Application();
        $first_application->setAuthorType($first);
        $first_application->setSubreddit($subreddit);
        $first_application->setRestrictedUntilPreviousMissesDeadline(false);
        $first_application->save();

        $understudy_application = new Application();
        $understudy_application->setAuthorType($understudy);
        $understudy_application->setSubreddit($subreddit);
        $understudy_application->setRestrictedUntilPreviousMissesDeadline(true);
        $understudy_application->save();

        $first_restricted = ApplicationTable::getInstance()
                ->getIfApplicationRestrictedByAuthorTypeAndSubreddit(
                $first_application->getAuthorTypeId(), $first_application->getSubredditId()
        );
        $understudy_restricted = ApplicationTable::getInstance()
                ->getIfApplicationRestrictedByAuthorTypeAndSubreddit(
                $understudy_application->getAuthorTypeId(), $understudy_application->getSubredditId()
        );
        
        $this->assertFalse($first_restricted);
        $this->assertTrue($understudy_restricted);

        $first_application->delete();
        $understudy_application->delete();
        $subreddit->delete();
    }

}