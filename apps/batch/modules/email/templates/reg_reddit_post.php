<?php /* @var $user sfGuardUser */ ?>
<p> Dear <?php echo $user->getFullName() ?>,</p>

<p>Welcome to <?php echo $app_name; ?>!</p>

<p>You're almost ready to participate (we promise!).  Your final step is to
    paste in the following key:</p>
<blockquote><?php echo $user->getRedditValidationKey() ?></blockquote>
<p> as a reply to the following post in the <?php echo $app_name; ?>
    Subreddit:</p>
<blockquote><?php echo $reddit_post; ?></blockquote>

<p>Once you've posted your reply, verification shouldn't take more than an hour
    or two.  We'll do our best to let you know if there are any problems that
    might take longer.</p>

<p>We hope you enjoy using <?php echo $app_name; ?>!  Have fun!</p>

<p>Sincerely,<br/> The <?php echo $app_name; ?> Team</p>