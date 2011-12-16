<?php
/**
 * Validator for a uploaded file
 *
 * Uses code from sfValidatorFile
 *
 * @package     rbUtilityPlugin
 * @subpackage  validator
 * @author      Kevin Dew <kev@redbullet.co.uk>
 * @version     SVN $Id: sfValidatorUploadedFileAbstract.class.php 185 2010-12-09 21:29:43Z kevin $
 */
abstract class sfValidatorUploadedFileAbstract extends sfValidatorBase
{
  /**
   * Configures the current validator.
   *
   * Available options:
   *
   *  * generate_filename_function:
   *                              A function to be called by call_user_func
   *                              to generate the files filename (Optional)
   *  * generate_filename_function_args:
   *                              An array of extra arguments for the above
   *                              function (Optional)
   *  * post_save_processing_function:
   *                              A function to be called by call_user_func
   *                              to process the uploaded file (Optional)
   *  * post_save_processing_function_args:
   *                              An array of extra arguments for the above
   *                              function (Optional)
   *
   * @param array $options   An array of options
   * @param array $messages  An array of error messages
   *
   * @see   parent
   */
  protected function configure($options = array(), $messages = array())
  {
    parent::configure($options, $messages);
    $this->addOption('generate_filename_function', null);
    $this->addOption('generate_filename_function_args', array());
    $this->addOption('post_save_processing_function', null);
    $this->addOption('post_save_processing_function_args', array());
    $this->addOption('max_size');
    $this->addOption('mime_types');
    $this->addOption('mime_type_guessers', array(
      array($this, 'guessFromFileinfo'),
      array($this, 'guessFromMimeContentType'),
      array($this, 'guessFromFileBinary'),
    ));
    $this->addOption('mime_categories', array(
      'web_images' => array(
        'image/jpeg',
        'image/pjpeg',
        'image/png',
        'image/x-png',
        'image/gif',
    )));
    $this->addOption('validated_file_class', 'sfValidatedFile');
    $this->addOption('path', null);

    $this->addMessage(
      'max_size', 'File is too large (maximum is %max_size% bytes).'
    );
    $this->addMessage('mime_types', 'Invalid mime type (%mime_type%).');
    $this->addMessage(
      'file_not_found', 'File was not found'
    );
  }
  
  protected function generateFilename($originalFilename)
  {
    $generatedFilename = null;
    
    if ($this->getOption('generate_filename_function'))
    {
      if (!is_callable($this->getOption('generate_filename_function')))
      {
        throw new Exception('Generate filename function is not callable');
      }

      $generatedFilename = call_user_func_array(
        $this->getOption('generate_filename_function'), 
        array_merge(
          array($originalFilename),
          $this->getOption('generate_filename_function_args')
        )
      );
    }    
    
    return $generatedFilename;
  }

  protected function postSaveProcessing($filePath)
  {
    if ($this->getOption('post_save_processing_function'))
    {
      if (!is_callable($this->getOption('post_save_processing_function')))
      {
        throw new Exception('Post save processing function is not callable');
      }

      call_user_func_array(
        $this->getOption('post_save_processing_function'),
        array_merge(
          array($filePath),
          $this->getOption('post_save_processing_function_args')
        )
      );
    }
  }

  /**
   * Returns the mime type of a file.
   *
   * This methods call each mime_type_guessers option callables to
   * guess the mime type.
   *
   * This method always returns a lower-cased string as mime types are case-insensitive
   * as per the RFC 2616 (http://www.w3.org/Protocols/rfc2616/rfc2616-sec3.html#sec3.7).
   *
   * @param  string $file      The absolute path of a file
   * @param  string $fallback  The default mime type to return if not guessable
   *
   * @return string The mime type of the file (fallback is returned if not guessable)
   */
  protected function getMimeType($file, $fallback)
  {
    foreach ($this->getOption('mime_type_guessers') as $method)
    {
      $type = call_user_func($method, $file);

      if (null !== $type && $type !== false)
      {
        return strtolower($type);
      }
    }

    return strtolower($fallback);
  }

  /**
   * Guess the file mime type with PECL Fileinfo extension
   *
   * @param  string $file  The absolute path of a file
   *
   * @return string The mime type of the file (null if not guessable)
   */
  protected function guessFromFileinfo($file)
  {
    if (!function_exists('finfo_open') || !is_readable($file))
    {
      return null;
    }

    if (!$finfo = new finfo(FILEINFO_MIME))
    {
      return null;
    }

    $type = $finfo->file($file);

    // remove charset (added as of PHP 5.3)
    if (false !== $pos = strpos($type, ';'))
    {
      $type = substr($type, 0, $pos);
    }

    return $type;
  }

  /**
   * Guess the file mime type with mime_content_type function (deprecated)
   *
   * @param  string $file  The absolute path of a file
   *
   * @return string The mime type of the file (null if not guessable)
   */
  protected function guessFromMimeContentType($file)
  {
    if (!function_exists('mime_content_type') || !is_readable($file))
    {
      return null;
    }

    return mime_content_type($file);
  }

  /**
   * Guess the file mime type with the file binary (only available on *nix)
   *
   * @param  string $file  The absolute path of a file
   *
   * @return string The mime type of the file (null if not guessable)
   */
  protected function guessFromFileBinary($file)
  {
    ob_start();
    //need to use --mime instead of -i. see #6641
    passthru(sprintf('file -b --mime %s 2>/dev/null', escapeshellarg($file)), $return);
    if ($return > 0)
    {
      ob_end_clean();

      return null;
    }
    $type = trim(ob_get_clean());

    if (!preg_match('#^([a-z0-9\-]+/[a-z0-9\-]+)#i', $type, $match))
    {
      // it's not a type, but an error message
      return null;
    }

    return $match[1];
  }

  protected function getMimeTypesFromCategory($category)
  {
    $categories = $this->getOption('mime_categories');

    if (!isset($categories[$category]))
    {
      throw new InvalidArgumentException(
        sprintf('Invalid mime type category "%s".', $category)
      );
    }

    return $categories[$category];
  }

  protected function getMimeTypes()
  {
    if ($this->hasOption('mime_types'))
    {
      $mimeTypes = is_array($this->getOption('mime_types'))
        ? $this->getOption('mime_types')
        : $this->getMimeTypesFromCategory($this->getOption('mime_types')
      );
      
      return $mimeTypes;
    }

    return array();
  }

  protected function checkMimeType($mimeType, $mimeTypes)
  {
    if ($mimeTypes)
    {
      return in_array($mimeType, array_map('strtolower', $mimeTypes));
    }

    return true;
  }

  /**
   * Saves the file and performs any actions specified for it
   *
   * @param   array   $upload   an array of data for a file upload
   * @return  string
   */
  protected function processUpload(sfValidatedFile $validatedFile)
  {
    $filePath = rtrim($this->getOption('path'), '/');

    $generatedFilename = $this->generateFilename(
      isset($upload['name']) ? $upload['name'] : null
    );

    $filename = $validatedFile->save($generatedFilename);

    $this->postSaveProcessing($filePath . '/' . $filename);

    return $filename;
  }
}
