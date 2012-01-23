<div id="notifications">
    <?php if ($sf_user->hasFlash('notice')): ?>
        <div class="notification notice">
            <?php echo $sf_user->getFlash('notice'); ?>
        </div>
    <?php endif; ?>
    <?php include_partial('global/email_link'); ?>
    <?php if ($sf_user->hasFlash('login')): ?>
        <?php foreach ($sf_user->getFlash('login') as $login_message): ?>
            <div class="notification login_messages">
                <?php echo $login_message; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    <?php if ($sf_user->hasFlash('error')): ?> 
        <div class="notification error">
            <?php echo $sf_user->getFlash('error'); ?>
        </div>
    <?php endif; ?>
</div>