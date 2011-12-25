<div id="notifications" style="width: 90%; max-width: 900px; margin-left: 30px;">
        <?php if ($sf_user->hasFlash('notice')): ?>
         <div class="notification notice" style="background-color: wheat; margin: 10px; width: 100%; -moz-border-radius: 5px; border-radius: 5px; padding: 5px; vertical-align: middle;">
                 <?php echo $sf_user->getFlash('notice'); ?>
        </div>
        <?php endif; ?>
        <?php include_partial('global/email_link'); ?>
        <?php if ($sf_user->hasFlash('login')): ?>
            <?php foreach ($sf_user->getFlash('login') as $login_message): ?>
                <div class="notification login messages" style="background-color: #a6c9e2;"><?php echo $login_message; ?></div>
            <?php endforeach; ?>
        <?php endif; ?>
        <div class="notification error" style="background-color: #EE5757;"><?php echo $sf_user->getFlash('error'); ?></div>
</div>