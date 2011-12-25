<div id="notifications" style="width: 90%; max-width: 900px; margin-left: 30px; font-weight: bolder;">
    <?php if ($sf_user->hasFlash('notice')): ?>
     <div class="notification notice" style="background-color: wheat; margin: 10px; width: 100%; -moz-border-radius: 5px; border-radius: 5px; padding: 5px; vertical-align: middle;">
         <?php echo $sf_user->getFlash('notice'); ?>
    </div>
    <?php endif; ?>
    <?php include_partial('global/email_link'); ?>
    <?php if ($sf_user->hasFlash('login')): ?>
        <?php foreach ($sf_user->getFlash('login') as $login_message): ?>
            <div class="notification login messages" style="background-color: #a6c9e2; margin: 10px; width: 100%; -moz-border-radius: 5px; border-radius: 5px; padding: 5px; vertical-align: middle;"><?php echo $login_message; ?></div>
        <?php endforeach; ?>
    <?php endif; ?>
    <?php if ($sf_user->hasFlash('error')): ?> 
        <div class="notification error" style="background-color: #EE5757; color: white; margin: 10px; width: 100%; -moz-border-radius: 5px; border-radius: 5px; padding: 5px; vertical-align: middle;"><?php echo $sf_user->getFlash('error'); ?></div>
    <?php endif; ?>
</div>