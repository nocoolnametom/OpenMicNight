<?php
/* @var $user sfGuardUser */
/* @var $message Message */
/* @var $sender sfGuardUser */ ?>
<p> Dear <?php echo $name ?>,</p>

<p>You have been sent the following private message to your account on
<?php echo $app_name; ?> by the user
<?php echo $sender->getUsername(); ?>:</p>
<blockquote><?php echo $message->getText() ?></blockquote>

<p>To view this message or reply to it, log into <?php $app_name ?> and view
    your Messages.</p>

<p>Sincerely,<br/> The <?php echo $app_name; ?> Team</p>