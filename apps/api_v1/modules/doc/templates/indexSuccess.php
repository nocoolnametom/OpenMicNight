<h1><?php echo ProjectConfiguration::getApplicationName() ?> API v1</h1>

<h2>Categories</h2>
<ul>
    <li><?php echo link_to('authortype', 'doc/authortype'); ?></li>
    <li><?php echo link_to('episode', 'doc/episode') ?></li>
    <li><?php echo link_to('episodeassignment', 'doc/episodeassignment') ?></li>
    <li><?php echo link_to('membershiptype', 'doc/membershiptype') ?></li>
    <li><?php echo link_to('message', 'doc/message') ?></li>
    <li><?php echo link_to('subreddit', 'doc/subreddit') ?></li>
    <li><?php echo link_to('subredditdeadline', 'doc/subredditdeadline') ?></li>
    <li><?php echo link_to('subredditmembership', 'doc/subredditmembership') ?></li>
    <li><?php echo link_to('user', 'doc/user') ?></li>
    <li><?php echo link_to('user_id', 'doc/user_id') ?></li>
    <li><?php echo link_to('time', 'doc/time') ?></li>
</ul>

<h2>Authenticating</h2>
<p>Roughly, the process for almost all API calls is as follows:</p>

<ol>
    <li>Obtain the <?php echo link_to('server time', 'doc/time') ?>.  The 
        server will only accept API calls with a given timestamp within a 
        ten-minute window, so ensure that the timestamp you are giving the API
        server is as near as possible to the server's local time.</li>
    <li><p>Assemble your API signature. All calls expect the following GET
            variables (even POSTs, PUTs, and DELETEs):</p>
        <blockquote><strong>api_key=123456789abcdef0123456789abcdef0&time=123456789&signature=f512f39d1c05dbf2a33bf76acac8a0fb</strong></blockquote>
        <p>These elements are described as follows:</p>
        <blockquote>
            <dl>
                <dt><strong>api_key</strong></dt>
                <dd>Your API public key that you received from
                    <?php echo ProjectConfiguration::getApplicationName() ?>.</dd>
                <dt><strong>time</strong></dt>
                <dd>The Unix timestamp of when you made the request. The server
                    expects this to be nearly the same as its local time, which
                    you got in step 1.</dd>
                <dt><strong>signature</strong></dt>
                <dd>This is the MD5 hash of your concatenated API private key
                    and the timestamp.</dd>
            </dl>
        </blockquote>
        <p>Together, these elements prove that someone who has the private API
            key is making the call and the call is only valid for about ten
            minutes, so even if someone were to get the authentication
            signature, it wouldn't be valid unless they obtained it within a
            very short time frame.  If problems arise, this time frame may be
            shortened, which means that using the "time" call to keep in sync is
            very important.
    </li>
    <li><p>Some calls can only be made by authenticated users.  To authenticate the
            call, you'll need to send a <?php echo link_to('user_token', 'doc/user') ?>
            with the call, again as a GET variable (put it with the API
            authentication stuff).</p>
        <p>Asking for an authentication token will alert the user that this token
            has been requested.  While not ideal, we feel this is currently the
            second-best alternative to creating a fully-compliant OAuth process.
            The user is alerted to every authentication made and can revoke all
            authentication tokens at anytime rendering all calls made with that
            token void from that point on.</p>
    </li>
</ol>

<h2>Questions and Feedback</h2>
<p>We (mostly) use the API within the web app of
    <?php echo ProjectConfiguration::getApplicationName() ?>, so we care about
    making sure that everything is working.  Our goal is to make almost all
    aspects of the web application available to other developers (currently with
    the exception of handling user registration and validation).  Of course
    there are issues that stand in our way currently, and thus we're calling
    this API version 1.0.  We'll do our best to keep developers involved in
    future development, and for those who are frustrated with the API, rest
    assured: there will be a version 2.0.
</p>