<?php /* @var $form sfGuardUserForm */ ?>
<?php use_stylesheets_for_form($form) ?>
<?php use_javascripts_for_form($form) ?>

<form action="<?php echo url_for('profile/update?id='.$form->getObject()->getId()) ?>" method="post" <?php $form->isMultipart() and print 'enctype="multipart/form-data" ' ?>>
<input type="hidden" name="sf_method" value="put" />
  <table>
    <tfoot>
      <tr>
        <td colspan="2">
          <?php echo $form->renderHiddenFields(false) ?>
          &nbsp;<a href="<?php echo url_for('profile/index') ?>">Back to Profile</a>
          <input type="submit" value="Save" />
        </td>
      </tr>
    </tfoot>
    <tbody>
      <?php echo $form->renderGlobalErrors() ?>
      <?php echo $form->renderUsing('table'); /* ?>
      <tr>
        <th><?php echo $form['sf_guard_user_id']->renderLabel() ?></th>
        <td>
          <?php echo $form['sf_guard_user_id']->renderError() ?>
          <?php echo $form['sf_guard_user_id'] ?>
        </td>
      </tr>
      <tr>
        <th><?php echo $form['audio_file']->renderLabel() ?></th>
        <td>
          <?php echo $form['audio_file']->renderError() ?>
          <?php echo $form['audio_file'] ?>
        </td>
      </tr>
      <tr>
        <th><?php echo $form['nice_filename']->renderLabel() ?></th>
        <td>
          <?php echo $form['nice_filename']->renderError() ?>
          <?php echo $form['nice_filename'] ?>
        </td>
      </tr>
      <tr>
        <th><?php echo $form['graphic_file']->renderLabel() ?></th>
        <td>
          <?php echo $form['graphic_file']->renderError() ?>
          <?php echo $form['graphic_file'] ?>
        </td>
      </tr>
      <tr>
        <th><?php echo $form['is_nsfw']->renderLabel() ?></th>
        <td>
          <?php echo $form['is_nsfw']->renderError() ?>
          <?php echo $form['is_nsfw'] ?>
        </td>
      </tr>
      <tr>
        <th><?php echo $form['title']->renderLabel() ?></th>
        <td>
          <?php echo $form['title']->renderError() ?>
          <?php echo $form['title'] ?>
        </td>
      </tr>
      <tr>
        <th><?php echo $form['description']->renderLabel() ?></th>
        <td>
          <?php echo $form['description']->renderError() ?>
          <?php echo $form['description'] ?>
        </td>
      </tr>
      <tr>
        <th><?php echo $form['approved_at']->renderLabel() ?></th>
        <td>
          <?php echo $form['approved_at']->renderError() ?>
          <?php echo $form['approved_at'] ?>
        </td>
      </tr>
      <tr>
        <th><?php echo $form['file_is_remote']->renderLabel() ?></th>
        <td>
          <?php echo $form['file_is_remote']->renderError() ?>
          <?php echo $form['file_is_remote'] ?>
        </td>
      </tr>
      <tr>
        <th><?php echo $form['remote_url']->renderLabel() ?></th>
        <td>
          <?php echo $form['remote_url']->renderError() ?>
          <?php echo $form['remote_url'] ?>
        </td>
      </tr>
      <tr>
        <th><?php echo $form['reddit_post_url']->renderLabel() ?></th>
        <td>
          <?php echo $form['reddit_post_url']->renderError() ?>
          <?php echo $form['reddit_post_url'] ?>
        </td>
      </tr>
      <?php */ ?>
    </tbody>
  </table>
</form>
