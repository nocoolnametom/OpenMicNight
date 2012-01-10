<h1>Edit Episode</h1>
<?php if (strtotime($form->getObject()->getReleaseDate()) > time()): ?>
    <?php $submitted_at = date("g:ia, D j M Y", strtotime($form->getObject()->getSubmittedAt())); ?>
    <?php $approved_at = date("g:ia, D j M Y", strtotime($form->getObject()->getApprovedAt())); ?>
    <?php echo ($is_approved ? "<div class=\"status\">Approved ($approved_at)</div>" : '<div class="deadline">Deadline for Submission and Approval: ' . date("g:ia, D j M Y", $deadline) . '</div>'); ?>
    <?php echo ($is_submitted ? "<div class=\"status\">Submitted For Approval @ $submitted_at</div>" : ''); ?>
<?php endif; ?>
<?php
if (count($phone_numbers)) {
    include_partial('phone_numbers', array(
        'form' => $form,
        'phone_numbers' => $phone_numbers,
    ));
}
?>
<?php
include_partial('form', array(
    'form' => $form,
    'is_submitted' => $is_submitted,
    'is_approved' => $is_approved,
    'is_admin' => $is_admin,
    'graphic_hash' => $graphic_hash,
    'audio_hash' => $audio_hash,
))
?>
