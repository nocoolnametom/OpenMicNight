<?php use_javascript(sfConfig::get('app_plupload_web_dir') . '/plupload.js'); ?>
<?php use_javascript(sfConfig::get('app_plupload_web_dir') . '/plupload.html4.js'); ?>
<?php use_javascript(sfConfig::get('app_plupload_web_dir') . '/plupload.html5.js'); ?>
<?php use_javascript(sfConfig::get('app_plupload_web_dir') . '/plupload.flash.js'); ?>
<?php $graphic_file_web_location = '/' . trim(str_replace(sfConfig::get('sf_web_dir'), '', ProjectConfiguration::getEpisodeGraphicFileLocalDirectory()), '/') . '/'; ?>

<script type="text/javascript">
    // Custom example logic
    $(function() {
        var uploader = new plupload.Uploader({
            runtimes : 'html5, html4',
            browse_button : 'graphic_pickfiles',
            container : 'image_uploader',
            max_file_size : '10mb',
            chunk_size : '2mb',
            unique_names : false,
            url : '<?php echo url_for('plupload/upload_image?id=' . $form->getObject()->getId()); ?>',
            flash_swf_url : '<?php echo image_path('plupload.flash.swf'); ?>',
            filters : [
                {title : "Images", extensions : "gif,jpg,jpeg,png"},
                {title : "ISOs", extensions : "iso"},
            ],
            resize : {width : 575, height : 240, quality : 90}
        });
<?php if (sfConfig::get('sf_environment') != 'prod'): ?>
            uploader.bind('Init', function(up, params) {
                $('#graphic_filelist').html("<div>Current runtime: " + params.runtime + "</div>");
            });
<?php endif; ?>

        $('#graphic_uploadfiles').click(function(e) {
            uploader.start();
            e.preventDefault();
        });

        uploader.init();

        uploader.bind('FilesAdded', function(up, files) {
            $.each(files, function(i, file) {
                $('#graphic_filelist').append(
                '<div id="' + file.id + '">' +
                    file.name + ' (' + plupload.formatSize(file.size) + ') <b></b>' +
                    '</div>');
            });
            $('#graphic_uploadfiles').html("[Upload Image]");
            $('#graphic_pickfiles_span').html("");

            up.refresh(); // Reposition Flash/Silverlight
        });

        uploader.bind('UploadProgress', function(up, file) {
            $('#' + file.id + " b").html(file.percent + "%");
            if (file.percent != 100)
            {    
                $('#graphic_uploadfiles_span').html("<span class=\"pluploader_warning\">Please wait before your file is uploaded before submitting any further changes to the episode.</span>");
            }
            $('#remove_graphic').html("");
        });

        uploader.bind('Error', function(up, err) {
            $('#graphic_filelist').append("<div>Error: " + err.code +
                ", Message: " + err.message +
                (err.file ? ", File: " + err.file.name : "") +
                "</div>"
        );

            up.refresh(); // Reposition Flash/Silverlight
        });

        uploader.bind('FileUploaded', function(up, file) {
            $('#' + file.id + " b").html("100%");
            $('#graphic_uploadfiles_span').html("");
            $('#uploader_graphic').attr("src", "<?php echo substr(image_path($graphic_file_web_location . $graphic_hash . '.png'), 0, -3); ?>" + file.name.split('.').pop());
        });
    });
</script>

<div id="image_uploader">
    <div id="uploader_graphic_div">
        <img id="uploader_graphic" src="<?php echo ($form->getObject()->getGraphicFile() ? image_path($graphic_file_web_location . $form->getObject()->getGraphicFile()) : '') ?>" />
    </div>
    <?php if ($form->getObject()->getGraphicFile()): ?>
        <div id="remove_graphic">
            <input type="checkbox" name="episode[graphic_file_delete]" id="episode_audio_file_delete" />
            <label for="episode_graphic_file_delete">remove the current file</label>
        </div>
    <?php endif; ?>
    <div id="graphic_filelist"><?php if (sfConfig::get('sf_environment') != 'prod'): ?>No runtime found.<?php endif; ?></div>
    <span id="graphic_pickfiles_span"><a id="graphic_pickfiles" href="#">[Select Image]</a></span>
    <span id="graphic_uploadfiles_span"><a id="graphic_uploadfiles" href="#"></a></span>
</div>