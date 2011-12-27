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
<h3 style="padding-top: 0; marging-top: 0;">This is the Alpha version</h3>
<div>This is the alpha version of
    <?php echo ProjectConfiguration::getApplicationName() ?>.  I can't vouch for
    performance issues, and it's currently hosted on my own Linux node server.
    I'd like to move everything to Amazon, but first I'd just like to get things
    moving.  Please register your user and begin sharing episodes.  However, be
    aware that we probably won't be moving old episodes from this alpha version
    to the upcoming beta.</div>
<div>There is an extensive API behind the site (I've tried to use it for almost
    everything in the web application, so I know that it works even though it's
    a pain to use), but the documentation is currently really crappy and I don't
    have any automated sign-up process.  If you would like an API key to play
    around with, please <a href="mailto:nocoolnametom@gmail.com">send me
        note</a> and I'll see what I can do.</div>
<h3>How can I help?</h3>
<div>The beta will begin in a few months and will be open to all Subreddits to
    participate in. Until that time there's a number of things that need
    help.</div>
<h4>Template Assistance</h4>
<div>I'm a programmer, not a designer.  While I think the current look and feel
    is rather snazzy, I understand that it's <em>very</em> sub-par.  This site
    needs a much more unified and unique look to it that speaks to the ideas of
    fun, individual expression, and Reddit.  It needs fun JQuery and JQueryUI
    bells and whistles.  I can work with integration of visual improvements to
    the site, so if you're interested, have any idea, or want to show off
    anything please <a href="mailto:nocoolnametom@gmail.com">drop me a
        note</a>.</div>
<h4>Copy Editing</h4>
<div>Just as I'm not a designer, I'm also not a copy editor.  This front page
    will be the first thing many people see when they arrive at the site.  They
    need to understand the concept of scheduled and organized podcasts centered
    around subreddit communities and to know why they would enjoy participating.
    If you want suggestions for improvement to the copy of the site, please
    <a href="mailto:nocoolnametom@gmail.com">let me know</a>.</div>
<h4>Legal Assistance</h4>
<div>Since we're dealing with audio files that can be shared with thousands of
    people, the opportunities for copyright infringement need to be dealt with.
    The approval process seems to me to be a great tool to shift responsibility
    onto the various subreddits, but I am uncertain of the specific legal
    ramifications should someone attempt to shared audio content they do not
    have the rights to.  And yet other sites such as Imgur are able to deal with
    similar issues.  If you know of anyone with legal expertise in this area or
    want to offer advice or suggestions, please
    <a href="mailto:nocoolnametom@gmail.com">let me know</a>.</div>
<h4>The Name</h4>
<div>Yeah, I'm aware that the subdomain of the site is currently "openmicnight"
    and that the name is currently displayed as "Herddit".  I'm not very good
    with names; OpenMicNight was the first name I came up with, and I thought
    that Herddit was a fun play on the name "Reddit".  However, I may not have a
    good grasp on such things.  If you have a suggestion for a good name for the
    site, even if it's one of those crazy off-the-wall Web 2.0 type names so
    popular nowadays, please <a href="mailto:nocoolnametom@gmail.com">email the
        name to me</a>, making sure to explain why you think the name is
        good.</div>
<h4>A Job</h4>
<div>Finally, I'd like to think that I'm a pretty good backend web programmer.
    I'm proficient in PHP, good with C#, comfortable with Java and Python, and
    prefer Linux.  I'm always on the lookout for a good job where I can work a
    good 40 hours but still have a life outside of work with my family.  And
    anything that is available outside of Utah would be nice, too.  I know it's
    probably a long shot, but if you have any openings or know of any openings
    for a web programmer please let me know.</div>