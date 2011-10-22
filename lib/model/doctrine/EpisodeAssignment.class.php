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

    /**
     * applies the changes made to this object into database
     * this method is smart enough to know if any changes are made
     * and whether to use INSERT or UPDATE statement
     *
     * this method also saves the related components
     * 
     * Before creating a new EpisodeAssignment, this function verifies the
     * following rules:
     * 
     * 101) Blocked users should not be able to sign up for an Episode. Throws
     *      an sfException upon this failure.
     * 
     * 102) Only one sfGuardUser can sign up for one Episode with the same
     *      AuthorType for each Application period. Throws an sfException upon
     *      this failure.
     * 
     * 103) An sfGuardUser can only sign up for the same Episode with one
     *      AuthorType. Throws an sfException upon this failure.
     * 
     * 104) The EpisodeAssignment must be within the deadline for the
     *      AuthorType. Throws an sfException upon this failure.
     * 
     * 105) If the current AuthorType is flagged to be unavailable before the
     *      previous AuthorType has passed its Deadline by its Application
     *      record for the Subreddit and this previous Deadline has not passed,
     *      then the object will not be saved.  Throws an sfException upon this
     *      failure.
     * 
     * 106) The user attached to the EpisodeAssignment must be validated.
     * 
     * @see Doctrine_Record::save()
     * @throws sfException
     *
     * @param Doctrine_Connection $conn     optional connection parameter
     * @throws Exception                    if record is not valid and validation is active
     * @return void
     */
    public function save(Doctrine_Connection $conn = null)
    {

        /* If an EpisodeAssignment already exists we don't care about it. */
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
            if (!$this->isBeforeDeadlineForAuthorType())
                $this->deleteWithException("Cannot create EpisodeAssignment "
                        . "because the deadline has already passed for "
                        . "AuthorType " . $this->getAuthorTypeId() . " within "
                        . "Subreddit " . $this->getEpisode()->getSubredditId(), 104);

            /* Even if the deadline has not yet passed, we may only sign up an 
             * AuthorType if the AuthorType is allowed to register before the 
             * previous AuthorType (meaning the AuthorType with the next-longest
             * deadline).  The next few checks are for this purpose.
             */
            $deadline_seconds = Doctrine::getTable('Deadline')
                    ->getSecondsByAuthorAndSubreddit(
                    $this->getAuthorTypeId(), $this->getEpisode()->getSubredditId()
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
                                        $this->getAuthorTypeId(), $this->getEpisode()->getSubredditId())
                        && !$this->isBeforeDeadlineForAuthorType($previous_author_type_id))
                    $this->deleteWithException("Cannot create "
                            . "EpisodeAssignment because the deadline has "
                            . "not yet passed for the previous AuthorType "
                            . $this->getAuthorTypeId() . " within Subreddit "
                            . $this->getEpisode()->getSubredditId(), 105);

            if (!$this->hasVerifiedUser())
                $this->deleteWithException("Cannot create EpisodeAssignment "
                        . "because sfGuardUser " . $this->getSfGuardUserId()
                        . " has not been validated yet.", 106);
        }

        /* If the obejct is not new or has passed all rules for saving, we pass
         * it on to the parent save function.
         */
        parent::save($conn);
    }

    /**
     * Checks if the User of the EpisodeAssignment has a "blocked" Membership in
     * the Subreddit.
     * 
     * @see sfGuardUserSubredditMembership::getFirstByUserSubredditAndMemberships()
     *
     * @return bool Whether the user has a "blocked" Membership
     */
    public function hasBlockedUser()
    {
        $membership = Doctrine::getTable('sfGuardUserSubredditMembership')
                ->getFirstByUserSubredditAndMemberships(
                $this->getSfGuardUserId(), $this->getEpisode()->getSubredditId(), array('blocked')
        );
        return ($membership ? true : false);
    }

    /**
     * Checks if the User of the EpisodeAssignment has been validated as a
     * member of Reddit yet.
     *
     * @return bool  Whether the user is marked as "validated". 
     */
    public function hasVerifiedUser()
    {
        return (bool) $this->getSfGuardUser()->getIsValidated();
    }

    /**
     * Checks if the User of the EpisodeAssignment is already attached to a
     * future EpisodeAssignment of the Subreddit with the same AuthorType.
     * 
     * @see EpisodeAssignmentTable::getFirstByUserAuthorTypeAndSubreddit()
     *
     * @return bool Whether an EpisodeAssignment already exists
     */
    public function hasExistingUserAuthorTypeAssignment()
    {
        $assignment = Doctrine::getTable('EpisodeAssignment')
                ->getFirstByUserAuthorTypeAndSubreddit(
                $this->getAuthorTypeId(), $this->getSfGuardUserId(), $this->getEpisode()->getSubredditId()
        );
        return ($assignment ? true : false);
    }

    /**
     * Checks to see if the User of the EpsiodeAssignment is already attached to
     * the curent Episode via another Authortype.
     *
     * @return bool Whether the User is already attached to the Episode 
     */
    public function hasExistingAssignmentOnOtherEpisode()
    {
        $assignment = Doctrine::getTable('EpisodeAssignment')
                ->getFirstByEpisodeAuthorTypeAndSubreddit(
                $this->getAuthorTypeId(), $this->getEpisodeId(), $this->getEpisode()->getSubredditId()
        );
        return ($assignment ? true : false);
    }

    /**
     * Verifies if the current time is still before the Subreddit Deadline for
     * the AuthorType of the EpisodeAssignment.
     * 
     * If an AuthorType id is given, then the check is against the Subreddit
     * Deadline for that AuthorTyope.
     *
     * @param int $author_type_id  The id for a given AuthorType (optional)
     * @return bool                Whether the current time is within the 
     *                              Subreddit Deadline.
     */
    public function isBeforeDeadlineForAuthorType($author_type_id = null)
    {
        if (is_null($author_type_id))
            $author_type_id = $this->getAuthorTypeId();
        if ($this->getMissedDeadline())
            return false;
        $deadline_seconds = Doctrine::getTable('Deadline')
                ->getSecondsByAuthorAndSubreddit(
                $author_type_id, $this->getEpisode()->getSubredditId()
        );
        $release_date = new DateTime(EpisodeTable::getInstance()
                                ->getCurrentReleaseDate($this->getEpisodeId()));
        $now_and_deadline = new DateTime(date('Y-m-d H:i:s', time() + $deadline_seconds));
        if (($now_and_deadline > $release_date ) && !$this->getMissedDeadline()) {
            $this->setMissedDeadline(true);
        }
        return ( $now_and_deadline < $release_date );
    }

    /**
     * Deletes the current object and also throws and exception.
     * 
     * @throws sfException
     *
     * @param struing $message      The message for the exception
     * @param long $code            An error code for the exception
     * @param sfException $previous A previously thrown exception.
     */
    public function deleteWithException($message = null, $code = null, $previous = null)
    {
        $this->delete();
        throw new sfException($message, $code, $previous);
    }

}
