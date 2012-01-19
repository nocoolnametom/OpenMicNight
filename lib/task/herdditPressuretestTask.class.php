<?php

class herdditPressuretestTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'test'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
        ));

        $this->namespace = str_replace(' ', '-', strtolower(ProjectConfiguration::getApplicationName()));
        $namespace = $this->namespace;
        $this->name = 'pressure-test';
        $this->briefDescription = '';
        $this->detailedDescription = <<<EOF
The [$namespace:pressure-test|INFO] task attempts to fill the database with tons
of data to speed things up.  Call it with:

  [php symfony $namespace:pressure-test|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $quiet = (bool) $options['quiet'];

        // add your code here
        
        $num_subreddits = 1000;
        $num_deadline = 5;
        $num_users = 5000;
        
        for($i = 0; $i < $num_subreddits; $i++)
        {
            $exists = SubredditTable::getInstance()->findOneBy('name', $i);
            if ($exists)
            {
                continue;
            }
            $subreddit = new Subreddit();
            $subreddit->setName($i);
            $subreddit->setDomain($i);
            $subreddit->setEpisodeScheduleCronFormatted('0 0 * * *');
            $subreddit->setCreateNewEpisodesCronFormatted('0 0 1 * *');
            $subreddit->save();
            if (!$quiet)
                echo $subreddit->getName() . ' ';
        }
        echo "\n Analyizing which need Episodes...";
        
        $subreddits = Doctrine::getTable('Subreddit')
                ->getSubredditsNeedingEpisodeGeneration();
        
        foreach($subreddits as $subreddit) {
            if (!$quiet)
                echo $subreddit->getName() . ' ';
            $episodes = $subreddit->collectGeneratedEpisodes();
            if (!$quiet)
                echo ' Generated...';
            $episodes->save();
            if (!$quiet)
                echo " Saved!\n";
        }
    }

}
