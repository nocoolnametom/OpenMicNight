<?php slot('atom_feed') ?>
<link href="<?php echo url_for('@feed_index_atom') ?>" type="application/atom+xml" rel="alternate" title="Main Feed" />
<?php end_slot() ?>
<div id="app_subtitle" style="width: 100%; min-height: 1.5em; vertical-align: middle; max-width: 960px; background-color: orangered; color: white; margin-top: 1em;">
    <h2 style="font-weight: bolder; padding: 5px; margin: 0;">
        Open Mike Night-style Subreddit Podcast Scheduling
    </h2>
</div>
<div id="slideshow_container" style="padding: 1em 1.25em 0.5em 0em; float: left;">
    <?php
    include_partial('home/slideshow',
                    array(
        'episodes' => $episodes,
        'subreddits' => $subreddits,
    ));

    ?>
</div>
<div id="description" style="text-size: larger; padding-top: 0.25em; text-align: justify;">
    <p><?php echo ProjectConfiguration::getApplicationName() ?> is a podcast
        scheduler organized around Reddit communities.  Each subreddit sets
        their own schedule and rules, resulting in a multiplicity of themed
        podcasts.</p>
    <p>Enjoy your favorite Reddit communities anywhere you also enjoy your
        favorite Podcasts! You can find podcast feeds by subreddit or even 
        personalize your own!</p>
    <p>Some various things you will probably be able to find on <?php echo ProjectConfiguration::getApplicationName() ?>:</p>
    <ul style="list-style-position: inside; padding-left: 2em;">
        <li>Comedy routines</li>
        <li>Scholarly panels, debates, and presentations</li>
        <li>Independent music</li>
        <li>And tons of people expressing themselves.</li>
    </ul>
    <p>Please <?php echo link_to('register', '@sf_guard_register') ?> and
        <?php echo link_to('sign in', '@sf_guard_signin') ?> to participate!</p>
</div>
<div class="clear_columns" style="clear: both;">&nbsp;</div>
<div style="background-color: palegoldenrod; padding: 0.1em 1em 1em 1em; -moz-border-radius: 5px; border-radius: 5px;">
    <h3 style="padding-top: 0; marging-top: 0;">We are in Alpha</h3>
    <div>This is the alpha version of
    <?php echo ProjectConfiguration::getApplicationName() ?>.  I can't vouch for
    performance issues, and it's currently hosted on my own Linux node server.
    I'd like to move everything to Amazon, but first I'd just like to get things
    moving.  Please register your user and begin sharing episodes.  However, be
    aware that we probably won't be moving old episodes from this alpha version
    to the upcoming beta.</div>
</div>