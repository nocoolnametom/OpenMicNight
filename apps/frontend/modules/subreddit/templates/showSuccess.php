<h1><?php echo $subreddit ?></h1>
<?php if ($sf_user->isAuthenticated()): ?>
    <?php echo link_to('Edit', 'subreddit/edit?id=' . $subreddit->getIncremented()); ?>
<?php endif; ?>
<?php if ($sf_user->isAuthenticated()): ?>
    <?php echo link_to('Signup for Upcoming Episodes', 'subreddit/signup?id=' . $subreddit->getIncremented()); ?>
<?php endif; ?>

<h2>Released Episodes</h2>
<ul>
    <?php foreach ($episodes as $episode): ?>
        <li><?php echo $episode->getReleaseDate(); ?></li>
    <?php endforeach; ?>
</ul>
<?php if (!(($page == 1 || $page == 0) && count($episodes) == 0)): ?>
    <div class="navigation"> view more: 
        <?php if ($page > 1): ?>
            <a href="<?php echo url_for('subreddit/show?domain=' . $subreddit->getDomain() . '&page=' . ($page - 1)) ?>">prev</a>
        <?php endif; ?>
        <?php echo (($page > 1 && count($episodes) > 0) ? ' | ' : ''); ?>
        <?php if (count($episodes) > 0): ?>
            <a href="<?php echo url_for('subreddit/show?domain=' . $subreddit->getDomain() . '&page=' . ($page + 1)) ?>">next</a>
        <?php endif; ?>
    </div>
<?php endif; ?>