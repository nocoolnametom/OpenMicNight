<h1>Edit Episode</h1>
<?php echo ($is_submitted ? 'Submitted' : '') ?>
<?php echo ($is_approved ? 'Approved' : '') ?>
<?php include_partial('form', array('form' => $form)) ?>
