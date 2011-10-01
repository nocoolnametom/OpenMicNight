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
        if ($this->getMembership()->get('name') == 'blocked') {
            Doctrine_Query::create()
                    ->delete()
                    ->from('EpisodeAssignment')
                    ->leftJoin('Episode')
                    ->where('EpisodeAssignment.sf_guard_user_id = ?',
                            $this->getSfGuardUserId())
                    ->andWhere('Episode.subreddit_id = ?',
                               $this->getSubredditId())
                    ->andWhere('Episode.release_date > NOW()');
        }

        parent:save($conn);
    }
}
