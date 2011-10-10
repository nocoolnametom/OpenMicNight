# OpenMicNight

The basic idea would be that once a month or so we’d open up for scheduling episodes for the next month and it would be first-come-first-served in terms of which spot you wanted.  An “understudy” spot could also be chosen in case the first claimant bails, and all entries would have to be turned in at a predetermined time beforehand.  If an entry is not turned in before the deadline, then the understudy gets a much smaller deadline (hopefully you’re prepared like any good understudy would be!), and if that doesn’t work then there’s simply no episode for that time.

Perhaps we might feature a Last Minute Raffle the day before on the days when the understudy also cannot fulfill.  If the rules for sign-up are set up beforehand, much of the process can be easily automated.  This web application would also be the publicly-facing side of the podcast, and voting and comments can be placed on the site.  Obviously, we’d try and get an automatic posting back to the subreddit when a new episode is released.

It’s open mic night!


## Requirements

 * [symfony 1.4 framework](http://svn.symfony-project.com/branches/1.4/).  You can place it wherever you want, but standard ssytem-wide installation options are in the `/usr/share` or `/opt` directories.  Make sure that wherever you install it that it is readable and executable by PHP and your webserver.  For local, applciation-specific installation you should probably place it within the `lib/vendor` directory, alongside other third-party libraries the application uses.

 * [Zend Framework](http://framework.zend.com/download/latest).  The application assumes the existence of ZF classes within the PHP include_path, which is part of a standard ZF installation.

 * [AWS SDK for PHP](https://github.com/amazonwebservices/aws-sdk-for-php).  This can be pulled into the application from the command `git submodule update --init lib/vendor/AWS-SDK;`.

 * [PHP Cron Expression Parser](https://github.com/mtdowling/cron-expression).  This can be pulled into the application from the command `git submodule update --init lib/vendor/CronExpression;`.

 * PHPUnit is required to run the unit tests.


## Installation


### ProjectConfiguration.class.php

Copy `config/ProjectConfiguration.class.php.sample` to `config/ProjectConfiguration.class.php`.  Then edit the file and change the `require_once` reference at the beginning of the file to point to the `sfCoreAutolaod.class.php` file within your local symfony installation.  If you installed symfony locally in `lib/vendor` you can change this line to read as:
`require_once '../lib/vendor/symfony/lib/autoload/sfCoreAutoload.class.php';`

Save `ProjectConfiguration.class.php` and verify that your changes work by running `./symfony` from your project root.  You should be presented with a list of command-line options for the application.  If this list doesn't appear, verify that `ProjectConfiguration.class.php` is requiring the symfony autoloader and that the syfmony framework is visible to your CLI PHP.


### databases.yml

Copy `config/databases.yml.sample` to `config/databases.yml`.  Edit the file and change the database credentials to those you've set up in your database solution for the application.  Save the file and verify that it's working by using the symfony CLI to create the database tables and load the basic data fixtures by running:

`./symfony doctrine:build --all --and-load;`


### Amazon Web Services

The application calls AWS without explicitly making authentication calls; it assumes that authentication will be handled by the use external to the application.  The easiest way to ensure that AWS behaves as expected is to copy `lib/vendor/AWS-SDK/config-sample.inc.php` to `lib/vendor/AWS-SDK/config.inc.php` and edit it.  Place your authentication keys within the config file and save the file.  Amazon Web Services should be good to go now.


## Testing

The application should have full code coverage in its unique model classes (though this does not mean that every use case is actually covered).  Simply run `phpunit` from the applciation root and PHPUnit should use the `phpunit.xml.dist` file to execute those tests.


## Configuation

There are a few options that affect all aspects of the application, and they are declared within `config/ProjectConfiguration.class.php`.  You can change the Amazon bucket prefix, the storage location for pre-approved audio files, and the storage location for episode graphic files.  Other options more sepcific to different application services are contained within their respectives app config directories (eg, `apps/api_v1/config/app.yml` and `apps/api_v1/config/settings.yml`).  For more information on these configuration files see the [symfony documentation](http://www.symfony-project.org/gentle-introduction/1_4/en/05-Configuring-Symfony).