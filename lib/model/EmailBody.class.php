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
        $this->name = ($this->user->getPreferredName() ?
                        $this->user->getPreferredName() : $this->user->getFullName());
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
<p>Dear $this->name,</p>

<p>This is just a notice to inform you that you have authorized $api_name with
   your user credentials to access $this->app_name.  If this is incorrect,
   <i>please</i> let us know by responding to this email!  You can also revoke
   this authorized in your user preferences at $this->app_name. If this
   authorization is approved, feel free to disregard this email.</p>

<p>Sincerely,<br/> The $this->app_name Team</p>
EOF;
        return $this->output;
    }

    public static function ChangeRedditKey($user_id)
    {
        $this->prepare($user_id);
        $valid_key = $this->user->getRedditValidationKey();
        $reddit_post = sfConfig::get('app_email_reddit_validation_post_location');
        if (sfConfig::get('app_email_reddit_validation_local_file', false)) {
            $app_pattern = '/~([^~]+)~/';
            $app_location = preg_match($app_pattern, $reddit_post, $matches);
            $app_location = $app_location[1];
            $reddit_post = preg_replace($app_pattern, sfConfig::get($app_location), $reddit_post);
        }
        $this->output = <<<EOF
<p>Dear $this->name,</p>

<p>You need to authorize your Reddit username.  This is because you have either
    changed the email address or password of your account, or because you have
    not previously authorized your username since registering for
    $this->app_name.</p>

<p>You can verify your Reddit username by responding with the following Reddit
    activation key:</p>
<blockquote>$valid_key</blockquote>
<p> as a reply to the following post in the $this->app_name Subreddit:</p>
<blockquote>$reddit_post</blockquote>

<p>Once you've posted your key, verification shouldn't take more than an hour
    or two.  We'll do our best to let you know if there are any problems that
    might take longer.</p>

<p>Sincerely,<br/> The $this->app_name Team</p>
EOF;
        return $this->output;
    }

    public static function EmailNewPassword($user_id, $additional_params = array())
    {
        $this->prepare($user_id);
        $new_password = $additional_params['new_password'];
        $valid_key = $this->user->getRedditValidationKey();
        $this->output = <<<EOF
<p>Dear $this->name,</p>

<p>You're receiving this email because you have indicated that you have
   forgotten your password to $this->app_name.  We do not store passwords in a
   recoverable format, so we cannot tell you what your password was.  This helps
   keep $this->app_name secure and safe.  We have changed your password to the
   following:</p>

<blockquote>$new_password</blockquote>

<p>Please log in using your email address and this password.  You can change
   your password once logged into $this->app_name to your preferred password for
   the application.</p>

<p>If you think you have received this email by mistake and are concerned that
   your password has been changed, please let us know by replying to this
   email.</p>

<p>Sincerely,<br/> The $this->app_name Team</p>
EOF;
        return $this->output;
    }

    public static function EpisodeApprovalPending($user_id, $additional_params = array())
    {
        $this->prepare($user_id);
        $episode_id = $additional_params['episode_id'];
        $episode = EpisodeTable::getInstance()->find($episode_id);
        if (!$episode)
            throw new sfException('Cannot find Episode identified by ' . $episode_id);
        $subreddit_name = $episode->getSubreddit()->getName();
        $this->output = <<<EOF
<p>Dear $this->name,</p>

<p>There is an episode that has been submitted for approval in the
   $subreddit_name subreddit.  Please take some time to listen to this episode
   and determine if you feel you can approve it. Remember that all episodes must
   be submitted by users who own or adhere to rules governing the rights to all
   content contained within.  Creative Commons content should be correctly
   attributed, and it's safer to avoid relying on "fair use" defenses by
   avoiding the use of copyrighted materials altogether, though it's up to you
   as an approver to decide that.  Remember that it your approval of this
   episode means that it complies with the aims of your subreddit as well as
   copyright laws.</p>

<p>Please take the time needed to approve this episode or to send it back to the
    submitter for further work.  Be honest and be fair.   There's a lot of
    responsibility resting upon you, but you know that you're up to it; you
    wouldn't be a moderator otherwise.  Have fun, and help someone else give
    their voice to the world!</p>

<p>Sincerely,<br/> The $this->app_name Team</p>
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
<p>Dear $this->name,</p>

<p>You have been sent the following private message to your account on
   $this->app_name by the user $sender_username:</p>
<blockquote>$message_text</blockquote>

<p>To view this message or reply to it, log into $this->app_name and view your
   Messages.</p>

<p>Sincerely,<br/> The $this->app_name Team</p>
EOF;
        return $this->output;
    }

    public static function NewlyOpenedEpisode($user_id, $additional_params = array())
    {
        $this->prepare($user_id);
        $episode_id = $additional_params['episode_id'];
        $deadline = $additional_params['deadline'];
        $episode = EpisodeTable::getInstance()->find($episode_id);
        $deadline = strtotime($deadline);
        if (!$message)
            throw new sfException('Cannot find Episode identified by ' . $episode_id);
        $release_date = $episode->getReleaseDate('l, F n, Y \a\t g:ia');
        $deadline_date = date('l, F n, Y \a\t g:ia', $deadline);
        $this->output = <<<EOF
<p>Dear $this->name,</p>

<p>You have been successfully paired with an empty episode slot!  Your episode
    will air on $release_date.  Hurry and put something together and submit it
    for approval!  You need to ensure that your episode has been submitted and
    approval before $deadline_date.  If you miss your deadline, your episode
    will not be aired, and someone else may take your spot.</p>

<p>If you'd rather not submit an episode, feel free to just wait it out and let
    your episode spot roll over to someone else.  There's no pressure on this;
    the only person in charge of this is you!</p>

<p>Have fun!</p>

<p>Sincerely,<br/> The $this->app_name Team</p>
EOF;
        return $this->output;
    }

    public static function RegisterInitial($user_id)
    {
        $this->prepare($user_id);
        $authorization_key = $user->getEmailAuthorizationKey();

        $frontend_app_location = ProjectConfiguration::getFrontendAppLocation();
        $frontendRouting = new sfPatternRouting(new sfEventDispatcher());

        $config = new sfRoutingConfigHandler();
        $routes = $config->evaluate(array(sfConfig::get('sf_apps_dir') . '/frontend/config/routing.yml'));

        $frontendRouting->setRoutes($routes);

        $frontend_route = $frontend_app_location . $frontendRouting
                        ->generate('@sf_guard_verify', array(
                            'key' => $authorization_key,
                        ));

        $this->output = <<<EOF
<p>Welcome to $this->app_name!</p>

<p>You're almost ready to participate.  We need to verify your email address by
    having you visit the following web address: <br/>
    <blockquote><a href="$frontend_route">$frontend_route</a></blockquote></p>
<p>This will be your only email containing this web address so don't lose it
    before you visit that link!</p>

<p>Thanks,<br/> The $this->app_name Team</p>
EOF;
        return $this->output;
    }

    public static function RegisterOneDay($user_id)
    {
        $this->prepare($user_id);
        $reddit_key = $user->getRedditValidationKey();
        $this->output = <<<EOF
<p>Dear $this->name,</p>

<p>We're hoping you still want to participate in $this->app_name.  The only
   thing standing in your way is to verify your Reddit username by responding
   with your Reddit activation key:</p>
<blockquote>$reddit_key</blockquote>
<p> as a reply to the following post in the $this->app_name Subreddit:</p>
<blockquote><?php echo $reddit_post; ?></blockquote>

<p>Once you've posted your key, verification shouldn't take more than an hour
    or two.  We'll do our best to let you know if there are any problems that
    might take longer.</p>

<p>We hope you enjoy using $this->app_name!  Have fun!</p>

<p>Sincerely,<br/> The $this->app_name Team</p>
EOF;
        return $this->output;
    }

    public static function RegisterOneWeek($user_id)
    {
        $this->prepare($user_id);
        $reddit_key = $user->getRedditValidationKey();
        $this->output = <<<EOF
<p>Dear $this->name,</p>

<p>It's been a week since you registered for an account at $this->app_name.  You
   still need to verify your Reddit username, however.  You can do this by
   posting your Reddit activation key:</p>
<blockquote>$reddit_key</blockquote>
<p> as a reply to the following post in the $this->app_name Subreddit:</p>
<blockquote><?php echo $reddit_post; ?></blockquote>

<p>Once you've posted your key, verification shouldn't take more than an hour or
   two.  We'll do our best to let you know if there are any problems that might
   take longer.</p>

<p>Since it's been a week, we should let you know that this will be the last 
   email sent to you about verifying your Reddit account.  We don't want to
   bother you, so we'll back off.  If you need to try again in the future,
   you'll be able to start this process off again from within your user
   preferences at $this->app_name.</p>

<p>We hope you'll join us soon!</p>

<p>Sincerely,<br/> The $this->app_name Team</p>
EOF;
        return $this->output;
    }

    public static function RegisterRedditPost($user_id)
    {
        $this->prepare($user_id);
        $reddit_key = $user->getRedditValidationKey();
        $this->output = <<<EOF
<p>Dear $this->name,</p>

<p>Welcome to $this->app_name!</p>

<p>You're almost ready to participate (we promise!).  Your final step is to
    paste in the following key:</p>
<blockquote>$reddit_key</blockquote>
<p> as a reply to the following post in the $this->app_name Subreddit:</p>
<blockquote><?php echo $reddit_post; ?></blockquote>

<p>Once you've posted your reply, verification shouldn't take more than an hour
    or two.  We'll do our best to let you know if there are any problems that
    might take longer.</p>

<p>We hope you enjoy using $this->app_name!  Have fun!</p>

<p>Sincerely,<br/> The $this->app_name Team</p>
EOF;
        return $this->output;
    }

}