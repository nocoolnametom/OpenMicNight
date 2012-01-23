<h2 class="orangeredbar">Edit Deadline</h2>

<?php use_stylesheets_for_form($form) ?>
<?php use_javascripts_for_form($form) ?>

<form action="<?php echo url_for('subreddit/updatedeadline?id='.$form->getObject()->getId()) ?>" method="post" <?php $form->isMultipart() and print 'enctype="multipart/form-data" ' ?>>
<input type="hidden" name="sf_method" value="put" />
  <table>
    <tfoot>
      <tr>
        <td colspan="2">
          <?php echo $form->renderHiddenFields(false) ?>
          <a href="<?php echo url_for('subreddit/deadlines?domain=' . $subreddit->getDomain()) ?>">Back to Deadlines</a>
          &nbsp;<a href="<?php echo url_for('subreddit/delete_deadline?id=' . $form->getObject()->getId()) ?>">Delete Deadline</a>
          <input type="submit" value="Save" />
        </td>
      </tr>
    </tfoot>
    <tbody>
      <?php echo $form->renderGlobalErrors() ?>
      <?php echo $form->renderUsing('table'); ?>
    </tbody>
  </table>
</form>