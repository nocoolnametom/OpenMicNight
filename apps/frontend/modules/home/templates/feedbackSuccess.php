<?php use_helper('I18N', 'recaptcha') ?>

<h2><?php echo __('Send Feedback', null, 'home') ?></h2>

<form action="<?php echo url_for('home/send') ?>" method="POST">
    <table>
        <?php echo $form ?>
        <tr>
            <td colspan="2">
                <?php
                if (sfConfig::get('app_recaptcha_active', false)) {
                    echo recaptcha_get_html(sfConfig::get('app_recaptcha_publickey'), $form['response']->getError(), false, true);
                }
                ?>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <input type="submit" />
            </td>
        </tr>
    </table>
</form>