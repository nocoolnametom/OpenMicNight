<?php

/**
 * Episode
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    OpenMicNight
 * @subpackage model
 * @author     Tom Doggett
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Episode extends BaseEpisode
{
    public function __toString()
    {
        return $this->getTitle();
    }
}
