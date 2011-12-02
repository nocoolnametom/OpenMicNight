<h1><?php echo $subreddit ?></h1>
<?php if ($sf_user->isAuthenticated()): ?>
    <?php echo link_to('Edit', 'subreddit/edit?id=' . $subreddit->getIncremented()); ?>
<?php endif; ?>

<h2>Released Episodes</h2>
<ul>
    <?php foreach ($episodes as $episode): ?>
        <li><?php echo $episode->getReleaseDate(); ?></li>
    <?php endforeach; ?>
</ul>
<?php if ($sf_user->isAuthenticated()): ?>
    <?php echo link_to('Signup for Upcoming Episodes', 'subreddit/signup?id=' . $subreddit->getIncremented()); ?>
<?php endif; ?>
