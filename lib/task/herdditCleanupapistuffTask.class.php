<?php

class herdditCleanupapistuffTask extends sfBaseTask
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

        $this->namespace = str_replace(' ', '-', strtolower(ProjectConfiguration::getApplicationName()));
        $namespace = $this->namespace;
        $this->name = 'cleanup-api-stuff';
        $this->briefDescription = '';
        $this->detailedDescription = <<<EOF
The [$namespace:cleanup-api-stuff|INFO] task removes old information regarding API authorization from the database.
Call it with:

  [php symfony $namespace:cleanup-api-stuff|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $quiet = (bool) $options['quiet'];

        if (!$quiet)
            echo "Removing old authorization failures...";
        AuthFailureTable::getInstance()->cleanUpOldFailures();
        
        if (!$quiet)
            echo "\nRemoving expired API user authorization tokens...";
        sfGuardUserAuthKeyTable::getInstance()->cleanUpTokens();
        
        if (!$quiet)
            echo "\n";
    }

}
