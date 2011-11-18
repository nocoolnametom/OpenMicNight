<?php

class herdditValidateusersTask extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    // $this->addArguments(array(
    //   new sfCommandArgument('my_arg', sfCommandArgument::REQUIRED, 'My argument'),
    // ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      // add your own options here
    ));

    $this->namespace        = 'herddit';
    $this->name             = 'validate-users';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [herddit:validate-users|INFO] task does things.
Call it with:

  [php symfony herddit:validate-users|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

    // Go to the Subreddit and obtain the past few keys.
    $reddit_location = 'http://www.reddit.com/r/atheism';
        
    $reddit = new RedditObject($reddit_location);
    
    echo "Obtaining recent comments from $reddit_location...";
    $reddit->appendData();
    
    echo "\nStoring any new keys in the database...\n";
    ValidationTable::getInstance()->storeNewKeys($reddit->getComments());
    
    // Now that new keys are stored in the database we need to update all applicable users
    $updated = sfGuardUserTable::getInstance()->getNewlyValidatedUsers();
    
    echo "$updated users validated.\n";
  }
}
