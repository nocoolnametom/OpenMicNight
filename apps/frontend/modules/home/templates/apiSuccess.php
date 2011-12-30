<h2>API Access</h2>

<div>There is an extensive API behind the site (I've tried to use it for almost
    everything in the web application, so I know that it works even though it's
    a pain to use), but the documentation is currently really crappy and I don't
    have any automated sign-up process.  If you would like an API key to play
    around with, please
    <?php include_partial('global/feedback_link', array('feedback_text' => 'send me a note', 'link_style' => '',)) ?>
    and I'll see what I can do.</div>

<div>You can find the documentation for the API at
    <a href="<?php echo sfConfig::get('app_web_app_api_location') ?>"><?php echo sfConfig::get('app_web_app_api_location') ?></a>
</div>