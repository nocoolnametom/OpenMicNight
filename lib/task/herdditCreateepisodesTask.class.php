<?php

class herdditCreateepisodesTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('subreddit', sfCommandArgument::OPTIONAL, 'The name of any specific subreddit', '%'),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'api_v1'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
        ));

        $this->namespace = str_replace(' ', '-', strtolower(ProjectConfiguration::getApplicationName()));
        $namespace = $this->namespace;
        $this->name = 'create-episodes';
        $this->briefDescription = 'Generates Episodes for all Subreddits without future Episodes.';
        $this->detailedDescription = <<<EOF
The [$namespace:create-episodes|INFO] task generates empty Episode objects for
those Subreddits not having any future Episodes.  Calling with with a specific
Subreddit name will generate Episode objects for only that Subreddit, assuming
that it does not have future Episodes.

It's recommended to run this task at least once a day and no more than once
every half-hour.  Best for starting would be once every two hours.

Call it with:

  [php symfony $namespace:create-episodes ]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        ProjectConfiguration::registerCron();

        $quiet = (bool) $options['quiet'];

        $subreddits = Doctrine::getTable('Subreddit')
                ->getSubredditsNeedingEpisodeGeneration($arguments['subreddit']);

        foreach ($subreddits as $subreddit) {
            $episodes = $subreddit->collectGeneratedEpisodes();
            foreach ($episodes as $episode) {
                $episode->save();
            }
        }
    }

}
