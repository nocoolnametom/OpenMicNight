  /**
   * Applies the update validators to the payload posted to the service
   *
   * @param   string   $payload  A payload string
   */
  public function validateUpdate($payload, sfWebRequest $request = null)
  {
    $params = $this->parsePayload($payload);

    $validators = $this->getUpdateValidators();
    $this->validate($params, $validators);

    $postvalidators = $this->getUpdatePostValidators();
    $this->postValidate($params, $postvalidators);
  }
