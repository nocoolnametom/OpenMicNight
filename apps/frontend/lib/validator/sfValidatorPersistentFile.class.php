<?php
/**
 * Validator for a file that persists between form submissions
 *
 * @package     rbUtilityPlugin
 * @subpackage  validator
 * @author      Kevin Dew <kev@redbullet.co.uk>
 * @version     SVN $Id: sfValidatorPersistentFile.class.php 185 2010-12-09 21:29:43Z kevin $
 */
class sfValidatorPersistentFile extends sfValidatorUploadedFileAbstract
{
  /**
   * Configures the current validator.
   *
   * Available options:
   *
   *  * form:                     A form object associated with this validator
   *                              (to have it's tainted values updated)
   *  * field_name:               The field name of the form field
   *  * file_validator_class:     The class to validate the file upload
   *                              (can't extend sfValidatorFile for this
   *                              validator as otherwise the form processing
   *                              on sfFormDoctrine tries to save it as a file)
   *                              (Optional)
   *  * file_validator_options:   Options for the file validator (Optional)
   *  * file_validator_messages:  Messages for the file validator (Optional)
   *
   * @param array $options   An array of options
   * @param array $messages  An array of error messages
   *
   * @see   parent
   */
  protected function configure($options = array(), $messages = array())
  {
    if (!ini_get('file_uploads'))
    {
      throw new LogicException(
        sprintf(
          'Unable to use a file validator as "file_uploads" is disabled in '
          . 'your php.ini file (%s)', get_cfg_var('cfg_file_path')
        )
      );
    }

    parent::configure($options, $messages);
    $this->addRequiredOption('form');
    $this->addRequiredOption('field_name');
    $this->addOption('check_existing_file', false);
    $this->addOption('existing_file_path', null);

    $this->addMessage('partial', 'The uploaded file was only partially uploaded.');
    $this->addMessage('no_tmp_dir', 'Missing a temporary folder.');
    $this->addMessage('cant_write', 'Failed to write file to disk.');
    $this->addMessage('extension', 'File upload stopped by extension.');
  }

  /**
   * @see   parent
   * @todo  This doesn't currently delete any files, reason for this is we don't
   *        want to delete a file on a failed form processing and then have the
   *        user not save it leaving the old filename in storage
   */
  protected function doClean($value)
  {
    if (!is_array($value))
    {
      throw new Exception('Array expected as value');
    }

    // whether user has specified to delete
    $toDelete = isset($value['delete']) && $value['delete'];

    $form = $this->getOption('form');

    if (!$form instanceof sfForm)
    {
      throw new Exception('Form must be an instance of sfForm');
    } 
    elseif (!method_exists($form, 'setTaintedValue'))
    {
      throw new Exception('Form must have a set tainted value method');
    }

    $filename = isset($value['file']) ? $value['file'] : '';

    if (
      isset($value['upload'])
      &&
      !(
        isset($value['upload']['error'])
        &&
        UPLOAD_ERR_NO_FILE === $value['upload']['error']
      )
    )
    {
      // process the file sent
      $validatedFile = $this->validateUploadedFile($value['upload']);
      $filename = $this->processUpload($validatedFile);
      $toDelete = false;
    }
    else if (
      !$toDelete && $filename && $this->getOption('check_existing_file')
    )
    {
      // check existing file
      $this->checkExistingFile(
        ($this->getOption('existing_file_path')
          ? $this->getOption('existing_file_path')
          : $this->getOption('path')
        ) . $filename
      );
    }

    if ((!$filename || $toDelete) && $this->getOption('required'))
    {
      throw new sfValidatorError($this, 'required');
    }

    $return = $filename;

    if ($toDelete)
    {
      $return = array(
        'file' => $filename,
        'delete' => true
      );
    }

    $form->setTaintedValue($this->getOption('field_name'), $return);

    return !$toDelete ? $filename : '';
  }
  
  protected function validateUploadedFile($upload)
  {
    if (!is_array($upload) || !isset($upload['tmp_name']))
    {
      throw new sfValidatorError(
        $this, 'invalid', array('value' => (string) $upload)
      );
    }

    if (!isset($upload['name']))
    {
      $upload['name'] = '';
    }

    if (!isset($upload['error']))
    {
      $upload['error'] = UPLOAD_ERR_OK;
    }

    if (!isset($upload['size']))
    {
      $upload['size'] = filesize($upload['tmp_name']);
    }

    if (!isset($upload['type']))
    {
      $upload['type'] = 'application/octet-stream';
    }

    switch ($upload['error'])
    {
      case UPLOAD_ERR_INI_SIZE:
        $max = ini_get('upload_max_filesize');
        if ($this->getOption('max_size'))
        {
          $max = min($max, $this->getOption('max_size'));
        }
        throw new sfValidatorError(
          $this,
          'max_size',
          array('max_size' => $max, 'size' => (int) $upload['size'])
        );
      case UPLOAD_ERR_FORM_SIZE:
        throw new sfValidatorError(
          $this,
          'max_size',
          array('max_size' => 0, 'size' => (int) $upload['size'])
        );
      case UPLOAD_ERR_PARTIAL:
        throw new sfValidatorError($this, 'partial');
      case UPLOAD_ERR_NO_TMP_DIR:
        throw new sfValidatorError($this, 'no_tmp_dir');
      case UPLOAD_ERR_CANT_WRITE:
        throw new sfValidatorError($this, 'cant_write');
      case UPLOAD_ERR_EXTENSION:
        throw new sfValidatorError($this, 'extension');
    }

    // check file size
    if (
      $this->hasOption('max_size')
      &&
      $this->getOption('max_size') < (int) $upload['size']
    )
    {
      throw new sfValidatorError(
        $this,
        'max_size',
        array(
          'max_size' => $this->getOption('max_size'),
          'size' => (int) $upload['size']
        )
      );
    }

    $mimeType = $this->getMimeType(
      (string) $upload['tmp_name'], (string) $upload['type']
    );

    $mimeTypes = $this->getMimeTypes();

    if (!$this->checkMimeType($mimeType, $mimeTypes))
    {
      throw new sfValidatorError(
        $this,
        'mime_types',
        array('mime_types' => $mimeTypes, 'mime_type' => $mimeType)
      );
    }

    $class = $this->getOption('validated_file_class');

    return new $class(
      $upload['name'],
      $mimeType,
      $upload['tmp_name'],
      $upload['size'],
      $this->getOption('path')
    );
  }

  protected function checkExistingFile($filePath)
  {
    if (!file_exists($filePath))
    {
      throw new sfValidatorError(
        $this,
        'file_not_found'
      );
    }

    $mimeType = $this->getMimeType($filePath, 'application/octet-stream');

    $mimeTypes = $this->getMimeTypes();

    if (!$this->checkMimeType($mimeType, $mimeTypes))
    {
      throw new sfValidatorError(
        $this,
        'mime_types',
        array('mime_types' => $mimeTypes, 'mime_type' => $mimeType)
      );
    }
  }

  /**
   * @see sfValidatorBase
   */
  protected function isEmpty($value)
  {
    $noUploadedFile =
      isset($value['upload']['error'])
      &&
      UPLOAD_ERR_NO_FILE === $value['upload']['error']
    ;

    $emptyFile = !isset($value['file']) || !$value['file'];

    $fileToBeDeleted = isset($value['delete']) && $value['delete'];

    return
      !is_array($value)
      ||
      (
        $noUploadedFile
        &&
        ($emptyFile || $fileToBeDeleted)
      )
    ;
  }
}
