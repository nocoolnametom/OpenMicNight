<?php

/**
 * Message
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    OpenMicNight
 * @subpackage model
 * @author     Tom Doggett
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Message extends BaseMessage
{

    /**
     * Returns the Message text
     *
     * @return string  The object formatted as a string
     */
    public function __toString()
    {
        return $this->getText();
    }

    public function save(Doctrine_Connection $conn = null)
    {
        $send_message = false;

        if ($this->isNew()) {
            if (!$this->hasVerifiedSender())
                $this->deleteWithException("Cannot create Message "
                        . "because sfGuardUser " . $this->getSenderId()
                        . " has not been validated yet.", 406);
            if (!$this->hasVerifiedRecipient())
                $this->deleteWithException("Cannot create Message "
                        . "because sfGuardUser " . $this->getSenderId()
                        . " has not been validated yet.", 406);

            $send_message = true;
        }

        /* If the obejct is not new or has passed all rules for saving, we pass
         * it on to the parent save function.
         */
        parent::save($conn);

        if ($send_message) {
            /* The following is for sending an email to the recipient to notify them that they've received a message.
             */
            $recipient = sfGuardUserTable::getInstance()->find($this->getRecipientId());
            if ( !$recipient || !$recipient->getReceiveNotificationOfPrivateMessages())
                return parent::save($conn);;

            ProjectConfiguration::registerZend();
            $mail = new Zend_Mail();
            $mail->addHeader('X-MailGenerator', ProjectConfiguration::getApplicationName());
            $parameters = array(
                'user_id' => $this->getRecipientId(),
                'message_id' => $this->getIncremented(),
            );

            $prefer_html = $recipient->getPreferHtml();
            $address = $recipient->getEmailAddress();
            $name = ($recipient->getPreferredName() ?
                            $recipient->getPreferredName() : $recipient->getFullName());

            $email = EmailTable::getInstance()->getFirstByEmailTypeAndLanguage('NewPrivateMessage', $recipient->getPreferredLanguage());

            $subject = $email->generateSubject($parameters);
            $body = $email->generateBodyText($parameters, $prefer_html);

            $mail->setBodyText($body);

            $mail->setFrom(sfConfig::get('app_email_address', 'donotreply@' . ProjectConfiguration::getApplicationName()), sfconfig::get('app_email_name', ProjectConfiguration::getApplicationName() . 'Team'));
            $mail->addTo($address, $name);
            $mail->setSubject($subject);
            if (sfConfig::get('sf_environment') == 'prod') {
                $mail->send();
            } else {
                throw new sfException('Mail sent: ' . $mail->getBodyText()->getRawContent());
            }
        }
    }

    /**
     * Checks if the User of the EpisodeAssignment has been validated as a
     * member of Reddit yet.
     *
     * @return bool  Whether the user is marked as "validated". 
     */
    public function hasVerifiedSender()
    {
        return (bool) $this->getSfGuardUser()->getIsValidated();
    }

    /**
     * Checks if the User of the EpisodeAssignment has been validated as a
     * member of Reddit yet.
     *
     * @return bool  Whether the user is marked as "validated". 
     */
    public function hasVerifiedRecipient()
    {
        $recipient_id = $this->getRecipientId();
        $user = sfGuardUserTable::getInstance()->find($recipient_id);
        return (bool) ($user && $user->getIsValidated());
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
