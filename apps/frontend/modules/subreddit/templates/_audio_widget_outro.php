<?php use_javascript(sfConfig::get('app_plupload_web_dir') . '/plupload.js'); ?>
<?php use_javascript(sfConfig::get('app_plupload_web_dir') . '/plupload.html4.js'); ?>
<?php use_javascript(sfConfig::get('app_plupload_web_dir') . '/plupload.html5.js'); ?>
<?php use_javascript(sfConfig::get('app_plupload_web_dir') . '/plupload.flash.js'); ?>

<script type="text/javascript">
    // Custom example logic
    $(function() {
        var uploader = new plupload.Uploader({
            runtimes : 'html5, html4',
            browse_button : 'outro_pickfiles',
            container : 'outro_uploader',
            max_file_size : '10mb',
            chunk_size : '2mb',
            unique_names : false,
            url : '<?php echo url_for('plupload/upload_subreddit_outro?id=' . $form->getObject()->getId()); ?>',
            flash_swf_url : '<?php echo image_path('plupload.flash.swf'); ?>',
            filters : [
                {title : "Audio files", extensions : "mp3,m4a,flac,ogg"}
            ]
        });
<?php if (sfConfig::get('sf_environment') != 'prod'): ?>
            uploader.bind('Init', function(up, params) {
                $('#outro_filelist').html("<div>Current runtime: " + params.runtime + "</div>");
            });
<?php endif; ?>

        $('#outro_uploadfiles').click(function(e) {
            uploader.start();
            e.preventDefault();
        });

        uploader.init();

        uploader.bind('FilesAdded', function(up, files) {
            $.each(files, function(i, file) {
                $('#outro_filelist').append(
                '<div id="' + file.id + '">' +
                    file.name + ' (' + plupload.formatSize(file.size) + ') <b></b>' +
                    '</div>');
            });
            $('#outro_uploadfiles').html("[Upload Audio File]");
            $('#outro_pickfiles_span').html("");

            up.refresh(); // Reposition Flash/Silverlight
        });

        uploader.bind('UploadProgress', function(up, file) {
            $('#' + file.id + " b").html(file.percent + "%");
            if (file.percent != 100)
            {    
                $('#outro_uploadfiles_span').html("<span class=\"pluploader_warning\">Please wait before your file is uploaded before submitting any further changes to the subreddit.</span>");
            }
            $('#remove_audio').html("");
        });

        uploader.bind('Error', function(up, err) {
            $('#outro_filelist').append("<div>Error: " + err.code +
                ", Message: " + err.message +
                (err.file ? ", File: " + err.file.name : "") +
                "</div>"
        );

            up.refresh(); // Reposition Flash/Silverlight
        });

        uploader.bind('FileUploaded', function(up, file) {
            $.ajax('<?php echo url_for('@subreddit_backup?id=' . $form->getObject()->getId() . '&which=outro') ?>');
            $('#' + file.id + " b").html("100%");
            $('#outro_uploadfiles_span').html("");
            $('#subreddit_outro').attr("src", "<?php echo substr(url_for('@subreddit_outro?id=' . $form->getObject()->getId() . '&format=mp3'), 0, -3); ?>" + file.name.split('.').pop());
            $('#subreddit_outro_link').attr("href", "<?php echo substr(url_for('@subreddit_outro?id=' . $form->getObject()->getId() . '&format=mp3'), 0, -3); ?>" + file.name.split('.').pop());
            $('#subreddit_outro_link').http(file.name);
            
        });
    });
</script>

<div id="outro_uploader">
    <?php include_partial('subreddit/html5_audio_player', array(
        'subreddit' => $form->getObject(),
        'filename' => $form->getObject()->getEpisodeOutro(),
        'type' => 'outro',
        'class' => 'full',
        'audio_src' => ProjectConfiguration::getApplicationAmazonBucketUrl() . 'outro/' . $form->getObject()->getEpisodeOutro(),
    )); ?>
    <?php if ($form->getObject()->getEpisodeOutro()): ?>
        <div id="remove_outro">
            <input type="checkbox" name="subreddit[episode_outro_delete]" id="subreddit_episode_outro_delete" />
            <label for="subreddit_episode_outro_delete">remove the current file</label>
        </div>
    <?php endif; ?>
    <div id="outro_filelist"><?php if (sfConfig::get('sf_environment') != 'prod'): ?>No runtime found.<?php endif; ?></div>
    <span id="outro_pickfiles_span"><a id="outro_pickfiles" href="#">[Select Audio File]</a></span>
    <span id="outro_uploadfiles_span"><a id="outro_uploadfiles" href="#"></a></span>
</div>