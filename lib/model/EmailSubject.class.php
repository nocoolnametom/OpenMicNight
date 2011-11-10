<?php

/**
 * The EmailBody class contains the Body texts of emails commonly used by the
 * system in a centralized loction accessible to all apps.
 *
 * @author doggetto
 */
class EmailBody
{

    protected $app_name;
    protected $user;
    protected $name;
    protected $output;

    public function prepare($user_id)
    {
        $this->app_name = ProjectConfiguration::getApplicationName();
        $this->user = sfGuardUserTable::getInstance()->find($user_id);
        if (!$this->user)
            throw new sfException('Cannot find User identified by ' . $user_id);
    }

    /**
     *
     * @param int   $user_id
     * @param array $additional_params  Requires a key of 'api_id' with a value
     *                                   of the identifier of the ApiKey object
     * @return string 
     */
    public static function ApiAuthRequest($user_id, $additional_params = array())
    {
        $this->prepare($user_id);
        $api_id = $additional_params['api_id'];
        $api = ApiKeyTable::getInstance()->find($api_id);
        if (!$api)
            throw new sfException('Cannot find ApiKey identified by ' . $api_id);
        $api_name = $api->getApiAppName();
        $this->output = <<<EOF
$this->app_name: Authorization Granted to $api_name
EOF;
        return $this->output;
    }

    public static function ChangeRedditKey($user_id)
    {
        $this->prepare($user_id);
        $this->output = <<<EOF
$this->app_name: Authorize Your Reddit Username
EOF;
        return $this->output;
    }

    public static function EmailNewPassword($user_id, $additional_params = array())
    {
        $this->prepare($user_id);
        $this->output = <<<EOF
$this->app_name:  Your Password Has Been Reset
EOF;
        return $this->output;
    }

    public static function EpisodeApprovalPending($user_id, $additional_params = array())
    {
        $this->prepare($user_id);
        $this->output = <<<EOF
$this->app_name:  Your Episode is Awaiting Approval!
EOF;
        return $this->output;
    }

    public static function NewPrivateMessage($user_id, $additional_params = array())
    {
        $this->prepare($user_id);
        $message_id = $additional_params['message_id'];
        $message = MessageTable::getInstance()->find($message_id);
        if (!$message)
            throw new sfException('Cannot find Message identified by ' . $message_id);
        $sender = sfGuardUserTable::getInstance()->find($message->getSenderId());
        $sender_username = $sender->getUsername();
        $message_text = $message->getText();
        $this->output = <<<EOF
$this->app_name: New Private Message from $sender_username
EOF;
        return $this->output;
    }

    public static function NewlyOpenedEpisode($user_id, $additional_params = array())
    {
        $this->prepare($user_id);
        $this->output = <<<EOF
$this->app_name:  You Have Your Own Episode!
EOF;
        return $this->output;
    }

    public static function RegisterInitial($user_id)
    {
        $this->prepare($user_id);
        $this->output = <<<EOF
Welcome to $this->app_name!
EOF;
        return $this->output;
    }

    public static function RegisterOneDay($user_id)
    {
        $this->prepare($user_id);
        $this->output = <<<EOF
$this->app_name: Finish Your Registration by Verifying Your Reddit Username
EOF;
        return $this->output;
    }

    public static function RegisterOneWeek($user_id)
    {
        $this->prepare($user_id);
        $this->output = <<<EOF
$this->app_name: Last Chance to Finish Your Registration  by Verifying Your Reddit Username!
EOF;
        return $this->output;
    }

    public static function RegisterRedditPost($user_id)
    {
        $this->prepare($user_id);
        $this->output = <<<EOF
$this->app_name: Please Verify Your Reddit Username
EOF;
        return $this->output;
    }

}