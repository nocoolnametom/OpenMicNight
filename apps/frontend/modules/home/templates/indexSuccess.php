<?php slot('atom_feed') ?>
<link href="<?php echo url_for('@feed_index_atom') ?>" type="application/atom+xml" rel="alternate" title="Main Feed Atom" />
<link href="<?php echo url_for('@feed_index_rss') ?>" type="application/rss+xml" rel="alternate" title="Main Feed RSS" />
<?php end_slot() ?>
<div id="app_subtitle">
    <h2>Open Mike Night-style Subreddit Podcast Scheduling</h2>
</div>
<div id="slideshow_container">
    <?php
    include_partial('home/slideshow',
                    array(
        'episodes' => $episodes,
        'subreddits' => $subreddits,
    ));

    ?>
</div>
<div id="description">
    <p><?php echo ProjectConfiguration::getApplicationName() ?> is a podcast
        scheduler organized around Reddit communities.  Each subreddit sets
        their own schedule and rules, resulting in a multiplicity of themed
        podcasts.</p>
    <p>Enjoy your favorite Reddit communities anywhere you also enjoy your
        favorite Podcasts! You can find podcast feeds by subreddit or even 
        personalize your own!</p>
    <p>Some various things you will probably be able to find on <?php echo ProjectConfiguration::getApplicationName() ?>:</p>
    <ul>
        <li>Comedy routines</li>
        <li>Scholarly panels, debates, and presentations</li>
        <li>Independent music</li>
        <li>And tons of people expressing themselves.</li>
    </ul>
    <p>Please <?php echo link_to('register', '@sf_guard_register') ?> and
        <?php echo link_to('sign in', '@sf_guard_signin') ?> to participate!</p>
</div>
<div class="clear_columns">&nbsp;</div>
<div id="home_page_alert">
    <h3>We are in Beta</h3>
    <p>This is the beta version of
    <?php echo ProjectConfiguration::getApplicationName() ?>.  I can't vouch for
    performance issues, and it's currently hosted on my own Linux node server.
    I'd like to move everything to Amazon, but first I'd just like to get things
    moving.  Please register your user and begin sharing episodes.  However, be
    aware that we probably won't be moving old episodes from this beta version
    to the upcoming release, so hold onto your episodes in case you need to
    re-release them again.</p>
</div>