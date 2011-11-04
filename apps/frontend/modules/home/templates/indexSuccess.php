User: <?php echo $user; ?><br/>

<?php echo ($sf_user->isAuthenticated() ? link_to('Logout', 'sfGuardAuth/signout') : link_to('Login', 'sfGuardAuth/signin')); ?>