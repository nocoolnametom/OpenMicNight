<?php

class herdditAdvanceepisodesTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('subreddit', sfCommandArgument::OPTIONAL, 'The name of any specific subreddit', '%'),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
                // add your own options here
        ));

        $this->namespace = str_replace(' ', '-', strtolower(ProjectConfiguration::getApplicationName()));
        $namespace = $this->namespace;
        $this->name = 'advance-episodes';
        $this->briefDescription = 'Advances EpisodeAssignments';
        $this->detailedDescription = <<<EOF
The [$namespace:advance-episodes|INFO] task runs the "engine" of the app by
advancing currenlt Episodes according to the rules defined by their subreddit
admins.  It is also responsible for alerting users of when their connection with
an Episode is valid.

It's recommended to run this task at least once a day and no more than once
every half-hour.  Best for starting would be once every two hours.

Call it with:

  [php symfony $namespace:advance-episodes|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
        $applicationConfig = sfProjectConfiguration::getApplicationConfiguration('frontend', 'prod', true);
        $context = sfContext::createInstance($applicationConfig);

        ProjectConfiguration::registerCron();

        $quiet = (bool) $options['quiet'];

        $passed = array();

        $assigned = array();

        if ($arguments['subreddit'] == '%') {

            if (!$quiet)
                echo "Advancing EpisodeAssignments for all Subreddits...";
            SubredditTable::getInstance()->advanceEpisodeAssignments();
        } else {
            $subreddit = SubredditTable::getInstance()->findOneByName($arguments['subreddit']);
            if ($subreddit) {
                if (!$quiet)
                    echo "Advancing EpisodeAssignments for $subreddit Subreddit...";
                $subreddit->advanceEpisodeAssignments();
            } else {
                throw new sfException('Cannot find Subreddit: ' . $arguments['subreddit']);
            }
        }

        if (!$quiet)
            echo "\n";
    }

}
