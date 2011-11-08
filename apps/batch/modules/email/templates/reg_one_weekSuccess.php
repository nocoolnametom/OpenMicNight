<?php /* @var $user sfGuardUser */ ?>
<p> Dear <?php echo $name ?>,</p>

<p>It's been a week since you registered for an account at
<?php echo $app_name; ?>.  You still need to verify your Reddit username,
howwever.  You can do this by posting your Reddit activation key:</p>
<blockquote><?php echo $user->getRedditValidationKey() ?></blockquote>
<p> as a reply to the following post in the <?php echo $app_name; ?>
    Subreddit:</p>
<blockquote><?php echo $reddit_post; ?></blockquote>

<p>Once you've posted your key, verification shouldn't take more than an hour
    or two.  We'll do our best to let you know if there are any problems that
    might take longer.</p>

<p>Since it's been a week, we should let you know that this will be the last
    email sent to you about verifying your Reddit account.  We don't want to
    bother you, so we'll back off.  If you need to try again in the future,
    you'll be able to start this process off again from within your user
    preferences at <?php echo $app_name ?>.</p>

<p>We hope you'll join us soon!</p>

<p>Sincerely,<br/> The <?php echo $app_name; ?> Team</p>