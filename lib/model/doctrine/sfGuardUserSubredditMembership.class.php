<?php

/**
 * sfGuardUserSubredditMembership
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    OpenMicNight
 * @subpackage model
 * @author     Tom Doggett
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class sfGuardUserSubredditMembership extends BasesfGuardUserSubredditMembership
{

    public function save(Doctrine_Connection $conn = null)
    {
        /* If we're establishing a blocked membership, then we need to remove
         * all future Episode assignments.
         */
        if ($this->getMembership()->getType() == 'blocked') {
            Doctrine::getTable('EpisodeAssignment')
                    ->deleteBySubredditIdAndUserId(
                            $this->getSubredditId(), $this->getSfGuardUserId()
            );
        }

        parent::save($conn);
    }

}