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
<div id="feedback_link">
    <?php include_partial('global/feedback_link', array(
        'feedback_text' => $feedback_text,
        'class' => 'white_text',
        )) ?>
</div>
<?php endif; ?>