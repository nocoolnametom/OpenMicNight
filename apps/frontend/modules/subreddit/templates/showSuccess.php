<h1><?php echo $subreddit ?></h1>
<?php if ($sf_user->isAuthenticated()): ?>
    <?php if (in_array($membership->getMembership()->getType(), array('moderator', 'admin'))): ?>
        <div class="subreddit_edit_link"><?php echo link_to('Edit', 'subreddit/edit?id=' . $subreddit->getIncremented()); ?></a>
    <?php endif; ?>
    <div class=""membership">
    <?php if (is_null($membership)): ?>
        <?php echo link_to('Join Subreddit', 'subreddit/join?domain=' . $subreddit->getDomain()) ?>
    <?php else: ?>
             You have a <?php echo $membership->getMembership()->getDescription() ?> membership in this Subreddit.
         <?php endif; ?>
    </div>
    <div class="subreddit_episode_signup_link">
        <?php echo link_to('Signup for Upcoming Episodes', 'subreddit/signup?domain=' . $subreddit->getDomain()); ?>
    </div>
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