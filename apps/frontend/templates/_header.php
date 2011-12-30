<div id="header" style="border-bottom: 1px solid black; height: 3.5em;">
    <span id="logo" style="padding: 0 0.5em 0 0.5em; font-size: larger;">
        <a href="<?php echo url_for('@homepage'); ?>" class="app_name" style=" font-size: 3em; text-decoration: none; color: orangered; font-family: 'Arizonia', cursive;"><?php echo ProjectConfiguration::getApplicationName(); ?></a>
        <?php if (ProjectConfiguration::getApplicationSubname()): ?>
            <div style="display: inline; font-size:xx-small; vertical-align: text-top; padding-left: 0.5em; text-transform: uppercase; font-weight: bolder; color: darkgray;"><?php echo ProjectConfiguration::getApplicationSubname(); ?></div>
        <?php endif; ?>
    </span>
    <div id="non_logo-header" style="float:right; padding-top: 1em;">
        <div id="header_user">&nbsp;
            <?php if ($sf_user->isAuthenticated()): ?>
            <div class="header_username" style="float: left;"><?php echo $sf_user->getGuardUser(); ?></div>
            <div class="header_logout" style="float: right;"><?php
                        echo link_to('Logout', '@sf_guard_signout');
                        ?></div>
            <?php endif; ?>
        </div>
        <div class="top_links">
            <ul style="display: inline; margin: 0; padding: 0;">
                <li style="display: inline;">
                    <?php
                    echo link_to('Subreddits', 'subreddit/index');
                    ?>
                </li>
                <li style="display: inline;">
                    <?php
                    echo link_to('Episodes', 'episode/index');
                    ?>
                </li>
                <?php if (!$sf_user->isAuthenticated()): ?>
                    <li class="notice" style="display: inline;">
                        <?php
                        echo link_to('Login', '@sf_guard_signin');
                        ?>
                    </li>
                <?php else: ?>
                    <li style="display: inline;">
                        <?php
                        echo link_to('My Profile', 'profile');
                        ?>
                    </li>
                    <li style="display: inline;">
                        <?php
                        echo link_to('My Messages', 'message');
                        ?>
                    </li>
                    <li style="display: inline;">
                        <?php
                        echo link_to('My Episodes', 'profile/episodes');
                        ?>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>