<?php

/**
 *
 * @package    sfPhpunitNetBeansPlugin
 * @subpackage lib
 * 
 * @author     Maksim Kotlyar <mkotlar@ukr.net>
 */
class sfPhpunitNetbeansSuite extends PHPUnit_Framework_TestSuite
{
  public static $path;

  public static function suite()
  {
    return sfPhpunitProjectTestLoader::factory(self::$path)->suite();
  }
}
