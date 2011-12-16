<?php
/**
 * Widget for a file that persists between form submissions and uses Plupload 
 * as the mechanism for uploading
 *
 * Note default options use both jQuery and jQuery UI
 *
 * @package     rbUtilityPlugin
 * @subpackage  widget
 * @author      Kevin Dew <kev@redbullet.co.uk>
 * @version     SVN $Id: sfWidgetFormInputPersistentFileEditablePlupload.class.php 218 2010-12-21 01:00:15Z kevin $
 */

class sfWidgetFormInputPersistentFileEditablePlupload
  extends sfWidgetFormInputPersistentFileEditable
{

  protected
    $_containerId,
    $_uploaderId,
    $_hiddenFieldName,
    $_hiddenFieldId,
    $_file,
    $_toDelete,
    $_pluploadOptions,
    $_deleteId,
    $_pluploadDefaultOptions
  ;

  /**
   * There are a lot of options!
   *
   * Required
   * * plupload_upload_path - the path that files will be uploaded to
   *
   * Optional
   * * plupload_tempate - html for the plupload controls
   * * uploader_text - text within the uploader control
   * * uploading_text - text that appears while file is uploading
   * * ul_error_class - class for the list holding errors
   * * li_error_class - class for the list item within it
   * * plupload_500_error_message - error message for when plupload is returned
   *   a 500 error
   * * plupload_file_size_error_message
   * * plupload_extension_error_message
   * * delete_active_class - a class to give the delete container when it is in
   *   use so javascript can determine whether to hide it or not
   * * plupload_options - options for plupload itself, this is merged with
   *   the default options specified in this class and
   *   app_rbUtilityPlugin_plupload_options in a app.yml
   * * plupload_path_to_js - path to the javascript files for plupload
   * * plupload_js_base - base javascript for this widget (glues other parts in
   *   it)
   * * delete_js - js for handling the delete checkbox
   * * plupload_js_pre_init - all of these are various js methods
   * * plupload_js_files_added
   * * plupload_js_upload_progress
   * * plupload_js_error
   * * plupload_js_file_uploaded
   * * plupload_js_reset_uploader
   * * plupload_js_handle_error
   * * plupload_js_handle_success
   * * plupload_js_hide_uploader
   * * plupload_js_reset_errors
   * * plupload_js_post_init
   *
   * @see     parent
   * @param   array $options
   * @param   array $attributes
   */
  protected function configure($options = array(), $attributes = array())
  {
    parent::configure($options, $attributes);

    // default options for plupload
    $this->_pluploadDefaultOptions = $this->getDefaultPluploadOptions();

    $this->addRequiredOption('plupload_upload_path');

    $this->addOption(
      'template',
      '<div id="%container_id%" class="editable-file-container plupload-editable">'
      . '<div class="file-placeholder">%file_template%</div> '
      . '%plupload_template% '
      . '%input_template%'
      . '%delete_template%'
      . '<div class="errors"></div>'
      . '</div> %javascript%'
    );

    $this->addOption(
      'plupload_template',
      '<div class="plupload-controls-container">'
      . '<div class="progress-container"></div>'
      . '<div class="uploader-container" id="%plupload_container_id%">'
      . '<button class="uploader" id="%uploader_id%">%uploader_text%</button>'
      . '</div>'
      . '</div>'
    );

    $this->addOption('uploader_text', 'Upload');
    $this->addOption('uploading_text', 'Uploading %filename%');

    $this->addOption('ul_error_class', 'form-errors sf-error');
    $this->addOption('li_error_class', 'error');
    $this->addOption(
      'plupload_500_error_message', 'File could not be uploaded'
    );
    $this->addOption(
      'plupload_file_size_error_message', 'File size is too big'
    );
    $this->addOption(
      'plupload_extension_error_message', 'File is incorrect type'
    );

    $this->addOption(
      'delete_template',
      '<div class="delete-container%delete_active_class%">'
      . '%delete_input% %delete_label%'
      . '</div>'
    );
    $this->addOption('delete_active_class', 'active');
    
    $this->addOption('plupload_options', array());

    $this->addOption(
      'plupload_path_to_js', '/rbUtilityPlugin/js/plupload-1.3.0'
    );

    $this->addOption('plupload_js_base', <<<EOF
(function($) {
$(document).ready(function() {

%delete_js%

%plupload_js_pre_init%

  var container = $("#%container_id%");

  $("#%uploader_id%").click(function() { return false; });
  container.find(".input-container").hide();
  container.find(".delete-container:not(.active)").hide();

  if (!$("#%hidden_field_id%").length) {
    $("<input type=\"hidden\" name=\"%hidden_field_name%\" "
      + "id=\"%hidden_field_id%\" />"
    ).appendTo(container.find(".input-container").first());
  }

  var uploader = new plupload.Uploader(%uploader_json%);
  uploader.init();
  
	uploader.bind("FilesAdded", function(up, files) {
%plupload_js_files_added%
	});

  uploader.bind("UploadProgress", function(up, file) {
%plupload_js_upload_progress%
  });

  uploader.bind("Error", function(up, err) {
%plupload_js_error%
  });

  uploader.bind("FileUploaded", function(up, file, response) {
%plupload_js_file_uploaded%
  });

  function resetUploader(up) {
%plupload_js_reset_uploader%
  }

  function handleError(up, message) {
%plupload_js_handle_error%
  }

  function handleSuccess(up, file) {
%plupload_js_handle_success%
  }

  function hideUploader(up) {
%plupload_js_hide_uploader%
  }

  function resetErrors(up) {
%plupload_js_reset_errors%
  }
});
})(jQuery);
EOF
    );

    $this->addOption('delete_js', <<<EOF
$("#%delete_id%").click(function() {
  if (this.checked) {
    $("#%container_id%").find(".file").addClass("%to_delete_class%");
  } else {
    $("#%container_id%").find(".file").removeClass("%to_delete_class%");
  }
});
EOF
    );

    $this->addOption('plupload_js_pre_init', '');

    $this->addOption('plupload_js_files_added', <<<EOF
resetErrors();
resetUploader(up);
var filename = files[0].name;

var progress = container.find(".progress-container").get(0);
$(progress).append(
  "<div class=\"progress\"><div class=\"message\">%uploading_text%"
  + "</div><div class=\"bar\"></div></div>"
);

$(progress).find(".bar").progressbar({value: 0});

hideUploader(up);

up.start();
up.refresh();
EOF
    );
    
    $this->addOption('plupload_js_upload_progress', <<<EOF
container.find(".progress-container .bar").progressbar(
  "value", file.percent
);
EOF
    );

    $this->addOption('plupload_js_error', <<<EOF

var message = "";
switch (err.code) {
  case plupload.FILE_SIZE_ERROR:
    message = "%plupload_file_size_error_message%";
    break;
  case plupload.FILE_EXTENSION_ERROR:
    message = "%plupload_extension_error_message%";
    break;    
}

return handleError(up, message);
EOF
    );

    $this->addOption('plupload_js_file_uploaded', <<<EOF
try {
  var json = $.parseJSON(response.response);
} catch (err) {
  return handleError(up, "");
}

if (json.status == "error") {
  return handleError(up, json.message);
}

if (json.status != "complete") {
  return;
}

handleSuccess(up, json.file);
EOF
    );

    $this->addOption('plupload_js_reset_uploader', <<<EOF
container.find(".progress-container").empty();
container.find(".uploader-container button.uploader").show();
up.refresh();
EOF
    );

    $this->addOption('plupload_js_handle_error', <<<EOF
if (!message) {
  message = "%plupload_500_error_message%";
}

resetErrors(up);
var ul = $("<ul class=\"%ul_error_class%\" />").append(
  $("<li class=\"%li_error_class%\" />").text(message)
);
container.find(".errors").append(ul);
resetUploader(up);
EOF
    );

    $this->addOption('plupload_js_handle_success', <<<EOF
container.find(".file-placeholder .file").remove();

var template = "%file_template%";

template = template.replace(/%file_url%/g, "%path%" + "/" + file);
template = template.replace(/%file_name%/g, file);

container.find(".file-placeholder").append(template);

if ($("#%hidden_field_id%").length) {
  $("#%hidden_field_id%").val(file);
} else {
  $("<input type=\"hidden\" name=\"%hidden_field_name%\" "
    + "id=\"%hidden_field_id%\" />"
  ).val(file).appendTo(container.find(".input-container").first());
}

container.find(".delete-container").show();

resetUploader(up);
EOF
    );

    $this->addOption('plupload_js_hide_uploader', <<<EOF
container.find(".uploader-container button.uploader").hide();
up.refresh();
EOF
    );

    $this->addOption('plupload_js_reset_errors', <<<EOF
container.find(".errors").empty();
EOF
    );

    $this->addOption('plupload_js_post_init', '');

  }

  /**
   * Renders the widget.
   *
   * @param  string $name        The element name
   * @param  string $value       The value displayed in this widget
   * @param  array  $attributes  An array of HTML attributes to be merged with the default HTML attributes
   * @param  array  $errors      An array of errors for the field
   *
   * @return string An HTML tag string
   *
   * @see sfWidgetForm
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $this->_containerId = $this->generateId($name . '[container]');
    $this->_pluploadContainerId = 
      $this->generateId($name . '[plupload_container]')
    ;
    $this->_uploaderId = $this->generateId($name . '[uploader]');
    $this->_deleteId = $this->generateId($name . '[delete]');
    $this->_hiddenFieldName = $name . '[file]';
    $this->_hiddenFieldId = $this->generateId($this->_hiddenFieldName);

    // make plupload options
    $this->_pluploadOptions = array_merge(
      $this->getPluploadOptions(),
      array(
        'url' => $this->getOption('plupload_upload_path'),
        'browse_button' => $this->_uploaderId,
        'container' => $this->_pluploadContainerId
      )
    );

    $this->_file = $value;
    $this->_toDelete = false;

    if (is_array($value))
    {
      $this->_file = isset($value['file']) ? $value['file'] : '';
      $this->_toDelete = isset($value['delete']) && $value['delete'];
    }

    return strtr(
      $this->getOption('template'),
      array(
        '%container_id%' =>
          $this->_containerId,
        '%file_template%' => 
          $this->buildFileTemplate($name, $value, $attributes, $errors),
        '%plupload_template%' =>
          $this->buildPluploadTemplate($name, $value, $attributes, $errors),
        '%input_template%' =>
          $this->buildInputTemplate($name, $value, $attributes, $errors),
        '%delete_template%' =>
          $this->buildDeleteTemplate($name, $value, $attributes, $errors),
        '%javascript%' =>
          $this->buildJavascript($name, $value, $attributes, $errors),
      )
    );
  }

  /**
   * Build the template for the plupload part of the widget
   *
   * @param   string  $name
   * @param   string  $value
   * @param   array   $attributes
   * @param   array   $errors
   * @return  string
   */
  protected function buildPluploadTemplate($name, $value, $attributes, $errors)
  {
    return strtr(
      $this->getOption('plupload_template'),
      array(
        '%plupload_container_id%' => $this->_pluploadContainerId,
        '%uploader_id%' => $this->_uploaderId,
        '%uploader_text%' => $this->getOption('uploader_text')
      )
    );
  }

  /**
   * Build the template for the delete part of the widget
   *
   * @param   string  $name
   * @param   string  $value
   * @param   array   $attributes
   * @param   array   $errors
   * @return  string
   */
  protected function buildDeleteTemplate($name, $value, $attributes, $errors)
  {
    if (!$this->getOption('with_delete'))
    {
      return '';
    }

    $deleteInput = $this->renderTag(
      'input',
      array_merge(
        array('type' => 'checkbox', 'name' => $name . '[delete]'),
        $attributes,
        array('checked' => $this->_toDelete)
      )
    );

    $deleteLabel = $this->renderContentTag(
      'label',
      $this->translate($this->getOption('delete_label')),
      array_merge(array('for' => $this->generateId($name . '[delete]')))
    );

    return strtr(
      $this->getOption('delete_template'),
      array(
        '%delete_input%' => $deleteInput,
        '%delete_label%' => $deleteLabel,
        '%delete_active_class%' => ($this->_file
          ? ' ' . $this->getOption('delete_active_class')
          : ''
        )
      )
    );
  }

  /**
   * Build the javascript for this widget
   *
   * @param   string  $name
   * @param   string  $value
   * @param   array   $attributes
   * @param   array   $errors
   * @return  string
   */
  protected function buildJavascript($name, $value, $attributes, $errors)
  {
    // build full js for translatation
    $javascript = strtr(
      $this->getOption('plupload_js_base'),
      array(
        '%delete_js%' => $this->getOption('delete_js'),
        '%plupload_js_pre_init%' => $this->getOption('plupload_js_pre_init'),
        '%plupload_js_files_added%' => $this->getOption('plupload_js_files_added'),
        '%plupload_js_upload_progress%' => $this->getOption('plupload_js_upload_progress'),
        '%plupload_js_error%' => $this->getOption('plupload_js_error'),
        '%plupload_js_file_uploaded%' => $this->getOption('plupload_js_file_uploaded'),
        '%plupload_js_reset_uploader%' => $this->getOption('plupload_js_reset_uploader'),
        '%plupload_js_handle_error%' => $this->getOption('plupload_js_handle_error'),
        '%plupload_js_handle_success%' => $this->getOption('plupload_js_handle_success'),
        '%plupload_js_hide_uploader%' => $this->getOption('plupload_js_hide_uploader'),
        '%plupload_js_reset_errors%' => $this->getOption('plupload_js_reset_errors'),
        '%plupload_js_post_init%' => $this->getOption('plupload_js_post_init'),
      )
    );

    $fileTemplate = $this->getOption('is_image')
      ? $this->getOption('image_template')
      : $this->getOption('file_template')
    ;

    $uploadingText = strtr(
      addcslashes($this->getOption('uploading_text'), '"'),
      array(
        '%filename%' => '" + filename + "'
      )
    );

    // add replacement values
    $javascript = strtr(
      $javascript,
      array(
        '%uploader_id%' => addcslashes($this->_uploaderId, '"'),
        '%container_id%' => addcslashes($this->_containerId, '"'),
        '%delete_id%' => addcslashes($this->_deleteId, '"'),
        '%uploader_json%' => json_encode($this->_pluploadOptions),
        '%file_template%' => addcslashes(
          strtr($fileTemplate, array('%to_delete_class%' => ''))
          , '"'
        ),
        '%path%' => addcslashes(rtrim($this->getOption('file_dir'), '/'), '"'),
        '%hidden_field_name%' => addcslashes($this->_hiddenFieldName, '"'),
        '%hidden_field_id%' => addcslashes($this->_hiddenFieldId, '"'),
        '%ul_error_class%' =>
          addcslashes($this->getOption('ul_error_class', ''), '"'),
        '%li_error_class%' =>
          addcslashes($this->getOption('li_error_class', ''), '"'),
        '%plupload_500_error_message%' =>
          addcslashes($this->getOption('plupload_500_error_message', ''), '"'),
        '%plupload_file_size_error_message%' =>
          addcslashes(
            $this->getOption('plupload_file_size_error_message'), '"'
          ),
        '%plupload_extension_error_message%' =>
          addcslashes(
            $this->getOption('plupload_extension_error_message'), '"'
          ),
        '%to_delete_class%' => addcslashes($this->getOption('to_delete_class'), '"'),
        '%uploading_text%' => $uploadingText
      )
    );

    return sprintf(<<<EOF
<script type="text/javascript">
//<![CDATA[
  %s
//]]>
</script>
EOF
      ,
      $this->minifyJavascript($javascript)
    );
  }

  /**
   * Get the javascripts for this widget
   *
   * @see     parent
   * @return  array
   */
  public function getJavaScripts()
  {
    $options = $this->getPluploadOptions();

    $javascripts = array();

    if (
      isset($options['runtimes']) 
      &&
      strpos($options['runtimes'], 'gears') !== false
    )
    {
      $javascripts[] = 
        $this->getOption('plupload_path_to_js') . '/gears_init.js'
      ;
    }

    $javascripts[] =
      $this->getOption('plupload_path_to_js') . '/plupload.full.min.js'
    ;

    return array_merge(
      parent::getJavaScripts(),
      $javascripts
    );
  }

  /**
   * Build and return plupload options
   *
   * @return  array
   */
  protected function getPluploadOptions()
  {
    return array_merge(
      array(
        'flash_swf_url' =>
          $this->getOption('plupload_path_to_js') . '/plupload.flash.swf',
        'silverlight_xap_url' =>
          $this->getOption('plupload_path_to_js') . '/plupload.silverlight.xap'
      ),
      $this->_pluploadDefaultOptions,
      sfConfig::get('app_rbUtilityPlugin_plupload_options', array()),
      $this->getOption('plupload_options')
    );
  }

  /**
   * Get the default options for plupload
   *
   * @return  array
   */
  public function getDefaultPluploadOptions()
  {
    return array(
      'runtimes' => 'gears,flash,html5,silverlight',
      'chunk_size' => '1MB',
      'multi_selection' => false,
      'unique_names' => true
    );
  }
}
