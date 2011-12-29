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
}

?>
<?php if ($display): ?>
<div id="feedback_link" style="position: fixed; clear: both; max-height: 30px; bottom: 0; left: 30; border: 2px solid white; box-shadow: 2px 3px 10px #000; border-bottom: 0; text-align: center; font-weight: bolder; color: white; background-color: orangered; -moz-border-radius: 5px 5px 0px 0px; border-radius: 5px 5px 0px 0px;">
    <?php include_partial('global/feedback_link', array(
        'feedback_text' => $feedback_text,
        'link_style' => "color: white; text-decoration: none; text-shadow:1px 1px 3px #000000; padding: 7px 10px 5px 10px; display: block;")) ?>
</div>
<?php endif; ?>