<?php

class herdditValidateusersTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
            new sfCommandOption('subreddit', null, sfCommandOption::PARAMETER_OPTIONAL, 'An alternate subreddit location', ProjectConfiguration::getDefaultSubredditAddress()),
        ));

        $this->namespace = str_replace(' ', '-', strtolower(ProjectConfiguration::getApplicationName()));
        $namespace = $this->namespace;
        $this->name = 'validate-users';
        $this->briefDescription = 'Validates users against keys taken from subreddits';
        $this->detailedDescription = <<<EOF
The [$namespace:validate-users|INFO] task downloads a collection of validation
keys from a subreddit to validate usernames.

It's recommended to run this task at least once a day and no more than once
every fifteen minutes. Best for starting would be once every two hours.

Call it with:

  [php symfony $namespace:validate-users|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
        $applicationConfig = sfProjectConfiguration::getApplicationConfiguration('frontend', 'prod', true);
        $context = sfContext::createInstance($applicationConfig);

        // Go to the Subreddit and obtain the past few keys.
        $reddit_location = $options['subreddit'];

        $reddit = new RedditObject($reddit_location);

        $quiet = (bool) $options['quiet'];

        if (!$quiet)
            echo "Obtaining the most recent comments from $reddit_location...";
        $reddit->appendData();
        $found_keys = count($reddit->getComments());

        if (!$quiet)
            echo "\nFound $found_keys keys.  Updating keys in the database...";
        ValidationTable::getInstance()->storeNewKeys($reddit->getComments());

        // Now that new keys are stored in the database we need to update all applicable users
        $users = sfGuardUserTable::getInstance()->getUsersToBeValidated();
        
        $updated = sfGuardUserTable::getInstance()->validateUsers($users);
        
        if (!$quiet)
            echo "\nSending emails...";
        
        foreach ($users as $user_id) {
            $sf_user = $context->getUser();
            $sf_user->setApiUserId($user_id);
            $sf_user->sendMail('RedditValidationSucceeded');
        }

        if (!$quiet)
            echo "\n$updated users validated and email sent.\n";
    }

}
