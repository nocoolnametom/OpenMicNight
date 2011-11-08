<?php

/**
 * sfGuardRegisterForm for registering new users
 *
 * @package    sfDoctrineGuardPlugin
 * @subpackage form
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: BasesfGuardChangeUserPasswordForm.class.php 23536 2009-11-02 21:41:21Z Kris.Wallsmith $
 */
class sfGuardRegisterForm extends BasesfGuardRegisterForm
{

    /**
     * @see sfForm
     */
    public function configure()
    {
        unset(
                $this['is_validated'],
                $this['reddit_validation_key'],
                $this['is_authorized'],
                $this['email_authorization_key'],
                $this['authorized_at'],
                //$this['full_name'],
                //$this['preferred_name'],
                $this['website'],
                $this['twitter_account'],
                $this['avatar'],
                $this['short_bio'],
                $this['prefer_html'],
                //$this['address_line_one'],
                //$this['address_line_two'],
                //$this['city'],
                //$this['state'],
                //$this['zip_code'],
                //$this['country'],
                $this['display_location'],
                $this['receive_private_messages_from_profile_page'],
                $this['receive_notification_of_private_messages'],
                $this['receive_notification_of_newly_opened_episodes'],
                $this['receive_notification_of_episode_approval_pending']
        );
        
        $this->widgetSchema['username']->setLabel('Reddit Username');
    }
}