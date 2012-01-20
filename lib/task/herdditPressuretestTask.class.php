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
        $num_deadlines = 5;
        $num_users = 3000;
        $create_episodes_attempt = 3;

        echo " Creating AuthorTypes...";
        $authortypes = new Doctrine_Collection('AuthorType');
        for ($i = 0; $i < $num_deadlines; $i++) {
            $exists = AuthorTypeTable::getInstance()->findOneBy('type', $i);
            if ($exists) {
                $authortypes[$i] = $exists;
            } else {
                $authortypes[$i] = new AuthorType();
            }
            $authortypes[$i]->setType($i);
            $authortypes[$i]->setDescription($i);
            if (!$quiet)
                echo $authortypes[$i]->getType() . ' ';
        }
        $authortypes->save();

        /*echo "\n Creating Subreddits...";
        $subreddits = new Doctrine_Collection('Subreddit');
        $x = 0;
        for ($i = 0; $i < $num_subreddits; $i++) {
            $exists = SubredditTable::getInstance()->findOneBy('name', $i);
            if ($exists) {
                if (!$quiet)
                    echo $exists->getName() . ' ';
                continue;
            }
            $subreddits[$x] = new Subreddit();
            $subreddits[$x]->setName($i);
            $subreddits[$x]->setDomain($i);
            $subreddits[$x]->setEpisodeScheduleCronFormatted('0 0 * * *');
            $subreddits[$x]->setCreateNewEpisodesCronFormatted('0 0 1 * *');
            if (!$quiet)
                echo $subreddits[$x]->getName() . ' ';
            $x++;
        }
        if ($x)
            $subreddits->save();

        echo "\n Creating Deadlines...";
        $deadlines = new Doctrine_Collection('Deadline');
        $x = 0;
        for ($i = 0; $i < $num_subreddits; $i++) {
            $subreddit = SubredditTable::getInstance()->findOneBy('name', $i);
            $deadlines = $subreddit->getDeadlines();
            if (!$quiet)
                echo $subreddit->getName() . ' ';
            if (count($deadlines) == 0) {
                for ($j = 0; $j < $num_deadlines; $j++) {
                    $deadlines[$x] = new Deadline();
                    $deadlines[$x]->setAuthorType($authortypes[$j]);
                    $deadlines[$x]->setSubreddit($subreddit);
                    $deadlines[$x]->setSeconds((24 / $num_deadlines) * $j);
                    $x++;
                }
            }
        }
        if ($x)
            $deadlines->save();
        unset($deadlines);

        echo "\n Creating fake users for signup...";

        $users = new Doctrine_collection("sfGuardUser");
        $x = 0;
        for ($i = 0; $i < $num_users; $i++) {
            $exists = sfGuardUserTable::getInstance()->findOneBy('username', $i);
            if ($exists) {
                $users[$x] = $exists;
                continue;
            } else {
                $users[$x] = new sfGuardUser();
            }
            $users[$x]->setUsername($i);
            $users[$x]->setEmailAddress($i);
            $users[$x]->setIsValidated(1);
            if (!$quiet)
                echo $users[$x]->getUsername() . ' ';
            $x++;
        }
        $users->save();
        
        echo "Joining to subreddits...";
        $memberships = new Doctrine_Collection('sfGuardUserSubredditMembership');
        $x = 0;
        $member = MembershipTable::getInstance()->findOneBy('type', 'member');
        foreach($users as $user)
        {
            echo $user->getUsername() .' ';
            foreach($subreddits as $subreddit)
            {
                $exists = sfGuardUserSubredditMembershipTable::getInstance()->getFirstByUserSubredditAndMemberships($user->getIncremented(), $subreddit->getIncremented(), array('member'));
                if ($exists)
                {
                    continue;
                }
                $memberships[$x] = new sfGuardUserSubredditMembership();
                $memberships[$x]->setSfGuardUserId($user->getIncremented());
                $memberships[$x]->setSubredditId($subreddit->getIncremented());
                $memberships[$x]->setMembershipId($member->getIncremented());
            }
        }
        if ($x)
            $memberships->save();
        
        echo "\n Analyizing which need Episodes...";
        $subreddits = Doctrine::getTable('Subreddit')
                ->getSubredditsNeedingEpisodeGeneration();
        foreach ($subreddits as $subreddit) {
            if (!$quiet)
                echo $subreddit->getName() . ' ';
            $episodes = $subreddit->collectGeneratedEpisodes();
            if (!$quiet)
                echo ' Generated...';
            $episodes->save();
            if (!$quiet)
                echo " Saved!\n";
        }*/

        echo "\n Randomly having users attempt to sign up for Episodes...";
        
        $assignments = new Doctrine_Collection('EpisodeAssignment');
        $x = 0;
        for ($i = 0; $i < $num_users; $i++) {
            $user = sfGuardUserTable::getInstance()->findOneBy('username', $i);
            echo $user->getUsername();
            for($j = 0; $j < $create_episodes_attempt; $j++)
            {
                $episode = EpisodeTable::getInstance()->getRandomUnassignedFutureEpisode();
                if (!$episode)
                    continue;
                $assignments[$x] = new EpisodeAssignment();
                $assignments[$x]->setEpisodeId($episode->getIncremented());
                $assignments[$x]->setSfGuardUserId($user->getIncremented());
                $assignments[$x]->setAuthorType($authortypes[$j]);
                try {
                    $assignments[$x]->save();
                } catch (Exception $e) {
                    unset($e);
                }
                
            }
        }
    }

}
