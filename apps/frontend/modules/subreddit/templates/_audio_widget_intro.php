<?php use_javascript(sfConfig::get('app_plupload_web_dir') . '/plupload.js'); ?>
<?php use_javascript(sfConfig::get('app_plupload_web_dir') . '/plupload.html4.js'); ?>
<?php use_javascript(sfConfig::get('app_plupload_web_dir') . '/plupload.html5.js'); ?>
<?php use_javascript(sfConfig::get('app_plupload_web_dir') . '/plupload.flash.js'); ?>

<script type="text/javascript">
    // Custom example logic
    $(function() {
        var uploader = new plupload.Uploader({
            runtimes : 'html5, html4',
            browse_button : 'intro_pickfiles',
            container : 'intro_uploader',
            max_file_size : '10mb',
            chunk_size : '2mb',
            unique_names : false,
            url : '<?php echo url_for('plupload/upload_subreddit_intro?id=' . $form->getObject()->getId()); ?>',
            flash_swf_url : '<?php echo image_path('plupload.flash.swf'); ?>',
            filters : [
                {title : "Audio files", extensions : "mp3,m4a,flac,ogg"}
            ]
        });
<?php if (sfConfig::get('sf_environment') != 'prod'): ?>
            uploader.bind('Init', function(up, params) {
                $('#intro_filelist').html("<div>Current runtime: " + params.runtime + "</div>");
            });
<?php endif; ?>

        $('#intro_uploadfiles').click(function(e) {
            uploader.start();
            e.preventDefault();
        });

        uploader.init();

        uploader.bind('FilesAdded', function(up, files) {
            $.each(files, function(i, file) {
                $('#intro_filelist').append(
                '<div id="' + file.id + '">' +
                    file.name + ' (' + plupload.formatSize(file.size) + ') <b></b>' +
                    '</div>');
            });
            $('#intro_uploadfiles').html("[Upload Audio File]");
            $('#intro_pickfiles_span').html("");

            up.refresh(); // Reposition Flash/Silverlight
        });

        uploader.bind('UploadProgress', function(up, file) {
            $('#' + file.id + " b").html(file.percent + "%");
            if (file.percent != 100)
            {    
                $('#intro_uploadfiles_span').html("<span class=\"pluploader_warning\">Please wait before your file is uploaded before submitting any further changes to the subreddit.</span>");
            }
            $('#remove_audio').html("");
        });

        uploader.bind('Error', function(up, err) {
            $('#intro_filelist').append("<div>Error: " + err.code +
                ", Message: " + err.message +
                (err.file ? ", File: " + err.file.name : "") +
                "</div>"
        );

            up.refresh(); // Reposition Flash/Silverlight
        });

        uploader.bind('FileUploaded', function(up, file) {
            $.ajax('<?php echo url_for('@subreddit_backup?id=' . $form->getObject()->getId() . '&which=intro') ?>');
            $('#' + file.id + " b").html("100%");
            $('#intro_uploadfiles_span').html("");
            $('#subreddit_intro').attr("src", "<?php echo substr(url_for('@subreddit_intro?id=' . $form->getObject()->getId() . '&format=mp3'), 0, -3); ?>" + file.name.split('.').pop());
            $('#subreddit_intro_link').attr("href", "<?php echo substr(url_for('@subreddit_intro?id=' . $form->getObject()->getId() . '&format=mp3'), 0, -3); ?>" + file.name.split('.').pop());
            $('#subreddit_intro_link').http(file.name);
            
        });
    });
</script>

<div id="intro_uploader">
    <?php include_partial('subreddit/html5_audio_player', array(
        'subreddit' => $form->getObject(),
        'filename' => $form->getObject()->getEpisodeIntro(),
        'type' => 'intro',
        'class' => 'full',
        'audio_src' => ProjectConfiguration::getApplicationAmazonBucketUrl() . 'intro/' . $form->getObject()->getEpisodeIntro(),
    )); ?>
    <?php if ($form->getObject()->getEpisodeIntro()): ?>
        <div id="remove_intro">
            <input type="checkbox" name="subreddit[episode_intro_delete]" id="subreddit_episode_intro_delete" />
            <label for="subreddit_episode_intro_delete">remove the current file</label>
        </div>
    <?php endif; ?>
    <div id="intro_filelist"><?php if (sfConfig::get('sf_environment') != 'prod'): ?>No runtime found.<?php endif; ?></div>
    <span id="intro_pickfiles_span"><a id="intro_pickfiles" href="#">[Select Audio File]</a></span>
    <span id="intro_uploadfiles_span"><a id="intro_uploadfiles" href="#"></a></span>
</div>