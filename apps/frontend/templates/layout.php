<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $sf_user->getCulture() ?>" lang="<?php echo $sf_user->getCulture() ?>">
    <head>
        <?php include_http_metas() ?>
        <?php include_metas() ?>
        <?php include_title() ?>
        <link rel="shortcut icon" href="/favicon.ico" />
        <?php include_stylesheets() ?>
        <?php include_javascripts() ?>
        <?php if (has_slot('atom_feed')): ?>
            <?php include_slot('atom_feed') ?>
        <?php endif; ?>
        <script type="text/javascript">  
            AudioPlayer.setup("<?php echo image_path('player.swf'); ?>", {  
                width: 290  
            });  
        </script>
        <script>
            $(document).ready(function() {
                $('.fancybox').fancybox();
            });
        </script>
    </head>
    <body>
        <?php include_partial('global/feedback_button', array('feedback_text' => 'Send Feedback')); ?>
        <div id="central_box">
            <div id="everything">
                <?php include_partial('global/header'); ?>
                <?php include_partial('global/notifications'); ?>
                <div id="content">
                    <?php echo $sf_content ?>
                </div>
            </div>
        </div>
        <?php include_partial('global/footer'); ?>
    </body>
</html>
