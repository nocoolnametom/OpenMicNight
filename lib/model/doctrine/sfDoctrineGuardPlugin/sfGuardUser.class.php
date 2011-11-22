<?php

/**
 * sfGuardUser
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    OpenMicNight
 * @subpackage model
 * @author     Tom Doggett
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class sfGuardUser extends PluginsfGuardUser
{

    public function save(Doctrine_Connection $conn = null)
    {
        if (is_null($this->_get('username')) && is_null($this->_get('email_address'))) {
            throw new sfException('Cannot save User with null username and email!');
        }
        if ($this->isNew() && sfGuardUserTable::getIfValidatedUserHasUsername($this->_get('username')))
        {
            throw new sfException('Cannot save user.  This username has already been validated with another user.');
        }
        if (!$this->isNew() && in_array('is_validated', $this->_modified) && !$this->_get('is_validated')) {
            /* The user has been un-validated, probably due to changing their
             * Reddit validation key by username or password.  We need to send
             * them an email about it.
             */
            ProjectConfiguration::registerZend();
            $mail = new Zend_Mail();
            $mail->addHeader('X-MailGenerator', ProjectConfiguration::getApplicationName());
            $parameters = array(
                'user_id' => $this->getIncremented(),
            );

            $prefer_html = $this->getPreferHtml();
            $address = $this->getEmailAddress();
            $name = ($this->getPreferredName() ?
                            $this->getPreferredName() : $this->getFullName());

            $email = EmailTable::getInstance()->getFirstByEmailTypeAndLanguage('ChangeRedditKey', $this->getPreferredLanguage());

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
        parent::save($conn);
    }

}
