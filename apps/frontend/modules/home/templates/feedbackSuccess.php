<?php use_helper('I18N', 'recaptcha') ?>

<h2 class="orangeredbar"><?php echo __('Send Feedback', null, 'home') ?></h2>

<?php echo form_tag('@feedback_send'); ?>
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
<?php echo '</form>' ?>