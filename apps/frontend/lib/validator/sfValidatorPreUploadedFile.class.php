<?php
/**
 * Validator for a file that has been uploaded
 *
 * @package     rbUtilityPlugin
 * @subpackage  validator
 * @author      Kevin Dew <kev@redbullet.co.uk>
 * @version     SVN $Id: sfValidatorPreUploadedFile.class.php 206 2010-12-15 10:52:42Z kevin $
 */
class sfValidatorPreUploadedFile extends sfValidatorUploadedFileAbstract
{
  /**
   * Configures the current validator.
   *
   * Available options:
   *
   *  * return_with_filename:     Return the validated result as an array with
   *                              filename and the original filename
   *
   * @param array $options   An array of options
   * @param array $messages  An array of error messages
   *
   * @see   parent
   */
  protected function configure($options = array(), $messages = array())
  {
    parent::configure($options, $messages);
    $this->addOption('return_with_name', false);
  }

  /**
   * @see   parent
   */
  protected function doClean($value)
  {
    if (!is_array($value))
    {
      $value['file'] = $value;
    }

    if (!isset($value['name']))
    {
      $value['name'] = '';
    }

    if (!isset($value['type']))
    {
      $value['type'] = 'application/octet-stream';
    }

    $value['size'] = sprintf('%u', filesize($value['file']));

    // check file exists
    if (!file_exists($value['file']))
    {
      throw new sfValidatorError(
        $this,
        'file_not_found'
      );
    }

    // check mime type
    $mimeType = $this->getMimeType($value['file'], $value['type']);

    $mimeTypes = $this->getMimeTypes();

    if (!$this->checkMimeType($mimeType, $mimeTypes))
    {
      throw new sfValidatorError(
        $this,
        'mime_types',
        array('mime_types' => $mimeTypes, 'mime_type' => $mimeType)
      );
    }

    // check max size
    // check file size
    if (
      $this->hasOption('max_size')
      &&
      $this->getOption('max_size') < (int) $value['size']
    )
    {
      throw new sfValidatorError(
        $this,
        'max_size',
        array(
          'max_size' => $this->getOption('max_size'),
          'size' => (int) $value['size']
        )
      );
    }

    $class = $this->getOption('validated_file_class');

    $validatedFile = new $class(
      $value['name'],
      $mimeType,
      $value['file'],
      $value['size'],
      $this->getOption('path')
    );

    return $this->getOption('return_with_name') 
      ? array(
        'file' => $this->processUpload($validatedFile),
        'name' => $value['name']
      )
      : $this->processUpload($validatedFile)
    ;
  }
}
