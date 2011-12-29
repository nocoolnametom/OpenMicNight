<?php
$module = sfContext::getInstance()->getModuleName();
$action = sfContext::getInstance()->getActionName();

if ($module == 'home'
        && ($action == 'feedback'
        || $action == 'send'
        || $action == 'thankyou')) {
    $display = false;
} else {
    $display = true;
    $class = $sf_user->getApiUserId() ? 'fancybox fancybox.ajax' : '';
}

?>
<?php if ($display): ?>
<div id="feedback_link" style="position: fixed; clear: both; max-height: 30px; bottom: 0; left: 30; border: 2px solid white; box-shadow: 2px 3px 10px #000; border-bottom: 0; text-align: center; font-weight: bolder; color: white; background-color: orangered; -moz-border-radius: 5px 5px 0px 0px; border-radius: 5px 5px 0px 0px;">
    <a href="<?php echo url_for('@feedback') ?>" class="<?php echo $class; ?>" style="color: white; text-decoration: none; text-shadow:1px 1px 3px #000000; padding: 7px 10px 5px 10px; display: block;">Feedback</a>
</div>
<?php endif; ?>