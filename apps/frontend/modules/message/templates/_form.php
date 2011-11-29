<?php use_stylesheets_for_form($form) ?>
<?php use_javascripts_for_form($form) ?>

<form action="<?php echo url_for('message/'.(is_null($form->getObject()->getId()) ? 'create' : 'update').(!is_null($form->getObject()->getId()) ? '?id='.$form->getObject()->getId() : '')) ?>" method="post" <?php $form->isMultipart() and print 'enctype="multipart/form-data" ' ?>>
<?php if (!is_null($form->getObject()->getId())): ?>
<input type="hidden" name="sf_method" value="put" />
<?php endif; ?>
  <table>
    <tfoot>
      <tr>
        <td colspan="2">
          <?php echo $form->renderHiddenFields(false) ?>
          &nbsp;<a href="<?php echo url_for('message/index') ?>">Back to list</a>
          <?php if (!is_null($form->getObject()->getId())): ?>
            &nbsp;<?php echo link_to('Delete', 'message/delete?id='.$form->getObject()->getId(), array('method' => 'delete', 'confirm' => 'Are you sure?')) ?>
          <?php endif; ?>
          <input type="submit" value="Save" />
        </td>
      </tr>
    </tfoot>
    <tbody>
      <?php echo $form->renderGlobalErrors() ?>
      <?php if (is_null($form->getObject()->getId())): ?>
      <tr>
        <th><?php echo $form['recipient_id']->renderLabel() ?></th>
        <td>
          <?php echo $form['recipient_id']->renderError() ?>
          <?php echo $form['recipient_id'] ?>
        </td>
      </tr>
      <?php endif; ?>
      <tr>
        <th><?php echo $form['sender_id']->renderLabel() ?></th>
        <td>
          <?php echo $form['sender_id']->renderError() ?>
          <?php echo $form['sender_id'] ?>
        </td>
      </tr>
      <tr>
        <th><?php echo $form['previous_message_id']->renderLabel() ?></th>
        <td>
          <?php echo $form['previous_message_id']->renderError() ?>
          <?php echo $form['previous_message_id'] ?>
        </td>
      </tr>
      <tr>
        <th><?php echo $form['text']->renderLabel() ?></th>
        <td>
          <?php echo $form['text']->renderError() ?>
          <?php echo $form['text'] ?>
        </td>
      </tr>
    </tbody>
  </table>
</form>
