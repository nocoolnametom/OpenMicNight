<?php

class herdditCreateepisodesTask extends sfBaseTask
{

    protected function configure()
    {
        // // add your own arguments here
        $this->addArguments(array(
            new sfCommandArgument('subreddit', sfCommandArgument::OPTIONAL, 'The name of any specific subreddit'),
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
        set_include_path(sfConfig::get('sf_lib_dir').'/vendor'.PATH_SEPARATOR.get_include_path());
        require_once 'Cron/FieldInterface.php';
        require_once 'Cron/AbstractField.php';
        require_once 'Cron/CronExpression.php';
        require_once 'Cron/DayOfMonthField.php';
        require_once 'Cron/DayOfWeekField.php';
        require_once 'Cron/FieldFactory.php';
        require_once 'Cron/HoursField.php';
        require_once 'Cron/MinutesField.php';
        require_once 'Cron/MonthField.php';
        require_once 'Cron/YearField.php';

        $subreddit_name = $arguments['subreddit'] != "" ? $arguments['subreddit']
                    : '%';


        $subquery = Doctrine_Query::create()
                ->select('Subreddit.id')
                ->from('Subreddit')
                ->leftJoin('Episode')
                ->groupBy('Episode.release_date')
                ->having('Episode.release_date > NOW()');
        $subreddits = @Doctrine_Query::create()
                ->from('Subreddit')
                ->where('Subreddit.name LIKE :name',
                        array(':name' => $subreddit_name))
                ->whereNotIn('Subreddit.id', $subquery)
                ->execute();


        foreach ($subreddits as $subreddit) {
            $episode_schedule = Cron\CronExpression::factory($subreddit->getEpisodeScheduleCronFormatted());

            $creation_schedule = Cron\CronExpression::factory($subreddit->getCreateNewEpisodesCronFormatted());

            $stop_creating = $creation_schedule->getNextRunDate();

            $episode_date = new DateTime(date('Y-m-d H:i:s', time()));
            while ($episode_schedule->getNextRunDate($episode_date)->getTimestamp()
            <= $stop_creating->getTimestamp()) {
                $episode_date = $episode_schedule->getNextRunDate($episode_date);

                $episode = new Episode();
                $episode->setSubreddit($subreddit);
                $episode->setReleaseDate($episode_date->format('Y-m-d H:i:s'));
                $episode->save();
            }
        }
    }

}
