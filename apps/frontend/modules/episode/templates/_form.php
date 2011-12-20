<?php use_stylesheets_for_form($form) ?>
<?php use_javascripts_for_form($form) ?>

<form action="<?php echo url_for('episode/update?id=' . $form->getObject()->getId()) ?>" method="post" <?php $form->isMultipart() and print 'enctype="multipart/form-data" ' ?>>
    <input type="hidden" name="sf_method" value="put" />
    <table>
        <tfoot>
            <tr>
                <td colspan="2">
                    <?php echo $form->renderHiddenFields(false) ?>
                    &nbsp;<a href="<?php echo url_for('profile/episodes') ?>">Back to Episodes</a>
                    &nbsp;<?php echo (($is_submitted ? '<span class="submitted">Waiting for Approval</span>' : link_to('Submit For Approval', 'episode/submit?id=' . $form->getObject()->getId(), array('confirm' => 'Are you sure?')))) ?>
                    &nbsp;<?php echo link_to('Preview', 'episode/show?id=' . $form->getObject()->getId()) ?>
                    <input type="submit" value="Save" />
                </td>
            </tr>
        </tfoot>
        <tbody>
            <?php echo $form->renderGlobalErrors() ?>
            <?php //echo $form->renderUsing('table'); /* ?>
            <tr>
                <th><?php echo $form['audio_file']->renderLabel() ?></th>
                <td>
                    <?php echo $form['audio_file']->renderError() ?>
                    <?php
                    include_partial('audio_widget', array(
                        'form' => $form,
                        'audio_hash' => $audio_hash,
                    ));
                    ?>
                </td>
            </tr>
            <tr>
                <th><?php echo $form['graphic_file']->renderLabel() ?></th>
                <td>
                    <?php echo $form['graphic_file']->renderError() ?>
                    <?php
                    include_partial('graphic_widget', array(
                        'form' => $form,
                        'graphic_hash' => $graphic_hash,
                    ));
                    ?>
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
                <th><?php echo $form['reddit_post_url']->renderLabel() ?></th>
                <td>
                    <?php echo $form['reddit_post_url']->renderError() ?>
                    <?php echo $form['reddit_post_url'] ?>
                </td>
            </tr>
            <?php //  */ ?>
        </tbody>
    </table>
</form>
