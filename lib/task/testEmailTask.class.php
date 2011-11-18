<?php

class testEmailTask extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    // $this->addArguments(array(
    //   new sfCommandArgument('my_arg', sfCommandArgument::REQUIRED, 'My argument'),
    // ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'api_v1'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      // add your own options here
    ));

    $this->namespace        = 'test';
    $this->name             = 'email';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [test:email|INFO] task does things.
Call it with:

  [php symfony test:email|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
    $applicationConfig = sfProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($applicationConfig);

    // add your code here
    /*$user = $context->getUser();
    $user->setApiUserId(1);
    $user->sendMail('ChangeRedditKey');*/
    
    $user = sfGuardUserTable::getInstance()->find(1);
    $user->setIsValidated(true);
    $user->save();
    $user->setIsValidated(false);
    $user->save();
  }
}
