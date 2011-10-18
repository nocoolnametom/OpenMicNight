  public function parsePayload($payload, $force = false, $remove_api = true)
  {
    if ($force || !isset($this->_payload_array))
    {
      $format = $this->getFormat();
      $serializer = $this->getSerializer();

      if ($serializer)
      {
        $payload_array = $serializer->unserialize($payload);
      }

      if (!isset($payload_array) || !$payload_array)
      {
        throw new sfException(sprintf('Could not parse payload, obviously not a valid %s data!', $format));
      }

      $filter_params = <?php var_export(array_flip(array_merge(
        $this->configuration->getValue('get.global_additional_fields', array()),
        $this->configuration->getValue('get.object_additional_fields', array())
      ))) ?>;

      $this->_payload_array = array_diff_key($payload_array, $filter_params);
    }
    
    if ($remove_api)
    {
      $api_auth_params = $this->getApiAuthFields();
      $this->_payload_array = array_diff_key($this->_payload_array, $api_auth_params);
    }

    return $this->_payload_array;
  }
