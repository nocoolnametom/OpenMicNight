# OpenMicNight

The basic idea would be that once a month or so we’d open up for scheduling
episodes for the next month and it would be first-come-first-served in terms
of which spot you wanted.  An “understudy” spot could also be chosen in case
the first claimant bails, and all entries would have to be turned in at a
predetermined time beforehand.  If an entry is not turned in before the
deadline, then the understudy gets a much smaller deadline (hopefully you’re
prepared like any good understudy would be!), and if that doesn’t work then
there’s simply no episode for that time.

Perhaps we might feature a Last Minute Raffle the day before on the days when
the understudy also cannot fulfill.  If the rules for sign-up are set up
beforehand, much of the process can be easily automated.  This web application
would also be the publicly-facing side of the podcast, and voting and comments
can be placed on the site.  Obviously, we’d try and get an automatic posting
back to the subreddit when a new episode is released.

It’s open mic night!


## Requirements

 * [symfony 1.4 framework](http://svn.symfony-project.com/branches/1.4/).  You
   can place it wherever you want, but standard system-wide installation
   options are in the `/usr/share` or `/opt` directories.  Make sure that
   wherever you install it that it is readable and executable by PHP and your
   webserver.  For local, application-specific installation you should probably
   place it within the `lib/vendor` directory, alongside other third-party
   libraries the application uses.  This can be pulled into the application
   from the command `git submodule update --init lib/vendor/symfony;`.

 * [Zend Framework](http://framework.zend.com/download/latest).  The
   application assumes the existence of ZF classes within the PHP include_path,
   which is part of a standard ZF installation.  Make sure you reference it in
   `config/ProjectConfiguration.class.php` all the same.

 * [AWS SDK for PHP](https://github.com/amazonwebservices/aws-sdk-for-php).
   This can be pulled into the application from the command `git submodule
   update --init lib/vendor/AWS-SDK;`.

 * [PHP Cron Expression Parser](https://github.com/mtdowling/cron-expression).
   This can be pulled into the application from the command `git submodule
   update --init lib/vendor/CronExpression;`.

 * [JQuery Fancybox Plugin](https://github.com/fancyapps/fancyBox).  This can
   be pulled into the application from the command `git submodule update
   --init lib/vendor/fancybox;`.

 * [PHP Markdown](https://github.com/wolfie/php-markdown).  This can be pulled
   into the application from the command `git submodule update --init
   lib/vendor/php-markdown;`.

 * [Plupload Uploader](https://github.com/moxiecode/plupload).  This can be
   pulled into the application from the command `git submodule update --init
   lib/vendor/plupload;`.

 * Tropo PHP Library for phone recording integration. This can be pulled into
   the application from the command `git submodule update --init
   lib/vendor/plupload;`.

 * PHPUnit is required to run the unit tests.

 * MySQL (or one of its derivatives, like MariaDB) due to some MySQL-specific
   raw queries at use in the application.

 * cUrl is used within the web application to interact with the API and other
   aspects of the app.

 * PHP should have the openssl extension compiled, installed, and enabled in
   order to best interact with Amazon Web Services and with itself.

## Installation

Make sure that you grab whatever submodules you require for the application
when you clone the codebase.  You can grab all submodules by issuing the
following commands:

    git submodule init;
    git submodule update;

You need to at least initialize the symfony plugins that are included as
submodules:

 * sfDoctrineRestGeneratorPlugin

 * sfThemeGeneratorPlugin

 * sfHadoriThemePlugin


### ProjectConfiguration.class.php

Copy `config/ProjectConfiguration.class.php.sample` to
`config/ProjectConfiguration.class.php`.  Then edit the file and change the
`require_once` reference at the beginning of the file to point to the
`sfCoreAutolaod.class.php` file within your local symfony installation.  If you
installed symfony locally in `lib/vendor` you can change this line to read as:
`require_once '../lib/vendor/symfony/lib/autoload/sfCoreAutoload.class.php';`

Save `ProjectConfiguration.class.php` and verify that your changes work by
running `./symfony` from your project root.  You should be presented with a
list of command-line options for the application.  If this list doesn't appear,
verify that `ProjectConfiguration.class.php` is requiring the symfony
autoloader and that the syfmony framework is visible to your CLI PHP.


### databases.yml

Copy `config/databases.yml.sample` to `config/databases.yml`.  Edit the file
and change the database credentials to those you've set up in your database
solution for the application.  Save the file and verify that it's working by
using the symfony CLI to create the database tables and load the basic data
fixtures by running:

    ./symfony doctrine:build --all --and-load;


### app.yml

Copy `apps/frontend/config/app.yml.sample` to `apps/frontend/config/app.yml`.
See the Configuration section below for more info on further instructions
regarding this file.


### Amazon Web Services

The application calls AWS without explicitly making authentication calls; it
assumes that authentication will be handled by the use external to the 
application.  The easiest way to ensure that AWS behaves as expected is to copy
`lib/vendor/AWS-SDK/config-sample.inc.php` to
`lib/vendor/AWS-SDK/config.inc.php` and edit it.  Place your authentication
keys within the config file and save the file.  Amazon Web Services should be
good to go now.


## Testing

The application should have full code coverage in its unique model classes
(though this does not mean that every use case is actually covered).  Simply
run `phpunit` from the application root and PHPUnit should use the
`phpunit.xml.dist` file to execute those tests.


## Configuration

There are a few options that affect all aspects of the application, and they
are declared within `config/ProjectConfiguration.class.php`.  You can change
the Amazon bucket prefix, the storage location for pre-approved audio files,
and the storage location for episode graphic files.  Other options more
specific to different application services are contained within their
respective app config directories (eg, `apps/api_v1/config/app.yml` and
`apps/api_v1/config/settings.yml`).  For more information on these
configuration files see the
[symfony documentation](http://www.symfony-project.org/gentle-introduction/1_4/en/05-Configuring-Symfony).

Since episode files can be uploaded through the API, make sure that the server
handling the API instance of the application is set up to allow the maximum file
size from a POST.  The web applications uses the Pluploader to upload large
files in small chunks, thus you don't need to set the allowable POST for the web
application to be very high.  You can place the following within the declaration
of a VirtualHost in an Apache configuration file to allow php file uploads and
POSTs of the default size within the codebase of 250 MB.  Change it to whatever
you feel is needed.

    php_value post_max_size 250M
    php_value upload_max_filesize 250M

Remember to make the data/temp directory writable by the web user so that users
can upload episode files.  Episodes are served via
Apache [X-Sendfile](https://tn123.org/mod_xsendfile/),
nginx [X-Accel-Redirect](http://wiki.nginx.org/XSendfile), or lighttpd
[X-LIGHTTPD-send-file](http://redmine.lighttpd.net/wiki/1/X-LIGHTTPD-send-file)
so the files are not executed when delivered.  Also remember to deactviate the
execution of PHP scripts within these directories.  You can place the following
within the declaration of a VirtualHost in an Apache configuration file:

    <Directory "/[path to application root]/web/uploads">
        php_admin_flag engine off
     </Directory>
     <Directory "/[path to application root]/data/temp">
        php_admin_flag engine off
     </Directory>


### Configure nginx for X-Accel-Redirect

Audio files are put into the /data/temp directory, so this directory must be
explicitly linked in the nginx configuration to an external URI.  Place the
following in your nginx configuration:

    location /audio/temp/ {
      internal;
      root /[path_to_project_root]/data/temp/; # note the trailing slash
    }


### Configure Apache for X-Sendfile

In your Apache config ensure that XSendfile is loaded, enabled, and can serve
files that are found within the `data/temp/` folder:

    # Load xsendfile
    LoadModule xsendfile_module /path/to/modules/mod_xsendfile.so
    
    # Enable xsendfile
    XSendFile On
    # Set the data/tmp/ directory to be whitelisted
    XSendFilePath /[path_to_project_root]/data/temp/

Ensure that the `data/temp/' directory is readable by the web server (it should
be because the server's going to be trying to put stuff into it!), and the rest
is handled by the app.


### Configure lighttpd for X-LIGHTTPD-send-file

If your FastCGI setup file, make sure that the `allow-x-send-file' option is
enabled:

    fastcgi.server = {
       ".php" => {
          "127.0.0.1" => {
              # ....
              "allow-x-send-file" => "enable" 
          }
       }
    }

Ensure that the `data/temp/' directory is readable by the web server (it should
be because the server's going to be trying to put stuff into it!), and the rest
is handled by the app.


### Configure the really slow file delivery using PHP

If you don't have the ability to tell the server to deliver files for you, you
can still deliver them using PHP, but this is NOT recommended as it is much
more resource intensive.  To activate this option, you'll need to explicitly
change the `enable_slow_audio_download` key in `apps/frontend/config/app.yml`
from false to true.  And may God have mercy on your soul (and server).


### Replace Hashes in Configuration files

Replace the existing hashes in the following files for security reasons.

 * `data/fixtures/fixtures.yml` - Replace the `api_key` and `shared_secret`
   hashes.  Make a note of what you change them to: you'll need them for the
   following file.  Feel free to change the `api_app_name`, `developer_name`,
   and `developer_email` to whatever you'd like.
 
 * `apps/frontend/config/app.yml` - Replace the `web_app_api_key` and
   `web_app_api_shared_secret` with whatever you changed the corresponding
   values in the `data/fixtures/fixtures.yml` file.  Also replace the
   `web_app_image_hash_salt` and `web_app_audio_hash_salt` with whatever you'd
   like.  Near the bottom of the file, enter in your ReCAPTCHA keys to enable
   the correct functioning of ReCAPTCHA.


### Set the location of the API part of the app

It's expected that you'll serve the API using the API frontend controller.  Set
the location you'll use to provide access to the API by changing the `location`
key underneath `web_app_api` at the bottom of `apps/frontend/config/app.yml`.
You can change the similar value that is more in the middle of the file, but
this governs which API the web application will use if you are accessing the
web app in any other environment than production.


### Set Default Values


#### Set the Default Feed Subreddits

To set which Subreddits will automatically appear in the main site feed (and on
the slideshow rotator on the home page), list the subreddits by their domains
in `apps/frontend/config/app.yml` in the key called `subreddits` underneath
`web_app_feed_default`.


#### Set the Reddit Post for Validation

Users who register for the app are sent an email that contains a URL in the app
that will redirect them to a post on Reddit.  They're expected to reply to this
post with their Reddit validation key, which a task in the application will
routinely scan.  To set the web address for this post, replace the existing
address in `apps/frontend/config/app.yml` defined by the key `post_location`
underneath `reddit_validation`.


#### Set the Subreddit for Validation

Technically, users do not need to actually reply to the specific post defined
above, *but* they do need to reply within a specific Subreddit.  Rather than
have that subreddit defined by the `app.yml` value, the subreddit itself is
defined within the `getDefaultSubredditAddress()` function of
`config/ProjectConfiguration.class.php`.


#### Set the Frontend Location

To give the ability for cron jobs and api calls to reference the web frontend
correctly, define the web location of the homepage of the application in the
`getFrontendAppLocation()` function of `config/ProjectConfiguration.class.php`.


#### Set the Application Name

You can change the name of the app by changing it in the `getApplicationName()`
function of `config/ProjectConfiguration.class.php`.


#### Set the Application Email Address

Set what address the application should use to send feedback emails to by
changing the `getApplicationEmailAddress() function of
`config/ProjectConfiguration.class.php`.


#### Set the Amazon Bucket Prefix

You can change the Amazon bucket prefix by changing the
`getAmazonBucketPrefix()` function of `config/ProjectConfiguration.class.php`.


## Cron Jobs

**This needs to be filled in**

There are four cron jobs that the system expects for its functioning.  These
handle such things as creating new episodes according to Subreddit schedules,
validating users, sending nag emails, and moving user assignments on Episodes
when deadlines pass.  Executing a call against the symfony executable in the
project root should display all of the command-line tasks that can be run using
the executable.  These four tasks are defined by the namespace of the app
(which may be different if you changed the name of the app.  For help, just run
the following command:

    php symfony help [app_name]:[task_name]

This should give you more information about the task in question.
