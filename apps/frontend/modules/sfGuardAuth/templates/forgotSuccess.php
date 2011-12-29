<?php use_helper('I18N', 'recaptcha') ?>
<h2><?php echo __('Forgot your password?', null, 'sf_guard') ?></h2>
<p>
<?php echo __('Fill out the form with your email address to reset your password.', null, 'sf_guard') ?>
</p>

<form action="<?php echo url_for('@sf_guard_forgot_password') ?>" method="post">
    <table>
        <tbody>
<?php echo $form ?>
            <tr><td colspan="2">
                    <?php
if (sfConfig::get('app_recaptcha_active', false)) {
    echo recaptcha_get_html(sfConfig::get('app_recaptcha_publickey'), $form['response']->getError(), false, true);
}
?>
                </td></tr>
        </tbody>
        <tfoot><tr><td><input type="submit" name="change" value="<?php echo __('Request', null, 'sf_guard') ?>" /></td></tr></tfoot>
    </table>
</form>