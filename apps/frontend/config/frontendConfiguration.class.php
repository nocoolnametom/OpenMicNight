<?php

class frontendConfiguration extends sfApplicationConfiguration
{
  public function configure()
  {
      $this->dispatcher->connect('debug.web.load_panels', array(
          'hrWebDebugPanelApiCalls',
          'listenToLoadDebugWebPanelEvent'
      ));
  }
}
