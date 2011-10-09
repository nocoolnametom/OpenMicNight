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

    /**
     * Returns the Episode title
     *
     * @return string  The object formatted as a string
     */
    public function __toString()
    {
        return $this->getTitle();
    }

    public function setSfGuardUserId($user_id)
    {
        $user_found = false;
        // Verify that the user exists in an EpisodeAssignment
        foreach ($this->getEpisodeAssignments() as $assignment) {
            /* @var $assignment EpisodeAssignment */
            $episode_user_id = $assignment->getSfGuardUserId();
            if ($episode_user_id == $user_id) {
                $user_found = true;
                break;
            }
        }
        if (!$user_found && !is_null($user_id))
            return;

        // We found a user - are they within their Deadline?
        if ($user_found) {
            /* If they're not within their Deadline we cannot save them to the
             * Episode.
             */
            if (!$assignment->isBeforeDeadlineForAuthorType()) {
                return;
            }
        }

        $this->_set('sf_guard_user_id', $episode_user_id);
    }

    public function setIsSubmitted($is_submitted)
    {
        // Episode Must already have a User
        if (!$this->getSfGuardUser())
            return;

        // The User who is submitting must be within their Deadline.
        $assignment = EpisodeAssignmentTable::getInstance()
                ->getFirstByUserEpisodeAndSubreddit(
                $this->getSfGuardUserId(), $this->getIncremented(),
                $this->getSubredditId());
        if (!$assignment || !$assignment->isBeforeDeadlineForAuthorType()) {
            return;
        }

        // We set the timestamp for this action.
        if ($is_submitted)
            $this->setSubmittedAt(date('Y-m-d H:i:s'));

        if ($this->getIsSubmitted())
            return;

        $this->_set('is_submitted', $is_submitted);
    }

    public function setApprovedBy($approver_id)
    {
        // Episode must already have a User
        if (!$this->getSfGuardUserId() || !$this->getIsSubmitted())
            return;
        
        if ($this->getSfGuardUserId() == $approver_id)
            return;
        
        // The Approver must actually *be* an approver in the Episode Subreddit.
        $membership = sfGuardUserSubredditMembershipTable::getInstance()
                ->getFirstByUserSubredditAndMemberships(
                $approver_id,
                $this->getSubredditId(), array('moderator', 'admin')
        );
        if (!$membership)
            return;

        $this->_set('approved_by', $approver_id);
    }

    public function setIsApproved($is_approved)
    {
        // Episode must already have a User
        if (!$this->getSfGuardUser())
            return;

        // Episode must already have an Approver set beforehand
        if (!$this->getApprovedBy())
            return;

        // The Episode cannot be approved by its User
        if ($this->getSfGuardUser() == $this->getApprovedBy())
            return;

        // The Approver must actually *be* an approver in the Episode Subreddit.
        $membership = sfGuardUserSubredditMembershipTable::getInstance()
                ->getFirstByUserSubredditAndMemberships(
                $this->getApprovedBy(),
                $this->getSubredditId(), array('moderator', 'admin')
        );
        if (!$membership)
            return;

        // We set the timestamp for this action.
        if ($is_approved)
            $this->setApprovedAt(date('Y-m-d H:i:s'));

        // We move the physical file of the Episode to Amazon.
        $this->moveEpisodeFileToAmazon();

        $this->_set('is_approved', $is_approved);
    }

    public function moveEpisodeFileToAmazon()
    {
        ;
    }

    public function getEpisodeAssignments()
    {
        return EpisodeAssignmentTable::getInstance()->getAllByEpisodeId($this->getIncremented());
    }

    public function getIsSubmitted()
    {
        return (bool) $this->_get('is_submitted');
    }

    public function getIsApproved()
    {
        return (bool) $this->_get('is_approved');
    }

}
