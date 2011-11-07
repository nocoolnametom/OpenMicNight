<?php /* @var $user sfGuardUser */ ?>
<p> Dear <?php echo $user->getFullName(); ?>,</p>

<p>We're hoping you still want to participate in <?php echo $app_name; ?>.  The
    only thing standing in your way is to verify your Reddit username by
    responding with your Reddit activation key:</p>
<blockquote><?php echo $user->getRedditValidationKey() ?></blockquote>
<p> as a reply to the following post in the <?php echo $app_name; ?>
    Subreddit:</p>
<blockquote><?php echo $reddit_post; ?></blockquote>

<p>Once you've posted your key, verification shouldn't take more than an hour
    or two.  We'll do our best to let you know if there are any problems that
    might take longer.</p>

<p>We hope you enjoy using <?php echo $app_name; ?>!  Have fun!</p>

<p>Sincerely,<br/> The <?php echo $app_name; ?> Team</p>