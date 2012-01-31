<?php

class herdditCreatedefaultdeadlinesTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('domain', sfCommandArgument::REQUIRED, 'The subreddit domain.'),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'api_v1'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
                // add your own options here
        ));

        $this->namespace = str_replace(' ', '-', strtolower(ProjectConfiguration::getApplicationName()));
        $namespace = $this->namespace;
        $this->name = 'create-default-deadlines';
        $this->briefDescription = '';
        $this->detailedDescription = <<<EOF
The [$namespace:create-default-deadlines|INFO] task creates a set of deadlines for a subreddit if that subreddit has no current deadlines.
Call it with:

  [php symfony $namespace:create-default-deadlines|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $quiet = (bool) $options['quiet'];
        $domain = $arguments['domain'];
        
        if (!$quiet)
            echo "Creating deadlines...";
        
        $subreddit = SubredditTable::getInstance()->findOneBy('domain', $domain);
        /* @var $subreddit Subreddit */
        if (!$subreddit)
            return new sfException("Cannot find subreddit by the domain name $domain.", 404);
        if (count($subreddit->getDeadlines()))
            return new sfException("Subreddit has deadlines already.");
        
        $authortype_one = AuthorTypeTable::getInstance()->findOneBy('type', 'first_place');
        $authortype_two = AuthorTypeTable::getInstance()->findOneBy('type', 'second_place');
        $authortype_three = AuthorTypeTable::getInstance()->findOneBy('type', 'third_place');
        $authortype_four = AuthorTypeTable::getInstance()->findOneBy('type', 'sudden_death');
        
        $deadlines = new Doctrine_Collection('Deadline');
        
        $deadlines[0] = new Deadline();
        $deadlines[0]->setAuthorType($authortype_one);
        $deadlines[0]->setSubreddit($subreddit);
        $deadlines[0]->setSeconds(259200);
        
        $deadlines[1] = new Deadline();
        $deadlines[1]->setAuthorType($authortype_two);
        $deadlines[1]->setSubreddit($subreddit);
        $deadlines[1]->setSeconds(172800);
        
        $deadlines[2] = new Deadline();
        $deadlines[2]->setAuthorType($authortype_three);
        $deadlines[2]->setSubreddit($subreddit);
        $deadlines[2]->setSeconds(86400);
        
        $deadlines[3] = new Deadline();
        $deadlines[3]->setAuthorType($authortype_four);
        $deadlines[3]->setSubreddit($subreddit);
        $deadlines[3]->setSeconds(0);
        
        $deadlines->save();
        
        if (!$quiet)
            echo "\nFinished.\n";
    }

}
