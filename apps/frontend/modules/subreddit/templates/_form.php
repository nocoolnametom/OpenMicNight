<?php use_stylesheets_for_form($form) ?>
<?php use_javascripts_for_form($form) ?>

<form action="<?php echo url_for('subreddit/'.(is_null($form->getObject()->getId()) ? 'create' : 'update').(!is_null($form->getObject()->getId()) ? '?id='.$form->getObject()->getId() : '')) ?>" method="post" <?php $form->isMultipart() and print 'enctype="multipart/form-data" ' ?>>
<?php if (!is_null($form->getObject()->getId())): ?>
<input type="hidden" name="sf_method" value="put" />
<?php endif; ?>
  <table>
    <tfoot>
      <tr>
        <td colspan="2">
          <?php echo $form->renderHiddenFields(false) ?>
          &nbsp;<a href="<?php echo url_for('subreddit/index') ?>">Back to list</a>
          <?php if (!is_null($form->getObject()->getId())): ?>
            &nbsp;<?php echo ($sf_user->isSuperAdmin() ? link_to('Delete', 'subreddit/delete?id='.$form->getObject()->getId(), array('method' => 'delete', 'confirm' => 'Are you sure?')) : '') ?>
          <?php endif; ?>
          <input type="submit" value="Save" />
        </td>
      </tr>
    </tfoot>
    <tbody>
      <?php echo $form->renderGlobalErrors() ?>
      <?php echo $form->renderUsing('table'); /* ?>
      <tr>
        <th><?php echo $form['name']->renderLabel() ?></th>
        <td>
          <?php echo $form['name']->renderError() ?>
          <?php echo $form['name'] ?>
        </td>
      </tr>
      <tr>
        <th><?php echo $form['domain']->renderLabel() ?></th>
        <td>
          <?php echo $form['domain']->renderError() ?>
          <?php echo $form['domain'] ?>
        </td>
      </tr>
      <tr>
        <th><?php echo $form['is_active']->renderLabel() ?></th>
        <td>
          <?php echo $form['is_active']->renderError() ?>
          <?php echo $form['is_active'] ?>
        </td>
      </tr>
      <tr>
        <th><?php echo $form['create_new_episodes_cron_formatted']->renderLabel() ?></th>
        <td>
          <?php echo $form['create_new_episodes_cron_formatted']->renderError() ?>
          <?php echo $form['create_new_episodes_cron_formatted'] ?>
        </td>
      </tr>
      <tr>
        <th><?php echo $form['episode_schedule_cron_formatted']->renderLabel() ?></th>
        <td>
          <?php echo $form['episode_schedule_cron_formatted']->renderError() ?>
          <?php echo $form['episode_schedule_cron_formatted'] ?>
        </td>
      </tr>
      <tr>
        <th><?php echo $form['creation_interval']->renderLabel() ?></th>
        <td>
          <?php echo $form['creation_interval']->renderError() ?>
          <?php echo $form['creation_interval'] ?>
        </td>
      </tr>
      <tr>
        <th><?php echo $form['bucket_name']->renderLabel() ?></th>
        <td>
          <?php echo $form['bucket_name']->renderError() ?>
          <?php echo $form['bucket_name'] ?>
        </td>
      </tr>
      <?php */ ?>
    </tbody>
  </table>
</form>
