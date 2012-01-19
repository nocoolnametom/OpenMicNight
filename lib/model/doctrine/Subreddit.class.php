<?php

/**
 * Subreddit
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    OpenMicNight
 * @subpackage model
 * @author     Tom Doggett
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Subreddit extends BaseSubreddit
{

    /**
     * Returns the Subreddit name
     *
     * @return string  The object formatted as a string
     */
    public function __toString()
    {
        return $this->getName();
    }

    public function setIncremented($id)
    {
        $this->_id = array($id);
        $this->set('id', $id, false);
        $this->_lastModified = array();
    }

    public function save(Doctrine_Connection $conn = null)
    {
        if (sfConfig::get('sf_environment') != 'test' && ($this->isNew() || (in_array('name', $this->_modified) && $this->_get('name')))) {
            if (!$this->getBucketName() || strlen($this->getBucketName()) == 0) {
                $bucket_name = $this->createAmazonBucketName(
                        ProjectConfiguration::getAmazonBucketPrefix() . $this->_get('name'));
                $this->setBucketName($bucket_name);
            }
        }
        parent::save($conn);
    }

    public function createAmazonBucketName($name)
    {
        ProjectConfiguration::registerAws();
        $s3 = new AmazonS3();
        if (!$s3->if_bucket_exists($name)) {
            $s3->create_bucket($name, AmazonS3::REGION_US_E1,
                               AmazonS3::ACL_AUTH_READ);
            $exists = $s3->if_bucket_exists($name);
            $attempts = 0;
            while (!$exists && $attempts < 10) {
                // Not yet? Sleep for 1 second, then check again
                sleep(1);
                $exists = $s3->if_bucket_exists($name);
                $attempts++;
            }
            return $name;
        }
        $response = $s3->get_bucket_policy($name);
        if (in_array($response->status, array(403, 405)))
            return $this->createAmazonBucketName($name . rand(0, 1000));
    }

    public function deleteAmazonBucket($name)
    {
        ProjectConfiguration::registerAws();
        $s3 = new AmazonS3();
        if ($s3->if_bucket_exists($name)) {
            return $s3->delete_bucket($name, true);
        }
    }

    public function delete(Doctrine_Connection $conn = null)
    {
        $bucket_name = $this->getBucketName();
        parent::delete($conn);
        if ($bucket_name) {
            $this->deleteAmazonBucket($bucket_name);
        }
    }

    /**
     * Returns the Episode schedule as a CronExpression.
     * 
     * @see Cron\CronExpression::factory
     *
     * @return Cron\CronExpression The Episode schedule
     */
    public function getEpisodeScheduleAsCronExpression()
    {
        ProjectConfiguration::registerCron();
        return Cron\CronExpression::factory(parent::getEpisodeScheduleCronFormatted());
    }

    /**
     * Returns the creation schedule as a CronExpression.
     * 
     * @see Cron\CronExpression::factory
     *
     * @return Cron\CronExpression The creation schedule
     */
    public function getCreationScheduleAsCronExpression()
    {
        ProjectConfiguration::registerCron();
        return Cron\CronExpression::factory(parent::getCreateNewEpisodesCronFormatted());
    }

    /**
     * Returns the interval between Episodes as defined by the Subreddit's 
     * cron-formatted Episode schedule.
     *
     * @return DateInterval  The interval between Episodes
     */
    public function getEpisodeItervalAsDateInterval()
    {
        $next_creation = $this->getEpisodeScheduleAsCronExpression()->getNextRunDate();
        $after_that = $this->getEpisodeScheduleAsCronExpression()->getNextRunDate($next_creation);
        return $next_creation->diff($after_that);
    }

    /**
     * Returns the interval between Episode genration cycles as defined by the
     * Subreddit's cron-formatted Episode generation schedule.
     *
     * @return DateInterval  The interval between Episode generation cycles.
     */
    public function getCreationIntervalAsDateInterval()
    {
        $next_creation = $this->getCreationScheduleAsCronExpression()->getNextRunDate();
        $after_that = $this->getCreationScheduleAsCronExpression()->getNextRunDate($next_creation);
        return $next_creation->diff($after_that);
    }

    /**
     * Retrieves the release date for the youngest Episode of the Subreddit.
     *
     * @return string  The release date of the youngest Episode. 
     */
    public function getDateOfLastEpisode()
    {
        return SubredditTable::getInstance()->getLastEpisodeReleaseDate($this->getIncremented());
    }

    /**
     * Sets the Subreddit's creation interval to the number of seconds between
     * intervals of the current cron-formatted creation schedule.
     * 
     * It does not save the Subreddit, however.
     */
    public function calculateCreationInterval()
    {
        $original = $this->getCreationInterval();
        $creation_schedule = $this->getCreationScheduleAsCronExpression();

        $start = $creation_schedule->getNextRunDate();
        $stop = $creation_schedule->getNextRunDate($start);

        $diff = $start->diff($stop, true);
        $seconds_between = ($diff->y * 365 * 24 * 60 * 60) +
                ($diff->m * 30 * 24 * 60 * 60) +
                ($diff->d * 24 * 60 * 60) +
                ($diff->h * 60 * 60) +
                $diff->s;
        if ($original != $seconds_between)
        {
            $this->setCreationInterval($seconds_between);
            $this->save();
        }
    }

    /**
     * Creates a collection of Episodes with released dates assembled using the
     * Subreddit's Episode schedule between the Subreddit's creation interval.
     *
     * @return array  An array of unsaved Episode objects
     */
    public function collectGeneratedEpisodes()
    {
        ProjectConfiguration::registerCron();

        $this->calculateCreationInterval();

        $episode_schedule = $this->getEpisodeScheduleAsCronExpression();

        $creation_schedule = $this->getCreationScheduleAsCronExpression();

        $seconds = $this->getCreationInterval();

        $last_episode = new DateTime(date('R', $this->getDateOfLastEpisode())); // Jan 31 2011

        if ($last_episode->getTimestamp() <= time())
            $last_episode = new DateTime();

        $stop_creating = $creation_schedule->getNextRunDate($last_episode); // 01 Feb 2011
        while ((time() + $seconds) > $stop_creating->format('U')) {
            $stop_creating = $creation_schedule->getNextRunDate($stop_creating);  // Push it out one segment further
        }

        $episode_date = $last_episode;

        $new_episodes = new Doctrine_Collection('Episode');
        $i = 0;
        while ($episode_schedule->getNextRunDate($episode_date)->getTimestamp()
        <= $stop_creating->getTimestamp()) {
            $episode_date = $episode_schedule->getNextRunDate($episode_date);

            $episode = new Episode();
            $episode->setSubreddit($this);
            $episode->setReleaseDate($episode_date->format('Y-m-d H:i:s'));
            $new_episodes[$i++] = $episode;
        }

        return $new_episodes;
    }

    public function getDeadlineRules()
    {
        $deadlines = $this->getDeadlines();

        $deadline_rules = array();

        foreach ($deadlines as $deadline) {
            $deadline_rules[(int) $deadline->getAuthorTypeId()] = $deadline->getSeconds();
        }

        return $deadline_rules;
    }

    public function getFirstDeadlineId()
    {
        $deadline_rules = $this->getDeadlineRules();

        $longest = 0;
        $longest_id = null;
        foreach ($deadline_rules as $id => $seconds) {
            if ($seconds > $longest) {
                $longest = $seconds;
                $longest_id = $id;
            }
        }
        return $longest_id;
    }

    public function getNextDeadlineFrom($no_longer_than)
    {
        $deadline_rules = $this->getDeadlineRules();

        $longest = 0;
        $longest_id = null;
        foreach ($deadline_rules as $id => $seconds) {
            if ($seconds > $longest && $seconds < $no_longer_than) {
                $longest = $seconds;
                $longest_id = $id;
            }
        }
        return $longest_id;
    }

    public function assignUnassignedInLongestDeadline()
    {
        /* Before beginning the process of moving Episodes to new Assignments, 
         * we need to assign Episodes to the first Deadline assignment if they
         * haven't yet been so assigned.
         */
        $longest_id = $this->getFirstDeadlineId();

        $sql = "
SELECT episode_assignment.*
FROM episode_assignment
JOIN episode ON episode.id = episode_assignment.episode_id
WHERE episode.release_date >= NOW()
AND episode.is_approved <> 1
AND episode.subreddit_id = 1
AND episode_assignment.missed_deadline <> 1
AND episode.sf_guard_user_id IS NULL
AND episode_assignment.author_type_id = $longest_id;
        ";
        $query = Doctrine_Query::create()
                ->from('EpisodeAssignment ea')
                ->leftJoin('ea.Episode e')
                ->where('e.release_date >= ?', date('Y-m-d H:i:s'))
                ->andWhere('e.is_approved <> 1')
                ->andWhere('e.subreddit_id = ?', $this->getIncremented())
                ->andWhere('ea.missed_deadline <> 1')
                ->andWhere('e.sf_guard_user_id IS NULL')
                ->andWhere('ea.author_type_id = ?', $longest_id);
        $to_be_assigned = $query->execute();

        foreach ($to_be_assigned as $assignment) {
            /** @var $assignment EpisodeAssignment */
            $episode = $assignment->getEpisode();
            $episode->setSfGuardUserId($assignment->getSfGuardUserId());
            //$episode->save();

            $release_date = $episode->getReleaseDate('U');
            $seconds = DeadlineTable::getInstance()->getSecondsByAuthorAndSubreddit($longest_id,
                                                                                    $episode->getSubredditId());
            $deadline = $release_date - $seconds;

            // Send an email to that user telling them their EpisodeAssignment is now valid
            $this->sendEmailAboutNewAssignment($assignment->getSfGuardUserId(),
                                               $episode->getIncremented(),
                                               $deadline);
        }
    }

    public function advanceEpisodeAssignments()
    {
        // We grab the Deadlines in descending order for the Subreddit;
        $deadline_rules = $this->getDeadlineRules();

        $first_deadline_id = $this->getFirstDeadlineId();
        $first_deadline = DeadlineTable::getInstance()->find($first_deadline_id);

        // We establish the pool of emails we'll be sending.
        // First to those who pass their deadline
        $passed_deadline_assignments = array();

        // And to the new episode assignments that are reassigned
        $newly_assigned_assignments = array();

        // Now we can start on the assignments that are misassigned
        $sql = "`episode_assignment` ea
LEFT JOIN `episode` ON (`episode`.`id` = ea.`episode_id` AND `episode`.`is_approved` <> 1 AND `episode`.`release_date` > NOW() AND `episode`.`episode_assignment_id` = ea.`id` AND `episode`.`subreddit_id` = " . $this->getIncremented() . ")
/* Joing the deadline for the deadline seconds */
LEFT JOIN `deadline` ON (`deadline`.`author_type_id` = ea.`author_type_id` AND `deadline`.`subreddit_id` = " . $this->getIncremented() . ")
/* Make sure we're using the right deadlines for the episode's subreddit */
WHERE (ea.`missed_deadline` <> 1 OR ea.`missed_deadline` IS NULL)
/* Is the episode past the deadline for the assignment in question? */
AND UNIX_TIMESTAMP(`episode`.`release_date`) < (UNIX_TIMESTAMP() + `deadline`.`seconds`)";
        $q = new Doctrine_RawSql();
        $q->select('{ea.*}')
                ->from($sql)
                ->addComponent('ea', 'EpisodeAssignment ea');
        $assignments = $q->execute();
        for($i = 0; $i < count($assignments); $i++)
        {
            $passed_deadline_assignments[] = $assignments[$i];
            $assignments[$i]->setMissedDeadline(true);
            $episode = $assignments[$i]->getEpisode();
            // Clean up the Episode for any new user to use.
            $episode->setEpisodeAssignmentId(null);
            $audio_file = $episode->getAudioFile();
            $nice_filename = $episode->getNiceFilename();
            $graphic_file = $episode->getGraphicFile();
            $episode->setAudioFile(null);
            $episode->setNiceFilename(null);
            $episode->setGraphicFile(null);
            $episode->setIsNsfw(false);
            $episode->setTitle(null);
            $episode->setDescription(null);
            $episode->setIsSubmitted(false);
            $episode->setSubmittedAt(null);
            $episode->setFileIsRemote(null);
            $episode->setRemoteUrl(null);
            $episode->setRedditPostUrl(null);
            $episode->save();
        }
        $assignments->save();
        
        /* Now we make sure that all assignments past deadline are marked as
         * such.  If the assignment is here, however, then it hasn't ever
         * actually BEEN assigned and isn't added to the list of emails to send
         * out. */
        $sql = "`episode_assignment` ea
LEFT JOIN `episode` ON (`episode`.`id` = ea.`episode_id` AND `episode`.`subreddit_id` = " . $this->getIncremented() . ")
/* Joing the deadline for the deadline seconds */
LEFT JOIN `deadline` ON (`deadline`.`author_type_id` = ea.`author_type_id` AND `deadline`.`subreddit_id` = " . $this->getIncremented() . ")
/* Make sure we're using the right deadlines for the episode's subreddit */
WHERE (ea.`missed_deadline` <> 1 OR ea.`missed_deadline` IS NULL)
/* Is the episode past the deadline for the assignment in question? */
AND UNIX_TIMESTAMP(`episode`.`release_date`) < (UNIX_TIMESTAMP() + `deadline`.`seconds`)";
        $q = new Doctrine_RawSql();
        $q->select('{ea.*}')
                ->from($sql)
                ->addComponent('ea', 'EpisodeAssignment ea');
        $assignments = $q->execute();
        for($i = 0; $i < count($assignments); $i++)
        {
            $assignments[$i]->setMissedDeadline(true);
        }
        $assignments->save();

        /* Now all episodes are cleared and we need to see if they need to be
         * reassigned to an existing asignment. */
        $sql = "`episode_assignment` ea
LEFT JOIN `episode` ON (`episode`.`id` = ea.`episode_id` AND `episode`.`is_approved` <> 1 AND `episode`.`release_date` > NOW() AND (`episode`.`episode_assignment_id` IS NULL) AND `episode`.`subreddit_id` = " . $this->getIncremented() . ")
/* Joing the deadline for the deadline seconds */
LEFT JOIN `deadline` ON (`deadline`.`author_type_id` = ea.`author_type_id` AND `deadline`.`subreddit_id` = " . $this->getIncremented() . ")
/* Make sure we're using the right deadlines for the episode's subreddit */
WHERE (ea.`missed_deadline` <> 1 OR ea.`missed_deadline` IS NULL)
/* Is the episode past the deadline for the assignment in question? */
AND UNIX_TIMESTAMP(`episode`.`release_date`) > (UNIX_TIMESTAMP() + `deadline`.`seconds`)
ORDER BY `episode`.`id`,`deadline`.`seconds` DESC";
        $q = new Doctrine_RawSql();
        $q->select('{ea.*}')
                ->from($sql)
                ->addComponent('ea', 'EpisodeAssignment ea');

        /* Returns assignments closest to the front for each unassigned episode,
         * in order of closeness. */
        $assignments = $q->execute();
        $episodes_affected = array();

        foreach ($assignments as $assignment) {
            if (!in_array($assignment->getEpisodeId(), $episodes_affected)) {
                /* Ignore all subsequent assignments for an episode after the
                 * first!  We should only be dealing with assignments that have
                 * not missed their deadlines! */
                $episodes_affected[] = $assignment->getEpisodeId();
                $episode = $assignment->getEpisode();
                $assign_to_episode = false;

                /* If the *first* assignment is in the first spot, then assign
                 * it. */
                if ($assignment->getAuthorTypeId() == $first_deadline->getAuthorTypeId()) {
                    $assign_to_episode = true;
                } else {
                    /* Otherwise, check if we are past the deadline for the
                     * previous deadline. */
                    $previous_author_type_id = DeadlineTable::getInstance()
                            ->getFirstAuthorTypeIdBySubredditWhereDeadlineIsGreaterThan(
                            $deadline_rules[$assignment->getAuthorTypeId()],
                            $episode->getSubredditId());
                    $past_deadline_for_previous = strtotime($episode->getReleaseDate()) - $deadline_rules[$previous_author_type_id] <= time();
                    if ($past_deadline_for_previous) {
                        $assign_to_episode = true;
                    }
                }
                if ($assign_to_episode) {
                    $episode->setEpisodeAssignmentId($assignment->getIncremented());
                    $episode->save();
                    $newly_assigned_assignments[] = $assignment;
                }
            }
        }

        // We send the emails for the current deadline we're checking.
        foreach ($passed_deadline_assignments as $assignment) {
            $this->sendEmailAboutPassedDeadline($assignment->getSfGuardUserId(),
                                                $assignment->getEpisodeId());
        }

        foreach ($newly_assigned_assignments as $assignment) {
            $episode = $assignment->getEpisode();
            $release_date = strtotime($episode->getReleaseDate());
            $seconds = $deadline_rules[$assignment->getAuthorTypeId()];
            $deadline = $release_date - $seconds;
            $this->sendEmailAboutNewAssignment($assignment->getSfGuardUserId(),
                                               $episode->getIncremented(),
                                               $deadline);
        }
    }

    public function sendEmailAboutNewAssignment($user_id, $episode_id, $deadline)
    {
        // Send an email to that user telling them their EpisodeAssignment is now valid
        ProjectConfiguration::registerZend();

        $mail = new Zend_Mail();
        $mail->addHeader('X-MailGenerator',
                         ProjectConfiguration::getApplicationName());
        $parameters = array(
            'user_id' => $user_id,
            'episode_id' => $episode_id,
            'deadline' => date('Y-m-d H:i:s', $deadline),
        );
        $user = sfGuardUserTable::getInstance()->find($user_id);

        $prefer_html = $user->getPreferHtml();
        $address = $user->getEmailAddress();
        $name = ($user->getPreferredName() ?
                        $user->getPreferredName() : $user->getFullName());

        $email = EmailTable::getInstance()->getFirstByEmailTypeAndLanguage('NewlyOpenedEpisode',
                                                                           $user->getPreferredLanguage());

        $subject = $email->generateSubject($parameters);
        $body = $email->generateBodyText($parameters, $prefer_html);

        $mail->setBodyText($body);

        $mail->setFrom(sfConfig::get('app_email_address',
                                     ProjectConfiguration::getApplicationEmailAddress()),
                                     sfconfig::get('app_email_name',
                                                   ProjectConfiguration::getApplicationName() . 'Team'));
        $mail->addTo($address, $name);
        $mail->setSubject($subject);
        if (sfConfig::get('sf_environment') == 'prod') {
            $mail->send();
        } else {
            //throw new sfException('Mail sent: ' . $mail->getBodyText()->getRawContent());
            if (sfConfig::get('sf_logging_enabled')) {
                sfContext::getInstance()->getLogger()->info('Mail sent: ' . $mail->getBodyText()->getRawContent());
            }
            echo 'Mail sent: ' . $mail->getBodyText()->getRawContent();
        }
        $user->addLoginMessage('You have an episode that you can work with!');
    }

    public function sendEmailAboutPassedDeadline($user_id, $episode_id)
    {
        // Send an email to that user telling them their EpisodeAssignment is now valid
        ProjectConfiguration::registerZend();

        $mail = new Zend_Mail();
        $mail->addHeader('X-MailGenerator',
                         ProjectConfiguration::getApplicationName());
        $parameters = array(
            'user_id' => $user_id,
            'episode_id' => $episode_id
        );
        $user = sfGuardUserTable::getInstance()->find($user_id);

        $prefer_html = $user->getPreferHtml();
        $address = $user->getEmailAddress();
        $name = ($user->getPreferredName() ?
                        $user->getPreferredName() : $user->getFullName());

        $email = EmailTable::getInstance()->getFirstByEmailTypeAndLanguage('PassedDeadlineOnEpisode',
                                                                           $user->getPreferredLanguage());

        $subject = $email->generateSubject($parameters);
        $body = $email->generateBodyText($parameters, $prefer_html);

        $mail->setBodyText($body);

        $mail->setFrom(sfConfig::get('app_email_address',
                                     ProjectConfiguration::getApplicationEmailAddress()),
                                     sfconfig::get('app_email_name',
                                                   ProjectConfiguration::getApplicationName() . 'Team'));
        $mail->addTo($address, $name);
        $mail->setSubject($subject);
        if (sfConfig::get('sf_environment') == 'prod') {
            $mail->send();
        } else {
            //throw new sfException('Mail sent: ' . $mail->getBodyText()->getRawContent());
            if (sfConfig::get('sf_logging_enabled')) {
                sfContext::getInstance()->getLogger()->info('Mail sent: ' . $mail->getBodyText()->getRawContent());
            }
            echo 'Mail sent: ' . $mail->getBodyText()->getRawContent();
        }
        $user->addLoginMessage('Your episode passed its release deadline and has been re-assigned.');
    }
}
