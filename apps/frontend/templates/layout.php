<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $sf_user->getCulture() ?>" lang="<?php echo $sf_user->getCulture() ?>">
    <head>
        <?php include_http_metas() ?>
        <?php include_metas() ?>
        <?php include_title() ?>
        <link rel="shortcut icon" href="/favicon.ico" />
        <?php include_stylesheets() ?>
        <?php include_javascripts() ?>
        <script type="text/javascript">  
            AudioPlayer.setup("<?php echo image_path('player.swf'); ?>", {  
                width: 290  
            });  
        </script> 
    </head>
    <body style="background-color: #eee; font-family: Arial, Helvetica, Verdana, sans-serif; font-size: smaller;">
        <div id="central_box" style="width:95%; max-width: 960px; background-color: white; padding: 0 15px 15px 15px; -moz-border-radius: 0px 0px 5px 5px; border-radius: 0px 0px 5px 5px; text-align: center; margin: 0 auto; border-top: 15px black solid; margin-top: -0.5em;">
            <div id="everything" style="text-align: left;">
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
