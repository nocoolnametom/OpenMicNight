<?php

class herdditCreateepisodesTask extends sfBaseTask
{

    protected function configure()
    {
        // // add your own arguments here
        $this->addArguments(array(
            new sfCommandArgument('subreddit', sfCommandArgument::OPTIONAL, 'The name of any specific subreddit', '%'),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
                // add your own options here
        ));

        $this->namespace = 'herddit';
        $this->name = 'create-episodes';
        $this->briefDescription = 'Generates Episodes for all Subreddits without future Episodes.';
        $this->detailedDescription = <<<EOF
The [herddit:create-episodes|INFO] task generates empty Episode objects for those Subreddits not having any future Episodes.  Calling with with a specific Subreddit name will generate Episode objects for only that Subreddit, assuming that it does not have future Episodes.
Call it with:

  [php symfony herddit:create-episodes ]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        // add your code here
        ProjectConfiguration::registerCron();

        $subreddits = Doctrine::getTable('Subreddit')
                ->getSubredditsNeedingEpisodeGeneration($arguments['subreddit']);

        foreach ($subreddits as $subreddit) {
            $subreddit->generateEpisodes();
        }
    }

}
