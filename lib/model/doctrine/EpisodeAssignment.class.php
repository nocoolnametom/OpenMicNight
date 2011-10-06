<?php

/**
 * EpisodeAssignment
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    OpenMicNight
 * @subpackage model
 * @author     Tom Doggett
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class EpisodeAssignment extends BaseEpisodeAssignment
{

    public function save(Doctrine_Connection $conn = null)
    {

        if ($this->isNew()) {
            /* Blocked users should not be able to sign up for an Episode.
             */
            if ($this->hasBlockedUser())
                $this->deleteWithException("Cannot create EpisodeAssignment "
                        . "because sfGuardUser " . $this->getSfGuardUserId()
                        . " has a blocked Membership within Subreddit "
                        . $this->getEpisode()->getSubredditId(), 101);

            /* Only one sfGuardUser can sign up for one Episode with the same
             * AuthorType for each Application period.
             */
            if ($this->hasExistingUserAuthorTypeAssignment())
                $this->deleteWithException("Cannot create EpisodeAssignment "
                        . "because sfGuardUser " . $this->getSfGuardUserId()
                        . " has already registered with AuthorType "
                        . $this->getAuthorTypeId() . " within Subreddit "
                        . $this->getEpisode()->getSubredditId(), 102);
            
            /* Only one sfGuardUser can sign up for one Episode with the same
             * AuthorType for each Application period.
             */
            if ($this->hasExistingAssignmentOnOtherEpisode())
                $this->deleteWithException("Cannot create EpisodeAssignment "
                        . "because sfGuardUser " . $this->getSfGuardUserId()
                        . " has already registered with AuthorType "
                        . $this->getAuthorTypeId() . " within Subreddit "
                        . $this->getEpisode()->getSubredditId(), 103);

            /* The EpisodeAssignment must be within the deadline for the
             * AuthorType.
             */
            if ($this->isPastDeadlineForAuthorType())
                $this->deleteWithException("Cannot create EpisodeAssignment "
                        . "because the deadline has already passed for "
                        . "AuthorType " . $this->getAuthorTypeId() . " within "
                        . "Subreddit " . $this->getEpisode()->getSubredditId(),
                        104);

            /* Even if the deadline has not yet passed, we may only sign up an 
             * AuthorType if the AuthorType is allowed to register before the 
             * previous AuthorType (meaning the AuthorType with the next-longest
             * deadline).  The next few checks are for this purpose.
             */
            $deadline_seconds = Doctrine::getTable('Deadline')
                    ->getSecondsByAuthorAndSubreddit(
                    $this->getAuthorTypeId(),
                    $this->getEpisode()->getSubredditId()
            );

            /* Check to see if there *is* a previous AuthorType by Deadline
             * length.
             */
            if ($previous_author_type_id = DeadlineTable::getInstance()
                    ->getFirstAuthorTypeIdBySubredditWhereDeadlineIsGreaterThan(
                    $deadline_seconds, $this->getEpisode()->getSubredditId()))
                /* If a previous AuthorType exists, we need to see if the
                 * current AuthorType is restricted until that previous 
                 * uthorType is expired.  If it *is* restricted, then we need to
                 * see if the previous AuthorType has yet expired.  If the
                 * previous AuthorType is still beyond its Deadline for the
                 * Episode (and has not expired) then we cannot allow the
                 * current EpisodeAssignment to be saved.
                 */
                if (ApplicationTable::getInstance()
                                ->getIfApplicationRestrictedByAuthorTypeAndSubreddit(
                                        $this->getAuthorTypeId(),
                                        $this->getEpisode()->getSubredditId())
                        && $this->isPastDeadlineForAuthorType($previous_author_type_id))
                    $this->deleteWithException("Cannot create "
                            . "EpisodeAssignment because the deadline has "
                            . "not yet passed for the previous AuthorType "
                            . $this->getAuthorTypeId() . " within Subreddit "
                            . $this->getEpisode()->getSubredditId(),
                            105);
        }

        parent::save($conn);
    }

    public function hasBlockedUser()
    {
        $membership = Doctrine::getTable('sfGuardUserSubredditMembership')
                ->getFirstByUserSubredditAndMemberships(
                $this->getSfGuardUserId(),
                $this->getEpisode()->getSubredditId(), array('blocked')
        );
        return ($membership ? true : false);
    }

    public function hasExistingUserAuthorTypeAssignment()
    {
        $assignment = Doctrine::getTable('EpisodeAssignment')
                ->getFirstByUserAuthorTypeAndSubreddit(
                $this->getAuthorTypeId(), $this->getSfGuardUserId(),
                $this->getEpisode()->getSubredditId()
        );
        return ($assignment ? true : false);
    }
    
    public function hasExistingAssignmentOnOtherEpisode()
    {
        $assignment = Doctrine::getTable('EpisodeAssignment')
                ->getFirstByEpisodeAuthorTypeAndSubreddit(
                $this->getAuthorTypeId(), $this->getEpisodeId(),
                $this->getEpisode()->getSubredditId()
        );
        return ($assignment ? true : false);
    }

    public function isPastDeadlineForAuthorType($author_type_id = null)
    {
        if (is_null($author_type_id))
            $author_type_id = $this->getAuthorTypeId();
        $deadline_seconds = Doctrine::getTable('Deadline')
                ->getSecondsByAuthorAndSubreddit(
                $author_type_id, $this->getEpisode()->getSubredditId()
        );
        $release_date = new DateTime($this->getEpisode()->getReleaseDate());
        $now = new DateTime(date('Y-m-d H:i:s', time()));
        $diff = $release_date->diff($now, true);
        $seconds_between = ($diff->y * 365 * 24 * 60 * 60) +
                ($diff->m * 30 * 24 * 60 * 60) +
                ($diff->d * 24 * 60 * 60) +
                ($diff->h * 60 * 60) +
                $diff->s;
        return ( $deadline_seconds > $seconds_between );
    }

    public function deleteWithException($message = null, $code = null,
                                        $previous = null)
    {
        $this->delete();
        throw new sfException($message, $code, $previous);
    }

}
