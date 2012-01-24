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

    public function executeUpload_audio(sfWebRequest $request)
    {
        $id = $request->getParameter('id');
        $filename = $request->getParameter('name');

        $this->forward404Unless($id && $filename);

        $valid_episode = $this->validateEpisodeForAudioUpload($id, $filename);

        $this->forward404Unless($valid_episode);

        // Settings
        $targetDir = rtrim(ProjectConfiguration::getEpisodeAudioFileLocalDirectory(), '/');
        //$targetDir = 'uploads/';
        //$cleanupTargetDir = false; // Remove old files
        //$maxFileAge = 60 * 60; // Temp file age in seconds
        // 5 minutes execution time
        @set_time_limit(5 * 60);

        $fileName = $this->getUser()->getAttribute('valid_episode_audio_file_hash', '');

        return $this->handlePlupload($request, $targetDir, $fileName);
    }

    public function executeUpload_image(sfWebRequest $request)
    {
        $id = $request->getParameter('id');
        $filename = $request->getParameter('name');

        $this->forward404Unless($id && $filename);

        $valid_episode = $this->validateEpisodeForImageUpload($id, $filename);

        $this->forward404Unless($valid_episode);

        // Settings
        $targetDir = rtrim(ProjectConfiguration::getEpisodeGraphicFileLocalDirectory(), '/');
        //$targetDir = 'uploads/';
        //$cleanupTargetDir = false; // Remove old files
        //$maxFileAge = 60 * 60; // Temp file age in seconds
        // 5 minutes execution time
        @set_time_limit(5 * 60);

        $fileName = $this->getUser()->getAttribute('valid_episode_image_file_hash', '');

        return $this->handlePlupload($request, $targetDir, $fileName);
    }

    public function executeUpload_subreddit_intro(sfWebRequest $request)
    {
        $id = $request->getParameter('id');
        $filename = $request->getParameter('name');

        $this->forward404Unless($id && $filename);

        $valid_episode = $this->validateSubredditForIntroUpload($id, $filename);

        $this->forward404Unless($valid_episode);

        // Settings
        $targetDir = rtrim(ProjectConfiguration::getSubredditAudioFileLocalDirectory(), '/');
        //$targetDir = 'uploads/';
        //$cleanupTargetDir = false; // Remove old files
        //$maxFileAge = 60 * 60; // Temp file age in seconds
        // 5 minutes execution time
        @set_time_limit(5 * 60);

        $fileName = $this->getUser()->getAttribute('valid_subreddit_intro_file_hash', '');

        return $this->handlePlupload($request, $targetDir, $fileName);
    }

    public function executeUpload_subreddit_outro(sfWebRequest $request)
    {
        $id = $request->getParameter('id');
        $filename = $request->getParameter('name');

        $this->forward404Unless($id && $filename);

        $valid_episode = $this->validateSubredditForOutroUpload($id, $filename);

        $this->forward404Unless($valid_episode);

        // Settings
        $targetDir = rtrim(ProjectConfiguration::getSubredditAudioFileLocalDirectory(), '/');
        //$targetDir = 'uploads/';
        //$cleanupTargetDir = false; // Remove old files
        //$maxFileAge = 60 * 60; // Temp file age in seconds
        // 5 minutes execution time
        @set_time_limit(5 * 60);

        $fileName = $this->getUser()->getAttribute('valid_subreddit_outro_file_hash', '');

        return $this->handlePlupload($request, $targetDir, $fileName);
    }

    public function executeTest(sfWebRequest $request)
    {
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
                sfConfig::get('app_web_app_audio_hash_salt')
                . $this->getUser()->getAttribute('valid_episode_id')
                . $this->getUser()->getAttribute('valid_episode_user_id')
        );
        return $hash . '.' . $extension;
    }

    protected function generateFilenameHashForSubredditIntro($filename)
    {
        $pattern = '/\.([^\.]+)$/';
        preg_match($pattern, $filename, $matches);
        $extension = (array_key_exists(1, $matches) ? $matches[1] : '');

        $hash = sha1(
                sfConfig::get('app_web_app_audio_hash_salt')
                . $this->getUser()->getAttribute('valid_subreddit_id')
                . $this->getUser()->getAttribute('valid_subreddit_domain')
                . 'intro'
        );
        return $hash . '.' . $extension;
    }

    protected function generateFilenameHashForSubredditOutro($filename)
    {
        $pattern = '/\.([^\.]+)$/';
        preg_match($pattern, $filename, $matches);
        $extension = (array_key_exists(1, $matches) ? $matches[1] : '');

        $hash = sha1(
                sfConfig::get('app_web_app_audio_hash_salt')
                . $this->getUser()->getAttribute('valid_subreddit_id')
                . $this->getUser()->getAttribute('valid_subreddit_domain')
                . 'outro'
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
                if ($this->getUser()->getApiUserId() == $episode->getEpisodeAssignment()->getSfGuardUserId() && !$episode->getIsApproved()) {
                    $valid_episode = true;
                    $this->getUser()->setAttribute('valid_episode', true);
                    $this->getUser()->setAttribute('valid_episode_id', $id);
                    $this->getUser()->setAttribute('valid_episode_user_id', $episode->getEpisodeAssignment()->getSfGuardUserId());
                    $this->getUser()->setAttribute('valid_episode_audio_file_hash', $this->generateFilenamehashForAudio($filename));
                    $episode->setAudioFile($this->generateFilenamehashForAudio($filename));
                    $episode->setNiceFilename($filename);
                    $episode->setSkipBackup(true);
                    $episode->save();
                }
            }
        }

        return $this->getUser()->getAttribute('valid_episode', false);
    }

    protected function validateSubredditForIntroUpload($id, $filename)
    {
        if (is_null($this->getUser()->getAttribute('valid_subreddit', null))) {
            // Base value is false
            $this->getUser()->setAttribute('valid_subreddit', false);

            $subreddit = EpisodeTable::getInstance()->find($id);
            if ($subreddit) {
                // Check if the current user has permission to edit the deadline.
                $membership_data = Api::getInstance()->setUser($auth_key)->get('subredditmembership?sf_guard_user_id=' . $this->getUser()->getApiUserId() . '&subreddit_id=' . $id, true);
                $membership = is_array($membership_data['body']) && array_key_exists(0, $membership_data['body']) ? ApiDoctrine::createQuickObject($membership_data['body'][0]) : null;
                $valid_admin = (bool) ($membership
                        && in_array($membership->getMembership()->getType(), array(
                            'admin',
                        )));
                if ($valid_admin || $this->getUser()->isSuperAdmin()) {
                    $valid_subreddit = true;
                    $this->getUser()->setAttribute('valid_subreddit', true);
                    $this->getUser()->setAttribute('valid_subreddit_id', $id);
                    $this->getUser()->setAttribute('valid_subreddit_domain', $subreddit->getDomain());
                    $this->getUser()->setAttribute('valid_subreddit_audio_file_hash', $this->generateFilenameHashForSubredditIntro($filename));
                    $subreddit->setEpisodeIntro($this->generateFilenameHashForSubredditIntro($filename));
                    $subreddit->setSkipBackup(true);
                    $subreddit->save();
                }
            }
        }

        return $this->getUser()->getAttribute('valid_subreddit', false);
    }
    
    protected function validateSubredditForOutroUpload($id, $filename)
    {
        if (is_null($this->getUser()->getAttribute('valid_subreddit', null))) {
            // Base value is false
            $this->getUser()->setAttribute('valid_subreddit', false);

            $subreddit = EpisodeTable::getInstance()->find($id);
            if ($subreddit) {
                // Check if the current user has permission to edit the deadline.
                $membership_data = Api::getInstance()->setUser($auth_key)->get('subredditmembership?sf_guard_user_id=' . $this->getUser()->getApiUserId() . '&subreddit_id=' . $id, true);
                $membership = is_array($membership_data['body']) && array_key_exists(0, $membership_data['body']) ? ApiDoctrine::createQuickObject($membership_data['body'][0]) : null;
                $valid_admin = (bool) ($membership
                        && in_array($membership->getMembership()->getType(), array(
                            'admin',
                        )));
                if ($valid_admin || $this->getUser()->isSuperAdmin()) {
                    $valid_subreddit = true;
                    $this->getUser()->setAttribute('valid_subreddit', true);
                    $this->getUser()->setAttribute('valid_subreddit_id', $id);
                    $this->getUser()->setAttribute('valid_subreddit_domain', $subreddit->getDomain());
                    $this->getUser()->setAttribute('valid_subreddit_audio_file_hash', $this->generateFilenameHashForSubredditOutro($filename));
                    $subreddit->setEpisodeOutro($this->generateFilenameHashForSubredditOutro($filename));
                    $subreddit->setSkipBackup(true);
                    $subreddit->save();
                }
            }
        }

        return $this->getUser()->getAttribute('valid_subreddit', false);
    }

    protected function generateFilenameHashForImage($filename)
    {
        $pattern = '/\.([^\.]+)$/';
        preg_match($pattern, $filename, $matches);
        $extension = (array_key_exists(1, $matches) ? $matches[1] : '');

        $hash = sha1(
                sfConfig::get('app_web_app_image_hash_salt')
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
                if ($this->getUser()->getApiUserId() == $episode->getEpisodeAssignment()->getSfGuardUserId() && !$episode->getIsApproved()) {
                    $valid_episode = true;
                    $this->getUser()->setAttribute('valid_episode', true);
                    $this->getUser()->setAttribute('valid_episode_id', $id);
                    $this->getUser()->setAttribute('valid_episode_user_id', $episode->getEpisodeAssignment()->getSfGuardUserId());
                    $this->getUser()->setAttribute('valid_episode_image_file_hash', $this->generateFilenameHashForImage($filename));
                    $episode->setGraphicFile($this->generateFilenameHashForImage($filename));
                    $episode->setSkipBackup(true);
                    $episode->save();
                }
            }
        }

        return $this->getUser()->getAttribute('valid_episode', false);
    }

    protected function handlePlupload(sfWebRequest $request, $targetDir, $fileName)
    {
        // Uncomment this one to fake upload time
        //usleep(5000);
        // Get parameters
        $chunk = $request->getParameter('chunk', 0);
        $chunks = $request->getParameter('chunks', 0);


        // Clean the fileName for security reasons
        $fileName = preg_replace('/[^\w\._]+/', '', $fileName);

        // Make sure the fileName is unique but only if chunking is disabled
        if ($chunks < 2 && file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName)) {
            // Since the file exists and we haven't started chunking, we'll delete the file before beginning a new upload.
            while (file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName))
                unlink($targetDir . DIRECTORY_SEPARATOR . $fileName);
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
