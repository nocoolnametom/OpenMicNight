<?php
/**
 * Widget for a file that persists between form submissions
 *
 * @package     rbUtilityPlugin
 * @subpackage  widget
 * @author      Kevin Dew <kev@redbullet.co.uk>
 * @version     SVN $Id: sfWidgetFormInputPersistentFileEditable.class.php 206 2010-12-15 10:52:42Z kevin $
 */

class sfWidgetFormInputPersistentFileEditable extends sfWidgetFormInputFile
{
  protected
    $_containerId,
    $_hiddenFieldName,
    $_hiddenFieldId,
    $_file,
    $_toDelete,
    $_deleteId
  ;

  /**
   * Constructor.
   *
   * Available options:
   *
   *  * file_dir:     The directory where the file is (required)
   *  * is_image:     Whether the file is a displayable image
   *  * with_delete:  Whether to add a delete checkbox or not
   *  * delete_label: The delete label used by the template
   *  * template:     The HTML template to use to render this widget when in edit mode
   *                  The available placeholders are:
   *                    * %input% (the image upload widget)
   *                    * %delete% (the delete checkbox)
   *                    * %delete_label% (the delete label text)
   *                    * %file% (the file tag)
   *
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *
   * @see sfWidgetFormInputFile
   */
  protected function configure($options = array(), $attributes = array())
  {
    parent::configure($options, $attributes);

    $this->addRequiredOption('file_dir');
    $this->addOption('is_image', false);
    $this->addOption('with_delete', true);
    $this->addOption('delete_label', 'Remove');
    $this->addOption(
      'template',
      '<div id="%container_id%" class="editable-file-container">'
      . '<div class="file-placeholder">%file_template%</div> '
      . '%input_template%'
      . '%delete_template%'
      . '</div> %javascript%'
    );
    $this->addOption('to_delete_class', 'delete');

    $this->addOption(
      'file_template',
      '<div class="file-upload file-container%to_delete_class%">'
      . '<a href="%file_url%" target="_blank" class="file">%file_name%</a>'
      . '</div>'
    );
    $this->addOption(
      'image_template',
      '<div class="file-upload image-container%to_delete_class%">'
      . '<img src="%file_url%" alt="" />'
      . '</div>'
    );

    $this->addOption(
      'input_template',
      '<div class="input-container">'
      . '%file_input% %hidden_input%'
      . '</div>'
    );

    $this->addOption(
      'delete_template',
      '<div class="delete-container">'
      . '%delete_input% %delete_label%'
      . '</div>'
    );

    $this->addOption('minify_js_method', null);
    $this->addOption('minify_js_method_options', array());

    $this->addOption(
      'javascript',
      <<<EOF
(function($) {

$(document).ready(function() {

  $("#%delete_id%").click(function() {
    if (this.checked) {
      $("#%container_id%").find(".file").addClass("%to_delete_class%");
    } else {
      $("#%container_id%").find(".file").removeClass("%to_delete_class%");
    }
  });

});

})(jQuery);
EOF
    );
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
    $this->_hiddenFieldName = $name . '[file]';
    $this->_hiddenFieldId = $this->generateId($this->_hiddenFieldName);
    $this->_file = $value;
    $this->_toDelete = false;
    $this->_deleteId = $this->generateId($name . '[delete]');

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
        '%input_template%' =>
          $this->buildInputTemplate($name, $value, $attributes, $errors),
        '%delete_template%' =>
          $this->buildDeleteTemplate($name, $value, $attributes, $errors),
        '%javascript%' =>
          $this->buildJavascript($name, $value, $attributes, $errors)
      )
    );
  }

  protected function buildFileTemplate($name, $value, $attributes, $errors)
  {
    if (!$this->_file)
    {
      return '';
    }

    $filePath = '';

    if ($this->getOption('file_dir'))
    {
      $filePath = rtrim($this->getOption('file_dir'), '/');
    }

    $filePath .= '/' . $this->_file;

    $deleteClass =
      ($this->getOption('to_delete_class') ? ' ' : '')
      . $this->getOption('to_delete_class')
    ;

    $replacements = array(
      '%file_name%' => $this->_file,
      '%file_url%' => $filePath,
      '%to_delete_class%' => $this->_toDelete ? $deleteClass : ''
    );

    if ($this->getOption('is_image'))
    {
      $template = strtr($this->getOption('image_template'), $replacements);
    }
    else
    {
      $template = strtr($this->getOption('file_template'), $replacements);
    }

    return $template;
  }

  protected function buildInputTemplate($name, $value, $attributes, $errors)
  {
    $fileInput = $this->renderTag(
      'input',
      array('type' => 'file', 'name' => $name . '[upload]')
    );

    $hiddenInput = '';

    if ($this->_file)
    {
      $hiddenInput = $this->renderTag(
        'input',
        array(
          'type' => 'hidden',
          'name' => $this->_hiddenFieldName,
          'id' => $this->_hiddenFieldId,
          'value' => $this->_file
        )
      );
    }

    return strtr(
      $this->getOption('input_template'),
      array(
        '%file_input%' => $fileInput,
        '%hidden_input%' => $hiddenInput
      )
    );
  }

  protected function buildDeleteTemplate($name, $value, $attributes, $errors)
  {
    if (!$this->getOption('with_delete') || !$this->_file)
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
        '%delete_label%' => $deleteLabel
      )
    );
  }

  protected function buildJavascript($name, $value, $attributes, $errors)
  {
    if (!$this->getOption('javascript'))
    {
      return '';
    }

    $javascript = strtr(
      $this->getOption('javascript'),
      array(
        '%delete_id%' => addcslashes($this->_deleteId, '"'),
        '%container_id%' => addcslashes($this->_containerId, '"'),
        '%to_delete_class%' => addcslashes($this->getOption('to_delete_class'), '"'),
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
   * @see     parent
   * @return  array
   */
  public function getStylesheets()
  {
    return array(
      '/rbUtilityPlugin/css/input-persistent-file.css' => 'screen'
    );
  }

  protected function minifyJavascript($javascript)
  {
    if (
      $this->getOption('minify_js_method')
      &&
      is_callable($this->getOption('minify_js_method'))
    )
    {
      $javascript = call_user_func(
        $this->getOption('minify_js_method'),
        $javascript,
        $this->getOption('minify_js_method_options')
      );
    }

    return $javascript;
  }
}
