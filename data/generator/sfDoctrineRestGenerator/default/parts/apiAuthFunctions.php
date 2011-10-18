  public function checkApiAuth($params, $content = null)
  {
    return true;
  }

  public function getApiAuthValidators()
  {
    $validators = array();

    $validators['api_key'] = new sfValidatorString(array(
      'required' => true,
    ));
    $validators['time'] = new sfValidatorString(array(
      'required' => true,
    ));
    $validators['signature'] = new sfValidatorString(array(
      'required' => true,
    ));
    return $validators;
  }
  
  public function extractParameters($names)
  {
    $array = array();

    $parameters = $this->parameterHolder->getAll();
    foreach ($parameters as $key => $value)
    {
      if (in_array($key, $names))
      {
        $array[$key] = $value;
      }
    }

    return $array;
  }
  
  public function getApiAuthFields()
  {
    $fields = array(
      'api_key'   => 'api_key',
      'time'      => 'time',
      'signature' => 'signature',
    );
    return $fields;
  }

  public function getApiAuthFieldValues($parameters, $content = null)
  {
    $names = $this->getApiAuthFields();
    
    $array = array();
    
    if ($content)
    {
      foreach(explode('&', $content) as $var)
      {
        $var = explode('=', $var);
        if (in_array($var[0], $names))
        {
          $array[$var[0]] = (array_key_exists(1, $var) ? $var[1] : null);
        }
      }
    }
    
    foreach ($parameters as $key => $value)
    {
      if (in_array($key, $names))
      {
        $array[$key] = $value;
      }
    }
        
    if (empty($array) && $content)
    {
      $params = $this->parsePayload($content, false, false);
      foreach ($params as $key => $value)
      {
        if (in_array($key, $names))
        {
          $array[$key] = $value;
        }
      }
    }

    return $array;
  }

  public function validateApiAuth($parameters, $content = null)
  {
    $params = $this->getApiAuthFieldValues($parameters, $content);
    $validators = $this->getApiAuthValidators();
    $this->validate($params, $validators);
    $this->checkApiAuth($params);
  }