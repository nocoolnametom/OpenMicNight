<?php use_javascript($plupload_web_dir . '/plupload.js'); ?>
<?php use_javascript($plupload_web_dir . '/plupload.html4.js'); ?>
<?php use_javascript($plupload_web_dir . '/plupload.html5.js'); ?>
<?php use_javascript($plupload_web_dir . '/plupload.flash.js'); ?>

<?php use_javascript($plupload_web_dir . '/jquery.ui.plupload/jquery.ui.plupload.js', 'last'); ?>
<?php use_javascript($plupload_web_dir . '/jquery.plupload.queue/jquery.plupload.queue.js'); ?>

<?php use_stylesheet('../js/' . $plupload_web_dir . '/jquery.plupload.queue/css/jquery.plupload.queue.css'); ?>

<script type="text/javascript">
    // Convert divs to queue widgets when the DOM is ready
    $(function() {
        // Setup flash version
        $("#audio_uploader").pluploadQueue({
            // General settings
            runtimes : 'flash, html5, html4',
            url : '<?php echo url_for('plupload/upload_audio?id=4'); ?>',
            chunk_size : '2mb',
            unique_names : false,

            // Specify what files to browse for
            filters : [
                {title : "Audio files", extensions : "mp3,m4a,flac,ogg"},
                {title : "Iso files", extensions : "iso"}
            ],

            // Flash settings
            flash_swf_url : '<?php echo image_path('plupload.flash.swf'); ?>'
        });
    });
</script>
<form>
    <div id="audio_uploader">You browser doesn't have Adobe Flash installed.</div>
</form>
