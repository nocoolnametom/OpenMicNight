<?php

class herdditCleanupfilesTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
        ));

        $this->namespace = str_replace(' ', '-', strtolower(ProjectConfiguration::getApplicationName()));
        $namespace = $this->namespace;
        $this->name = 'cleanup-files';
        $this->briefDescription = 'Remove old API info from the database';
        $this->detailedDescription = <<<EOF
The [$namespace:cleanup-files|INFO] task removes old files from the app.

It's recommended to run this task at least once a day and no more than once
every fifteen minutes.  Best for starting would be once every six hours.

Call it with:

  [php symfony $namespace:cleanup-files|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $quiet = (bool) $options['quiet'];

        if (!$quiet)
            echo "Removing files...";
        AuthFailureTable::getInstance()->cleanUpOldFailures();
        
        if (!$quiet)
            echo "\n";
    }

}
