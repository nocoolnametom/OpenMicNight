<?php use_helper('I18N') ?>
<h1><?php echo __('Register', null, 'sf_guard') ?></h1>

<form action="<?php echo url_for('@sf_guard_register') ?>" method="post">
  <table>
    <?php echo $form ?>
    <tfoot>
      <tr>
        <td colspan="2">
          <input type="submit" name="register" value="<?php echo __('Register', null, 'sf_guard') ?>" />
        </td>
      </tr>
    </tfoot>
  </table>
</form>