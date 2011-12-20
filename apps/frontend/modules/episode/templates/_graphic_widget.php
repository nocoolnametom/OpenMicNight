<?php use_javascript(sfConfig::get('app_plupload_web_dir') . '/plupload.js'); ?>
<?php use_javascript(sfConfig::get('app_plupload_web_dir') . '/plupload.html4.js'); ?>
<?php use_javascript(sfConfig::get('app_plupload_web_dir') . '/plupload.html5.js'); ?>
<?php use_javascript(sfConfig::get('app_plupload_web_dir') . '/plupload.flash.js'); ?>

<script type="text/javascript">
    // Custom example logic
    $(function() {
        var uploader = new plupload.Uploader({
            runtimes : 'html5, html4',
            browse_button : 'pickfiles',
            container : 'image_uploader',
            //max_file_size : '10mb',
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
                $('#filelist').html("<div>Current runtime: " + params.runtime + "</div>");
            });
<?php endif; ?>

        $('#uploadfiles').click(function(e) {
            uploader.start();
            e.preventDefault();
        });

        uploader.init();

        uploader.bind('FilesAdded', function(up, files) {
            $.each(files, function(i, file) {
                $('#filelist').append(
                '<div id="' + file.id + '">' +
                    file.name + ' (' + plupload.formatSize(file.size) + ') <b></b>' +
                    '</div>');
            });
            $('#uploadfiles').html("[Upload Image]");
            $('#pickfiles_span').html("");

            up.refresh(); // Reposition Flash/Silverlight
        });

        uploader.bind('UploadProgress', function(up, file) {
            $('#' + file.id + " b").html(file.percent + "%");
            if (file.percent != 100)
            {    
                $('#uploadfiles_span').html("<span style=\"text-size: smaller;\">Please wait before your file is uploaded before submitting any further changes to the episode.</span>");
            }
            $('#remove_graphic').html("");
        });

        uploader.bind('Error', function(up, err) {
            $('#filelist').append("<div>Error: " + err.code +
                ", Message: " + err.message +
                (err.file ? ", File: " + err.file.name : "") +
                "</div>"
        );

            up.refresh(); // Reposition Flash/Silverlight
        });

        uploader.bind('FileUploaded', function(up, file) {
            $('#' + file.id + " b").html("100%");
            $('#uploadfiles_span').html("");
            $('#uploader_graphic').attr("src", "<?php echo substr(image_path('/uploads/graphics/' . $graphic_hash . '.png'), 0, -3); ?>" + file.name.split('.').pop());
        });
    });
</script>

<div id="image_uploader">
    <div id="uploader_graphic_div">
        <img id="uploader_graphic" src="<?php echo ($form->getObject()->getGraphicFile() ? image_path('/uploads/graphics/' . $form->getObject()->getGraphicFile()) : '') ?>" />
    </div>
    <?php if ($form->getObject()->getGraphicFile()): ?>
        <div id="remove_graphic">
            <input type="checkbox" name="episode[audio_file_delete]" id="episode_audio_file_delete">
            <label for="episode_audio_file_delete">remove the current file</label>
        </div>
    <?php endif; ?>
    <div id="filelist"><?php if (sfConfig::get('sf_environment') != 'prod'): ?>No runtime found.<?php endif; ?></div>
    <span id="pickfiles_span"><a id="pickfiles" href="#">[Select Image]</a></span>
    <span id="uploadfiles_span"><a id="uploadfiles" href="#"></a></span>
</div>