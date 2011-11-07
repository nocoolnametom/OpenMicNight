<?php /* @var $user sfGuardUser */ ?>
<p> Dear <?php echo $user->getFullName(); ?>,</p>

<p>You need to authorize your Reddit username.  This is because you have either
    changed the email address or password of your account, or because you have
    not previously authorized your username since registering for
<?php echo $app_name ?>.</p>

<p>You can verify your Reddit username by responding with the following Reddit
    activation key:</p>
<blockquote><?php echo $user->getRedditValidationKey() ?></blockquote>
<p> as a reply to the following post in the <?php echo $app_name; ?>
    Subreddit:</p>
<blockquote><?php echo $reddit_post; ?></blockquote>

<p>Once you've posted your key, verification shouldn't take more than an hour
    or two.  We'll do our best to let you know if there are any problems that
    might take longer.</p>

<p>Sincerely,<br/> The <?php echo $app_name; ?> Team</p>