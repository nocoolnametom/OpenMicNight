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
    
    public function setIncremented($id)
    {
        $this->_id = array($id);
        $this->set('id', $id, false);
        $this->_lastModified = array();
    }

    public function save(Doctrine_Connection $conn = null)
    {
        if (is_null($this->_get('username')) && is_null($this->_get('email_address'))) {
            return;//throw new sfException('Cannot save User with null username and email!');
        }
        if ($this->isNew() && sfGuardUserTable::getIfValidatedUserHasUsername($this->_get('username'))) {
            throw new sfException('Cannot save user.  This username has already been validated with another user.');
        }
        if (!$this->isNew() && in_array('is_validated', $this->_modified) && !$this->_get('is_validated')) {
            /* The user has been un-validated, probably due to changing their
             * Reddit validation key by username or password.  We need to send
             * them an email about it.
             */
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

            $from = sfConfig::get('app_email_address', ProjectConfiguration::getApplicationName() . ' <' .ProjectConfiguration::getApplicationEmailAddress() . '>');
            
            AppMail::sendMail($address, $from, $subject, $body, $prefer_html ? $body : null);
            
            $this->addLoginMessage('You have changed information relating to your Reddit user and will need to validate your Reddit username again.  Please see your email for more information.');
        }
        parent::save($conn);
    }
    
    public function getUndisplayedLoginMessages()
    {
        return sfGuardLoginMessageTable::getInstance()->getUndisplayedByUserId($this->getIncremented());
    }

    /**
     * Adds a login message to be displayed the next time the user logs in.
     *
     * @param string $message
     */
    public function addLoginMessage($message)
    {
        $login_message = new sfGuardLoginMessage();
        $login_message->setUserId($this->getIncremented());
        $login_message->setDisplayed(false);
        $login_message->setMessage($message);
        $login_message->save();
    }
    
    /**
     * Returns all auth keys for the users ignoring any given API key(s).
     *
     * @param string|array $excluded_api_keys  The ignored API key(s)
     * @return Doctrine_Collection 
     */
    public function getAuthKeysExcluding($excluded_api_keys)
    {
        if (!is_array($excluded_api_keys))
            $excluded_api_keys = array($excluded_api_keys);
        $auth_keys = sfGuardUserAuthKeyTable::getInstance()->getKeysByUserIdExcludingApiKeys($this->getIncremented(), $excluded_api_keys);
        return $auth_keys;
    }

}
