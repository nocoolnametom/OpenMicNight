<?php

class herdditEmailremindersTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
        ));

        $this->namespace = str_replace(' ', '-', strtolower(ProjectConfiguration::getApplicationName()));
        $namespace = $this->namespace;
        $this->name = 'email-reminders';
        $this->briefDescription = 'Emails reminders for users to validate';
        $this->detailedDescription = <<<EOF
The [$namespace:email-reminders|INFO] task emails reminders for users to validate their Reddit usernames.

It's recommended to run this task once a day.  Seriously, more than that will result in multiple emails.  Don't do that.

Call it with:

  [php symfony $namespace:email-reminders|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
        $applicationConfig = sfProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
        $context = sfContext::createInstance($applicationConfig);

        $one_day_users = sfGuardUserTable::getInstance()->getOneDayEmailReminders();
        $one_week_users = sfGuardUserTable::getInstance()->getOneWeekEmailReminders();

        $quiet = (bool) $options['quiet'];

        if (!$quiet)
            echo "Sending one-day reminder emails to  " . count($one_day_users) . " users...";

        foreach ($one_day_users as $user) {
            $sf_user = $context->getUser();
            $sf_user->setApiUserId($user->getIncremented());
            $sf_user->sendMail('RegisterOneDay');
        }

        if (!$quiet)
            echo "\nSending one-week reminder emails to " . count($one_week_users) . " users...";

        foreach ($one_week_users as $user) {
            $sf_user = $context->getUser();
            $sf_user->setApiUserId($user->getIncremented());
            $sf_user->sendMail('RegisterOneWeek');
        }

        if (!$quiet)
            echo "\n";
    }

}
