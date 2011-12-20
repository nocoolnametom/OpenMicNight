<?php use_stylesheets_for_form($form) ?>
<?php use_javascripts_for_form($form) ?>

<?php use_javascript(sfConfig::get('app_plupload_web_dir') . '/plupload.js'); ?>
<?php use_javascript(sfConfig::get('app_plupload_web_dir') . '/plupload.html4.js'); ?>
<?php use_javascript(sfConfig::get('app_plupload_web_dir') . '/plupload.html5.js'); ?>
<?php use_javascript(sfConfig::get('app_plupload_web_dir') . '/plupload.flash.js'); ?>

<?php use_javascript(sfConfig::get('app_plupload_web_dir') . '/jquery.ui.plupload/jquery.ui.plupload.js', 'last'); ?>
<?php use_javascript(sfConfig::get('app_plupload_web_dir') . '/jquery.plupload.queue/jquery.plupload.queue.js'); ?>

<?php use_stylesheet('../js/' . sfConfig::get('app_plupload_web_dir') . '/jquery.plupload.queue/css/jquery.plupload.queue.css'); ?>

<script type="text/javascript">
    // Convert divs to queue widgets when the DOM is ready
    $(function() {
        // Setup flash version
        $("#audio_uploader").pluploadQueue({
            // General settings
            runtimes : 'flash, html5, html4',
            url : '<?php echo url_for('plupload/upload_audio?id=' . $form->getObject()->getId()); ?>',
            chunk_size : '2mb',
            unique_names : false,

            // Specify what files to browse for
            filters : [
                {title : "Audio files", extensions : "mp3,m4a,flac,ogg"}
            ],

            // Flash settings
            flash_swf_url : '<?php echo image_path('plupload.flash.swf'); ?>'
        });
    });
    
    // Convert divs to queue widgets when the DOM is ready
    $(function() {
        // Setup flash version
        $("#image_uploader").pluploadQueue({
            // General settings
            runtimes : 'flash, html5, html4',
            url : '<?php echo url_for('plupload/upload_image?id=' . $form->getObject()->getId()); ?>',
            chunk_size : '2mb',
            unique_names : false,

            // Specify what files to browse for
            filters : [
                {title : "Image files", extensions : "gif,jpg,jpeg,png"}
            ],

            // Flash settings
            flash_swf_url : '<?php echo image_path('plupload.flash.swf'); ?>'
        });
    });
</script>

<h3>Use the following box to upload the audio file for your Episode to <?php echo ProjectConfiguration::getApplicationName(); ?></h3>
<div id="audio_uploader">You browser doesn't have Adobe Flash installed.</div>
<div style="font-size:small;">Please wait before your file is uploaded before submitting any further changes to the episode.</div>

<div id="image_uploader">You browser doesn't have Adobe Flash installed.</div>


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
