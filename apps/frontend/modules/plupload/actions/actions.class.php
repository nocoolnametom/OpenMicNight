<?php

/**
 * plupload actions.
 *
 * @package    OpenMicNight
 * @subpackage plupload
 * @author     Tom Doggett
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class pluploadActions extends sfActions
{

    /**
     * Takes the web request and tries to save the uploaded file
     *
     * The web request is expected to have a form and a validator field name
     * passed as arguments. Once an upload is complete this action will initiate
     * the form class and get the validator with the name and pass an array of
     * data through the clean method.
     *
     * data for validator
     * array(
     *   name => $filename
     *   file => $pathToFile
     *   type => $mimeType
     * )
     *
     * Expected to return JSON. The 3 successful states it'll return is
     *
     * incomplete file:
     *    status: incomplate
     *
     * validation error:
     *    status: error
     *    message: $errorMessage
     *
     * comlete file:
     *    status: complete
     *    filename: $fileName (note not path)
     *
     * @see sfActions::execute
     */
    public function executeIndex(sfWebRequest $request)
    {
        $plupload = new sfPluploadUploadedFile(
                        $request->getParameter('chunk', 0),
                        $request->getParameter('chunks', 0),
                        $request->getParameter('name', 'file.tmp')
        );

        $plupload->processUpload(
                $request->getFiles($request->getParameter('file-data-name', 'file')), $plupload->getContentType($request)
        );

        $this->returnData = array();

        if (!$plupload->isComplete()) {
            $this->returnData = array(
                'status' => 'incomplete'
            );

            return;
        }

        $formClass = $request->getParameter('form');
        $validatorName = $request->getParameter('validator');

        if (!class_exists($formClass)) {
            throw new Exception('Form class doesn\'t exist');
        }

        $form = new $formClass();

        $validator = $form->getValidator($validatorName);

        try {
            $file = $validator->clean(array(
                'name' => $plupload->getOriginalFilename(),
                'file' => $plupload->getFilePath(),
                'type' => $plupload->getMimeType()
                    ));
        } catch (sfValidatorError $e) {
            $this->returnData = array(
                'status' => 'error',
                'message' => $e->getMessage()
            );
            return;
        }

        $this->returnData = array(
            'status' => 'complete',
            'file' => $file
        );
    }

    public function executeTest(sfWebRequest $request)
    {
        $this->plupload_location = rtrim(preg_replace_callback('/~([a-z0-1_\-]+)~/', create_function('$matches', 'return sfConfig::get($matches[1]);'), sfConfig::get('app_plupload_js_dir')), '/');
        $this->plupload_web_dir = sfConfig::get('app_plupload_web_dir');
        $this->getUser()->getAttributeHolder()->remove('valid_episode');
        $this->getUser()->getAttributeHolder()->remove('valid_episode_id');
        $this->getUser()->getAttributeHolder()->remove('valid_episode_user_id');
        $this->getUser()->getAttributeHolder()->remove('valid_episode_audio_file_hash');
        $this->getUser()->getAttributeHolder()->remove('valid_episode_image_file_hash');
        $this->getUser()->getAttributeHolder()->remove('valid_episode_user_id');
    }

    protected function generateFilenameHashForAudio($filename)
    {
        $pattern = '/\.([^\.]+)$/';
        preg_match($pattern, $filename, $matches);
        $extension = (array_key_exists(1, $matches) ? $matches[1] : '');

        $hash = sha1(
                'audio_file'
                . $this->getUser()->getAttribute('valid_episode_id')
                . $this->getUser()->getAttribute('valid_episode_user_id')
        );
        return $hash . '.' . $extension;
    }

    protected function validateEpisodeForAudioUpload($id, $filename)
    {
        if (is_null($this->getUser()->getAttribute('valid_episode', null))) {
            // Base value is false
            $this->getUser()->setAttribute('valid_episode', false);

            $episode = EpisodeTable::getInstance()->find($id);
            if ($episode) {
                if ($this->getUser()->getApiUserId() == $episode->getSfGuardUserId()) {
                    $valid_episode = true;
                    $this->getUser()->setAttribute('valid_episode', true);
                    $this->getUser()->setAttribute('valid_episode_id', $id);
                    $this->getUser()->setAttribute('valid_episode_user_id', $episode->getSfGuardUserId());
                    $this->getUser()->setAttribute('valid_episode_audio_file_hash', $this->generateFilenamehashForAudio($filename));
                    $episode->setAudioFile($this->generateFilenamehashForAudio($filename));
                    $episode->setNiceFilename($filename);
                    $episode->save();
                }
            }
        }

        return $this->getUser()->getAttribute('valid_episode', false);
    }

    protected function generateFilenameHashForImage($filename)
    {
        $pattern = '/\.([^\.]+)$/';
        preg_match($pattern, $filename, $matches);
        $extension = (array_key_exists(1, $matches) ? $matches[1] : '');

        $hash = sha1(
                'image_file'
                . $this->getUser()->getAttribute('valid_episode_id')
                . $this->getUser()->getAttribute('valid_episode_user_id')
        );
        return $hash . '.' . $extension;
    }

    protected function validateEpisodeForImageUpload($id, $filename)
    {
        if (is_null($this->getUser()->getAttribute('valid_episode', null))) {
            // Base value is false
            $this->getUser()->setAttribute('valid_episode', false);

            $episode = EpisodeTable::getInstance()->find($id);
            if ($episode) {
                if ($this->getUser()->getApiUserId() == $episode->getSfGuardUserId()) {
                    $valid_episode = true;
                    $this->getUser()->setAttribute('valid_episode', true);
                    $this->getUser()->setAttribute('valid_episode_id', $id);
                    $this->getUser()->setAttribute('valid_episode_user_id', $episode->getSfGuardUserId());
                    $this->getUser()->setAttribute('valid_episode_image_file_hash', $this->generateFilenamehashForAudio($filename));
                    $episode->setGraphicFile($this->generateFilenamehashForAudio($filename));
                    $episode->save();
                }
            }
        }

        return $this->getUser()->getAttribute('valid_episode', false);
    }

    public function executeUpload_audio(sfWebRequest $request)
    {
        $id = $request->getParameter('id');
        $filename = $request->getParameter('name');

        $this->forward404Unless($id && $filename);

        $valid_episode = $this->validateEpisodeForAudioUpload($id, $filename);

        $this->forward404Unless($valid_episode);

        $response = $this->getResponse();

        // HTTP headers for no cache etc
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");


        // Settings
        $targetDir = sfConfig::get("sf_upload_dir") . DIRECTORY_SEPARATOR . "plupload";
        //$targetDir = 'uploads/';
        //$cleanupTargetDir = false; // Remove old files
        //$maxFileAge = 60 * 60; // Temp file age in seconds
        // 5 minutes execution time
        @set_time_limit(5 * 60);

        // Uncomment this one to fake upload time
        //usleep(5000);
        // Get parameters
        $chunk = $request->getParameter('chunk', 0);
        $chunks = $request->getParameter('chunks', 0);
        $fileName = $this->getUser()->getAttribute('valid_episode_file_hash', '');


        // Clean the fileName for security reasons
        $fileName = preg_replace('/[^\w\._]+/', '', $fileName);

        // Make sure the fileName is unique but only if chunking is disabled
        if ($chunks < 2 && file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName)) {
            $ext = strrpos($fileName, '.');
            $fileName_a = substr($fileName, 0, $ext);
            $fileName_b = substr($fileName, $ext);

            $count = 1;
            while (file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName_a . '_' . $count . $fileName_b))
                $count++;

            $fileName = $fileName_a . '_' . $count . $fileName_b;
        }

        // Create target dir
        if (!file_exists($targetDir))
            @mkdir($targetDir);

        $contentType = null;

        // Look for the content type header
        if (isset($_SERVER["HTTP_CONTENT_TYPE"]))
            $contentType = $_SERVER["HTTP_CONTENT_TYPE"];

        if (isset($_SERVER["CONTENT_TYPE"]))
            $contentType = $_SERVER["CONTENT_TYPE"];

        // Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
        if (strpos($contentType, "multipart") !== false) {
            if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
                // Open temp file
                $out = fopen($targetDir . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
                if ($out) {
                    // Read binary input stream and append it to temp file
                    $in = fopen($_FILES['file']['tmp_name'], "rb");

                    if ($in) {
                        while ($buff = fread($in, 4096))
                            fwrite($out, $buff);
                    } else
                        die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
                    fclose($in);
                    fclose($out);
                    @unlink($_FILES['file']['tmp_name']);
                } else
                    die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
            } else
                die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
        } else {
            // Open temp file
            $out = fopen($targetDir . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
            if ($out) {
                // Read binary input stream and append it to temp file
                $in = fopen("php://input", "rb");

                if ($in) {
                    while ($buff = fread($in, 4096))
                        fwrite($out, $buff);
                } else
                    die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');

                fclose($in);
                fclose($out);
            } else
                die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
        }

        // Return JSON-RPC response
        die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
    }

}
