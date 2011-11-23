<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $sf_user->getCulture() ?>" lang="<?php echo $sf_user->getCulture() ?>">
  <head>
    <?php include_http_metas() ?>
    <?php include_metas() ?>
    <?php include_title() ?>
    <link rel="shortcut icon" href="/favicon.ico" />
    <?php include_stylesheets() ?>
    <?php include_javascripts() ?>
  </head>
  <body>
    <div class="notice"><?php echo $sf_user->getFlash('notice'); ?></div>
    <?php if ($sf_user->hasFlash('login')): ?>
    <?php foreach($sf_user->getFlash('login') as $login_message): ?>
    <div class="notice"><?php echo $login_message; ?></div>
    <?php endforeach; ?>
    <?php endif; ?>
    <div class="error" style="color:red;"><?php echo $sf_user->getFlash('error'); ?></div>
    <?php echo $sf_content ?>
  </body>
</html>
