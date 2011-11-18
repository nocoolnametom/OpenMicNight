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
      //new sfCommandOption('quiet', 'q', sfCommandOption::PARAMETER_OPTIONAL, 'Silence output', 'false'),
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
    
    $quiet = (bool)$options['quiet'];
    
    if (!$quiet)
        echo "Obtaining the most recent comments from $reddit_location...";
    $reddit->appendData();
    $found_keys = count($reddit->getComments());
    
    if (!$quiet)
        echo "\nFound $found_keys keys.  Updating keys in the database...";
    ValidationTable::getInstance()->storeNewKeys($reddit->getComments());
    
    // Now that new keys are stored in the database we need to update all applicable users
    $updated = sfGuardUserTable::getInstance()->getNewlyValidatedUsers();
    
    if (!$quiet)
        echo "\n$updated users validated.\n";
  }
}
