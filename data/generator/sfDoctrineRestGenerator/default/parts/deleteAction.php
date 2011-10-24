  /**
   * Removes a <?php echo $this->getModelClass() ?> object, based on its
   * primary key
   * @param   sfWebRequest   $request a request object
   * @return  string
   */
  public function executeDelete(sfWebRequest $request)
  {
<?php $primaryKey = $this->configuration->getValue('default.update_key', Doctrine::getTable($this->getModelClass())->getIdentifier()); ?>
    $this->forward404Unless($request->isMethod(sfRequest::DELETE));
    $primaryKey = $request->getParameter('<?php echo $primaryKey ?>');
    $this->forward404Unless($primaryKey);
    $parameters = $request->getParameterHolder()->getAll();
    $params = $this->getApiAuthFieldValues($parameters);
    try
    {
      $this->validateApiAuth($parameters);
      $this->validateDelete($params, $request);
    } catch (Exception $e) {
      $this->getResponse()->setStatusCode($e->getCode() ? $e->getCode() : 406);
      $serializer = $this->getSerializer();
      $this->getResponse()->setContentType($serializer->getContentType());
      $error = $e->getMessage();

      // event filter to enable customisation of the error message.
      $result = $this->dispatcher->filter(
        new sfEvent($this, 'sfDoctrineRestGenerator.filter_error_output'),
        $error
      )->getReturnValue();

      if ($error === $result)
      {
        $error = array(array('message' => $error));
        $this->output = $serializer->serialize($error, 'error');
      }
      else
      {
        $this->output = $serializer->serialize($result);
      }
      
      $request->setRequestFormat('html');
      $this->setTemplate('index');
      return sfView::SUCCESS;
    }
    $this->item = Doctrine::getTable($this->model)->findOneBy<?php echo sfInflector::camelize($primaryKey) ?>($primaryKey);
    $this->forward404Unless($this->item);
    $this->item->delete();
    return sfView::NONE;
  }
