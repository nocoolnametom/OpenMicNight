<h1>Add Deadline</h1>

<?php use_stylesheets_for_form($form) ?>
<?php use_javascripts_for_form($form) ?>

<form action="<?php echo url_for('subreddit/updatedeadline?subreddit_id='.$subreddit_id) ?>" method="post" <?php $form->isMultipart() and print 'enctype="multipart/form-data" ' ?>>
  <table>
    <tfoot>
      <tr>
        <td colspan="2">
          <?php echo $form->renderHiddenFields(false) ?>
          <a href="<?php echo url_for('subreddit/deadlines?id=' . $subreddit->getDomain()) ?>">Back to Deadlines</a>
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