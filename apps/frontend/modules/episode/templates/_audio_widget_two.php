<?php use_javascript(sfConfig::get('app_plupload_web_dir') . '/plupload.js'); ?>
<?php use_javascript(sfConfig::get('app_plupload_web_dir') . '/plupload.html4.js'); ?>
<?php use_javascript(sfConfig::get('app_plupload_web_dir') . '/plupload.html5.js'); ?>
<?php use_javascript(sfConfig::get('app_plupload_web_dir') . '/plupload.flash.js'); ?>

<script type="text/javascript">
    // Custom example logic
    $(function() {
        var uploader = new plupload.Uploader({
            runtimes : 'html5, html4',
            browse_button : 'audio_pickfiles',
            container : 'audio_uploader',
            max_file_size : '250mb',
            chunk_size : '2mb',
            unique_names : false,
            url : '<?php echo url_for('plupload/upload_audio?id=' . $form->getObject()->getId()); ?>',
            flash_swf_url : '<?php echo image_path('plupload.flash.swf'); ?>',
            filters : [
                {title : "Audio files", extensions : "mp3,m4a,flac,ogg"}
            ]
        });
<?php if (sfConfig::get('sf_environment') != 'prod'): ?>
            uploader.bind('Init', function(up, params) {
                $('#audio_filelist').html("<div>Current runtime: " + params.runtime + "</div>");
            });
<?php endif; ?>

        $('#audio_uploadfiles').click(function(e) {
            uploader.start();
            e.preventDefault();
        });

        uploader.init();

        uploader.bind('FilesAdded', function(up, files) {
            $.each(files, function(i, file) {
                $('#audio_filelist').append(
                '<div id="' + file.id + '">' +
                    file.name + ' (' + plupload.formatSize(file.size) + ') <b></b>' +
                    '</div>');
            });
            $('#audio_uploadfiles').html("[Upload Audio File]");
            $('#audio_pickfiles_span').html("");

            up.refresh(); // Reposition Flash/Silverlight
        });

        uploader.bind('UploadProgress', function(up, file) {
            $('#' + file.id + " b").html(file.percent + "%");
            if (file.percent != 100)
            {    
                $('#audio_uploadfiles_span').html("<span class=\"pluploader_warning\">Please wait before your file is uploaded before submitting any further changes to the episode.</span>");
            }
            $('#remove_audio').html("");
        });

        uploader.bind('Error', function(up, err) {
            $('#audio_filelist').append("<div>Error: " + err.code +
                ", Message: " + err.message +
                (err.file ? ", File: " + err.file.name : "") +
                "</div>"
        );

            up.refresh(); // Reposition Flash/Silverlight
        });

        uploader.bind('FileUploaded', function(up, file) {
            $.ajax('<?php echo url_for('@episode_backup?id=' . $form->getObject()->getId() . '&which=audio') ?>');
            $('#' + file.id + " b").html("100%");
            $('#audio_uploadfiles_span').html("");
            $('#episode_audio').attr("src", "<?php echo substr(url_for('@episode_audio?id=' . $form->getObject()->getId() . '&format=mp3'), 0, -3); ?>" + file.name.split('.').pop());
            $('#episode_audio_link').attr("href", "<?php echo substr(url_for('@episode_audio?id=' . $form->getObject()->getId() . '&format=mp3'), 0, -3); ?>" + file.name.split('.').pop());
            $('#episode_audio_link').http(file.name);
            
        });
    });
</script>

<div id="audio_uploader">
    <?php include_partial('episode/html5_audio_player', array(
        'episode' => $form->getObject(),
        'class' => 'full',
    )); ?>
    <?php if ($form->getObject()->getAudioFile() && !$form->getObject()->getSubmittedAt() && !$form->getObject()->getApprovedAt()): ?>
        <div id="remove_audio">
            <input type="checkbox" name="episode[audio_file_delete]" id="episode_audio_file_delete" />
            <label for="episode_audio_file_delete">remove the current file</label>
        </div>
    <?php endif; ?>
    <div id="audio_filelist"><?php if (sfConfig::get('sf_environment') != 'prod'): ?>No runtime found.<?php endif; ?></div>
    <span id="audio_pickfiles_span"><a id="audio_pickfiles" href="#">[Select Audio File]</a></span>
    <span id="audio_uploadfiles_span"><a id="audio_uploadfiles" href="#"></a></span>
</div>