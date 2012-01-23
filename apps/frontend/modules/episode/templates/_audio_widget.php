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
</script>

<div id="audio_uploader">There was an error creating the download window.</div>
<div class="pluploader_warning">Please wait before your file is uploaded before submitting any further changes to the episode.</div>