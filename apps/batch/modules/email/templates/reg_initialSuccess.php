<?php /* @var $user sfGuardUser */ ?>
<p>Welcome to <?php echo $app_name; ?>!</p>

<p>You're almost ready to participate.  We need to verify your email address by
    having you visit the following web address: <br/>
    <blockquote><?php echo $user->getEmailAuthorizationKey()?></blockquote></p>
<p>This will be your only email containing this web address so don't lose it
    before you visit that link!</p>

<p>Thanks!</br>
    The <?php echo $app_name; ?> Team
</p>

