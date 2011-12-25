<div id="header" style="border-bottom: 1px solid black; height: 3.5em;">
    <span id="logo" style="padding: 0 0.5em 0 0.5em; font-size: larger; font-weight: bold; font-family: 'Arizonia', cursive;">
        <a href="<?php echo url_for('@homepage'); ?>" class="app_name" style=" font-size: 3em; text-decoration: none; color: orangered;"><?php echo ProjectConfiguration::getApplicationName(); ?></a>
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
            <ul style="list-style: none; display: inline; margin: 0; padding: 0;">
                <li style="display: inline;;">
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