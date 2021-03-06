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

    public $_skip_backup = false;

    public function setSkipBackup($value)
    {
        $this->_skip_backup = (bool) $value;
    }

    public function getSkipBackup($value)
    {
        return (bool) $this->_skip_backup;
    }

    /**
     * Returns the Episode title
     *
     * @return string  The object formatted as a string
     */
    public function __toString()
    {
        return (string) $this->getTitle();
    }

    public function setTitle($value)
    {
        if (!$this->getApprovedAt())
            $this->_set('title', $value);
    }

    public function setDescription($value)
    {
        if (!$this->getApprovedAt())
            $this->_set('description', $value);
    }

    public function setAudioFile($value)
    {
        if (!$this->getApprovedAt())
            $this->_set('audio_file', $value);
    }

    public function setNiceFilename($value)
    {
        if (!$this->getApprovedAt())
            $this->_set('nice_filename', $value);
    }

    public function setGraphicFile($value)
    {
        if (!$this->getApprovedAt())
            $this->_set('graphic_file', $value);
    }

    public function setIncremented($id)
    {
        $this->_id = array($id);
        $this->set('id', $id, false);
        $this->_lastModified = array();
    }

    public function setEpisodeAssignmentId($episode_assignment_id)
    {
        $episode_assignment = EpisodeAssignmentTable::getInstance()->find($episode_assignment_id);

        if (is_null($episode_assignment_id) || is_null($episode_assignment))
            return;

        /* If they're not within their Deadline we cannot save them to the
         * Episode.
         */
        if (!$episode_assignment->isBeforeDeadlineForAuthorType()) {
            return;
        }

        $this->_set('episode_assignment_id', $episode_assignment_id);
    }

    public function setIsSubmitted($is_submitted)
    {
        // Episode Must already have a User within their Deadline.
        $assignment = $this->getEpisodeAssignmentId();
        if (!$assignment) {
            return;
        }
        if (!$this->getEpisodeAssignment()->isBeforeDeadlineForAuthorType()) {
            return;
        }

        // Episode must also have a file attached
        if (!$this->getAudioFile()) {
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
        if (!$this->getEpisodeAssignmentId() || !$this->getIsSubmitted())
            return;

        // A user cannot approve their own Episode
        if ($this->getEpisodeAssignment()->getSfGuardUserId() == $approver_id)
            return;

        // The Approver must actually *be* an approver in the Episode Subreddit.
        $membership = sfGuardUserSubredditMembershipTable::getInstance()
                ->getFirstByUserSubredditAndMemberships(
                $approver_id, $this->getSubredditId(), array('moderator', 'admin')
        );
        if (!$membership)
            return;

        // Make sure that the user has been validated as a emmber of Reddit!
        $user = sfGuardUserTable::getInstance()->find($approver_id);
        if ($user && !$user->getIsValidated())
            return;

        $this->_set('approved_by', $approver_id);
    }

    public function getNiceFilename()
    {
        $nice_filename = $this->_get('nice_filename');
        $nice_filename = ($nice_filename ? $nice_filename : $this->getAudioFile());
        $nice_filename = preg_replace("/[^a-zA-Z0-9\-:\(\)\.]/", "_", $nice_filename);
        return $nice_filename;
    }

    public function getGraphicUrl()
    {
        if (!$this->getGraphicFile())
            return null;
        if (time() > strtotime($this->getReleaseDate())) {
            return rtrim(ProjectConfiguration::getApplicationAmazonCloudFrontUrl(), '/') . '/upload/' . $this->getGraphicFile();
        } else {
            return rtrim(ProjectConfiguration::getApplicationAmazonBucketUrl(), '/') . '/upload/' . $this->getGraphicFile();
        }
    }

    public function setIsApproved($is_approved)
    {
        // Episode Must already have a User within their Deadline.
        $assignment = $this->getEpisodeAssignmentId();
        if (!$assignment) {
            return;
        }
        if (!$this->getEpisodeAssignment()->isBeforeDeadlineForAuthorType()) {
            return;
        }

        // Episode must already have an Approver set beforehand
        if (!$this->getApprovedBy())
            return;

        // The Episode cannot be approved by its User
        if ($this->getEpisodeAssignment()->getSfGuardUserId() == $this->getApprovedBy())
            return;

        // The Approver must actually *be* an approver in the Episode Subreddit.
        $membership = sfGuardUserSubredditMembershipTable::getInstance()
                ->getFirstByUserSubredditAndMemberships(
                $this->getApprovedBy(), $this->getSubredditId(), array('moderator', 'admin')
        );
        if (!$membership)
            return;

        // We set the timestamp for this action.
        if ($is_approved)
            $this->setApprovedAt(date('Y-m-d H:i:s'));

        $now = new DateTime();
        $release_date = new DateTime($this->getReleaseDate());

        if ($is_approved && !$this->_get('is_approved')) {
            // We move the physical file of the Episode to Amazon.
            $this->moveEpisodeFileToAmazon();
        } elseif (!$is_approved && $this->_get('is_approved') && ($now < $release_date)) {
            // We move the physical file of the Episode from Amazon.
            $this->moveEpisodeFileFromAmazon();
        }

        $this->_set('is_approved', $is_approved);
    }

    public function saveFileToApplicationBucket($file_location, $filename, $prefix, $permissions = null)
    {
        ProjectConfiguration::registerAws();
        $permissions = is_null($permissions) ? AmazonS3::ACL_PRIVATE : $permissions;
        $location = $file_location . $filename;
        if (!file_exists($location))
            throw new Exception("No local file to upload!");
        $s3 = new AmazonS3();
        $bucket = ProjectConfiguration::getApplicationAmazonBucketName();
        if ($s3->if_bucket_exists($bucket)) {
            $s3->delete_object($bucket, $prefix . '/' . $filename);
            $response = $s3->create_object($bucket, $prefix . '/' . $filename, array(
                'fileUpload' => $location,
                'acl' => $permissions,
                    ));
            if (!$response->isOK()) {
                throw new Exception("Error uploading file!");
            }
        } else {
            throw new Exception("Amazon bucket '$bucket' does not exist!");
        }
        return $response;
    }
    
    public function pullAudioFileFromApplicationBucket()
    {
        ProjectConfiguration::registerAws();
        $file_location = rtrim(ProjectConfiguration::getEpisodeAudioFileLocalDirectory(), '/') . '/';
        $s3 = new AmazonS3();
        $bucket = ProjectConfiguration::getApplicationAmazonBucketName();
        if (!$s3->if_bucket_exists($bucket)) {
            throw new Exception("Amazon bucket '$bucket' does not exist!");
        }
        $response = $s3->get_object($bucket, 'audio/' . $this->getAudioFile(), array(
            'fileDownload' => $file_location . $this->getAudioFile()
                ));
    }
    
    public function removeFileFromApplicationBucket($filename, $prefix)
    {
        ProjectConfiguration::registerAws();
        $s3 = new AmazonS3;
        $bucket = ProjectConfiguration::getApplicationAmazonBucketName();
        if ($s3->if_bucket_exists($bucket)) {
            $response =$s3->delete_object($bucket, $prefix . '/' . $filename);
            if (!$response->isOK()) {
                throw new Exception("Error deleting file!");
            }
        } else {
            throw new Exception("Amazon bucket '$bucket' does not exist!");
        }
        return $response;
    }

    public function moveEpisodeFileToAmazon()
    {
        if (!$this->getAudioFile())
            throw new Exception("No local file to upload!");
        $file_location = ProjectConfiguration::getEpisodeAudioFileLocalDirectory();
        $filename = $file_location . $this->getAudioFile();
        if (!file_exists($filename))
            throw new Exception("No local file to upload!");
        ProjectConfiguration::registerAws();
        $s3 = new AmazonS3();
        $bucket = $this->getSubreddit()->getBucketName();
        if ($s3->if_bucket_exists($bucket)) {
            $nice_filename = $this->getNiceFilename();
            while ($s3->if_object_exists($bucket, $nice_filename)) {
                $nice_filename = $nice_filename . rand(0,1000);
                $this->setNiceFilename($nice_filename);
            }
            $response = $s3->create_object($bucket, $this->getNiceFilename(), array(
                'fileUpload' => $file_location . $this->getAudioFile(),
                'acl' => AmazonS3::ACL_PUBLIC,
                    ));
            if ($response->isOK()) {
                //$this->setRemoteUrl($s3->get_object_url($bucket,
                //                                        $this->getNiceFilename()));
                $this->setRemoteUrl('http://'
                        . $this->getSubreddit()->getCfDomainName()
                        . '/'
                        . urlencode($this->getNiceFilename()));
                $this->deleteLocalFile($this->getAudioFile());
            }
        } else {
            throw new Exception("Amazon bucket '$bucket' does not exist!");
        }
        $this->setFileIsRemote(true);
    }

    public function moveEpisodeFileFromAmazon()
    {
        ProjectConfiguration::registerAws();
        $file_location = ProjectConfiguration::getEpisodeAudioFileLocalDirectory();
        $s3 = new AmazonS3();
        $bucket = $this->getSubreddit()->getBucketName();
        if (!$s3->if_bucket_exists($bucket)) {
            throw new Exception("Amazon bucket '$bucket' does not exist!");
        }
        $response = $s3->get_object($bucket, $this->getNiceFilename(), array(
            'fileDownload' => $file_location . $this->getAudioFile()
                ));
        if (!$response->isOK())
            throw new Exception('There was an error retrieving from Amazon.');
        $this->deleteEpisodeFileFromAmazon();
        $this->setFileIsRemote(false);
    }

    public function deleteLocalFile($filename, $file_location = null)
    {
        $file_location = ($file_location ? $file_location : ProjectConfiguration::getEpisodeAudioFileLocalDirectory());
        if (!file_exists($filename)) {
            return;
        }
        if (!unlink($file_location . $filename)) {
            throw new Exception("Failed to remove $file_location$filename...\n");
        }
    }

    public function deleteEpisodeFileFromAmazon($filename = null, $bucket = null)
    {
        ProjectConfiguration::registerAws();
        $s3 = new AmazonS3();
        $bucket = (is_null($bucket) ? $this->getSubreddit()->getBucketName() : $bucket);
        if (!$s3->if_bucket_exists($bucket)) {
            throw new Exception("Amazon bucket '$bucket' does not exist!");
        }
        $filename = (is_null($filename) ? $this->getNiceFilename() : $filename);
        $response = $s3->delete_object($bucket, $filename);
        if (!$response->isOK())
            throw new Exception('Failed to remove file from Amazon!');
    }

    public function delete(Doctrine_Connection $conn = null)
    {
        $is_remote = $this->getFileIsRemote();
        $audio_filename = $this->getAudioFile();
        $graphic_filename = $this->getGraphicFile();
        $nice_filename = $this->getNiceFilename();
        $bucket = $this->getSubreddit()->getBucketName();
        parent::delete($conn);
        if ($is_remote)
            $this->deleteEpisodeFileFromAmazon($audio_filename);
        if (!$is_remote && $audio_filename)
            $this->deleteLocalFile($audio_filename);
        if ($graphic_filename)
            $this->deleteLocalFile($graphic_filename, ProjectConfiguration::getEpisodeGraphicFileLocalDirectory());
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

    public function save(Doctrine_Connection $conn = null)
    {
        if (!$this->isNew() && !$this->getSkipBackup() && in_array('graphic_file', $this->_modified) && $this->_get('graphic_file')) {
            $file_location = rtrim(ProjectConfiguration::getEpisodeGraphicFileLocalDirectory(), '/') . '/';
            $filename = $this->_get('graphic_file');
            if (file_exists($file_location . $filename)) {
                ProjectConfiguration::registerAws();
                $response = $this->saveFileToApplicationBucket($file_location, $filename, 'upload', AmazonS3::ACL_PUBLIC);
                if ($response->isOK())
                {
                    unlink($file_location . $filename);
                }
            }
        }

        if (!$this->isNew() && !$this->getSkipBackup() && in_array('audio_file', $this->_modified) && $this->_get('audio_file')) {
            $file_location = rtrim(ProjectConfiguration::getEpisodeAudioFileLocalDirectory(), '/') . '/';
            $filename = $this->_get('audio_file');
            if (file_exists($file_location . $filename)) {
                ProjectConfiguration::registerAws();
                $response = $this->saveFileToApplicationBucket($file_location, $filename, 'audio');
            }
        }

        if (!$this->isNew() && in_array('is_submitted', $this->_modified) && $this->_get('is_submitted')) {
            /* The episode has been submitted.  We need to send an email about
             * it to the subreddit moderators.
             */
            $types = array(
                'moderator',
            );
            $memberships = sfGuardUserSubredditMembershipTable::getInstance()->getAllBySubredditAndMemberships($this->getSubredditId(), $types);
            $initial_is_submitted = $this->_get('is_submitted');
            $initial_submitted_at = $this->_get('submitted_at');
            foreach ($memberships as $membership) {
                $user = $membership->getSfGuardUser();

                $parameters = array(
                    'user_id' => $membership->getSfGuardUserId(),
                    'episode_id' => $this->getIncremented(),
                );

                $prefer_html = $user->getPreferHtml();
                $address = $user->getEmailAddress();
                $name = ($user->getPreferredName() ?
                                $user->getPreferredName() : $user->getFullName());

                $email = EmailTable::getInstance()->getFirstByEmailTypeAndLanguage('EpisodeApprovalPending', $user->getPreferredLanguage());

                $subject = $email->generateSubject($parameters);
                $body = $email->generateBodyText($parameters, $prefer_html);

                $from = sfConfig::get('app_email_address', ProjectConfiguration::getApplicationName() . ' <' .ProjectConfiguration::getApplicationEmailAddress() . '>');
                
                AppMail::sendMail($address, $from, $subject, $body, $prefer_html ? $body : null);
                
                $user->addLoginMessage('You have Episodes awaiting your approval.');
            }
            // @todo: The previous foreach loop sets the 'is_submitted' and 'submitted_at' columns to null.  I don't know why.
            $this->_set('is_submitted', $initial_is_submitted);
            $this->_set('submitted_at', $initial_submitted_at);
        }

        return parent::save($conn);
    }

    public function getCurrentEpisodeAssignmentByDeadline()
    {
        $assignments = $this->getEpisodeAssignments();

        $subreddit_rules = $this->getSubreddit()->getDeadlineRules();

        $largest_seconds = 0;
        $longest_deadline = null;

        foreach ($subreddit_rules as $author_type_id => $seconds) {
            // If the deadline has passed, ignore it
            if ($this->getReleaseDate('U') > (time() + $seconds))
                unset($subreddit_rules[$author_type_id]);
            if ($seconds > $largest_seconds) {
                $largest_seconds = $seconds;
                $longest_deadline = $author_type_id;
            }
        }

        if (is_null($longest_deadline))
            return null;

        foreach ($assignments as $assignment) {
            if ($assignment->getAuthorTypeId() == $longest_deadline)
                return $assignment;
        }

        return null;
    }

}
