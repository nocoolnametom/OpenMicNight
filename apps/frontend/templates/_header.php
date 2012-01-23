<div id="header">
    <span id="logo">
        <a href="<?php echo url_for('@homepage'); ?>" class="app_name"><?php echo ProjectConfiguration::getApplicationName(); ?></a>
        <?php if (ProjectConfiguration::getApplicationSubname()): ?>
            <div class="sub_app_name"><?php echo ProjectConfiguration::getApplicationSubname(); ?></div>
        <?php endif; ?>
    </span>
    <div id="non_logo_header">
        <div id="header_user">&nbsp;
            <?php if ($sf_user->isAuthenticated()): ?>
                <div class="header_username"><?php echo $sf_user->getGuardUser(); ?></div>
                <div class="header_logout"><?php
            echo link_to('Logout', '@sf_guard_signout');
                ?></div>
            <?php endif; ?>
        </div>
        <div id="top_links">
            <ul>
                <li>
                    <?php
                    echo link_to('Subreddits', 'subreddit/index');
                    ?>
                </li>
                <li>
                    <?php
                    echo link_to('Episodes', 'episode/index');
                    ?>
                </li>
                <?php if (!$sf_user->isAuthenticated()): ?>
                    <li class="notice">
                        <?php
                        echo link_to('Login', '@sf_guard_signin');
                        ?>
                    </li>
                <?php else: ?>
                    <li>
                        <?php
                        echo link_to('My Profile', 'profile');
                        ?>
                    </li>
                    <li>
                        <?php
                        echo link_to('My Messages', 'message');
                        ?>
                    </li>
                    <li>
                        <?php
                        echo link_to('My Episodes', 'profile/episodes');
                        ?>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>