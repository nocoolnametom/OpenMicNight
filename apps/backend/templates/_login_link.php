<?php if ($sf_user->isAuthenticated() && $sf_user->isSuperAdmin()): ?>
Welcome, <?php echo $sf_user->getGuardUser() ?>!
<?php echo link_to('Logout', '@sf_guard_signout') ?>
<?php else: ?>
<?php echo link_to('Login', '@sf_guard_signin') ?>
<?php endif; ?>
