User: <?php echo $user; ?><br/>

<?php echo ($sf_user->isAuthenticated() ? link_to('Logout', '@sf_guard_signout') : link_to('Login', '@sf_guard_signin')); ?>