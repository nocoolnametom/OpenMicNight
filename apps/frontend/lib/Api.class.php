<?php

/**
 * Description of Api
 *
 * @author doggetto
 */
class Api
{

    /** @var string */
    protected $_key;

    /** @var string */
    protected $_location;

    /** @var string */
    protected $_shared_secret;

    /** @var string */
    protected $_auth_key;

    /** @var Api */
    public $_api;

    /** @var string */
    protected $_format;

    /** @var sfResourceSerializer */
    protected $_serializer;
    protected $_requestBody;
    protected $_responseBody;
    protected $_responseInfo;

    public static function getInstance()
    {
        return new Api();
    }

    public function Api()
    {
        $this->_key = sfConfig::get('app_web_app_api_key');
        $this->_location = sfConfig::get('app_web_app_api_location');
        $this->_shared_secret = sfConfig::get('app_web_app_api_shared_secret');
    }

    public function setUser($auth_key)
    {
        $this->_auth_key = $auth_key;
        return $this;
    }

    protected function assembleApiAuthentication()
    {
        $time = time();
        $output = array();
        $output['api_key'] = $this->_key;
        $output['time'] = $time;
        $output['signature'] = sha1($this->_shared_secret . $time);
        if ($this->_auth_key)
            $output['auth_key'] = $this->_auth_key;

        $output_formatted = http_build_query($output);

        return $output_formatted;
    }

    protected function makeUrl($location)
    {
        return rtrim($this->_location, '/') . '/' . $location . (strpos($location, '?') !== false ? '&' : '?')
                . $this->assembleApiAuthentication();
    }

    protected function packagePayload($payload_array)
    {
        $format = $this->getFormat();
        $serializer = $this->getSerializer();

        $payload = $serializer->serialize($payload_array);

        if (!isset($payload) || !$payload) {
            throw new sfException(sprintf('Could not package payload as %s data!', $format));
        }

        return $payload;
    }

    protected function parsePayload($payload, $remove_api_stuff = true)
    {
        $format = $this->getFormat();
        $serializer = $this->getSerializer();

        if ($serializer && $payload != "") {
            $payload_array = $serializer->unserialize($payload);
        }

        if ($payload == "")
            $payload_array = array();

        if (!isset($payload_array) || !$payload_array) {
            if ($payload == "" || $payload == "[]" || $payload == "{}" || $payload = "()")
                $payload_array = array();
            else
                throw new sfException(sprintf('Could not parse payload, obviously not a valid %s data!', $format));
        }

        if ($remove_api_stuff) {
            $payload_array = array_diff_key($payload_array, array(
                'api_key' => 'api_key',
                'time' => 'time',
                'signature' => 'signature',
                'auth_key' => 'auth_key',
                    ));
        }

        return $payload_array;
    }

    protected function getFormat()
    {
        if (!isset($this->_format)) {
            $format = sfConfig::get('app_web_app_api_format', 'json');
            if (!in_array($format, array(
                        0 => 'json',
                        1 => 'xml',
                        2 => 'yaml',
                    ))) {
                throw new sfException(sprintf('This API does not support the format %s', $format));
            }
            $this->_format = $format;
        }
        return $this->_format;
    }

    protected function getSerializer()
    {
        if (!isset($this->_serializer)) {
            try {
                $this->_serializer = sfResourceSerializer::getInstance($this->getFormat());
            } catch (sfException $e) {
                $this->_serializer = sfResourceSerializer::getInstance('json');
                throw new sfException($e->getMessage());
            }
        }
        return $this->_serializer;
    }

    protected function setCurlOpts(&$curlHandle, $package = null)
    {
        // Set the timeout in seconds (removed because my Mac doesn't like this being so short)
        //curl_setopt($curlHandle, CURLOPT_TIMEOUT, 10);
        // Follow Redirects
        //curl_setopt($curlHandle, CURLOPT_FOLLOWLOCATION, true);
        // Return the results of the command
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);

        // Set the accept type to JSON
        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, array('Accept: application/json'));
    }

    protected function doExecute(&$curlHandle)
    {
        try {
            $this->setCurlOpts($curlHandle);
            $this->_responseBody = curl_exec($curlHandle);
            $this->_responseInfo = curl_getinfo($curlHandle);

            curl_close($curlHandle);
            
            if (in_array($this->_responseInfo['http_code'], array(301, 302)) && strlen($this->_responseInfo['redirect_url']) > 0) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $this->_responseInfo['redirect_url']);
                $this->doExecute($ch);
            }
        } catch (Exception $e) {
            curl_close($curlHandle);
            throw $e;
        }
    }

    protected function doResponse($remove_api_stuff = true)
    {
        $response = array(
            'headers' => $this->_responseInfo,
            'body' => $this->parsePayload($this->_responseBody, $remove_api_stuff),
        );
        return $response;
    }

    public function get($location, $remove_api_stuff = true)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->makeUrl($location));
        $this->doExecute($ch);
        return $this->doResponse($remove_api_stuff);
    }

    public function post($location, $package = array(), $remove_api_stuff = true)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->makeUrl($location));

        $request = $this->packagePayload($package);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_POST, 1);

        $this->doExecute($ch);
        return $this->doResponse($remove_api_stuff);
    }

    public function put($location, $package = array(), $remove_api_stuff = true)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->makeUrl($location));

        $request = $this->packagePayload($package);

        $requestLength = strlen($request);

        $fh = fopen('php://memory', 'rw');
        fwrite($fh, $request);
        rewind($fh);

        curl_setopt($ch, CURLOPT_INFILE, $fh);
        curl_setopt($ch, CURLOPT_INFILESIZE, $requestLength);
        curl_setopt($ch, CURLOPT_PUT, true);

        $this->doExecute($ch);

        fclose($fh);
        return $this->doResponse($remove_api_stuff);
    }

    public function delete($location, $remove_api_stuff = true)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->makeUrl($location));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

        $this->doExecute($ch);
        return $this->doResponse($remove_api_stuff);
    }

}