<?php $links = array(
    'API Keys' => '@api',
    'Author Types' => '@authortype',
    'Emails' => '@email',
    'Episode Assignments' => '@episodeassignment',
    'Episodes' => '@episode',
    'Subreddit Deadlines' => '@subredditdeadline',
    'Subreddit Memberships' => '@subredditmembership',
    'Subreddits' => '@subreddit',
    'Tropo Numbers' => '@subreddittropo',
    'User Messages' => '@message',
    'Users' => '@user',
    'Validation Posts' => '@validationpost',
) ?>

<?php if ($sf_user->isAuthenticated() && $sf_user->isSuperAdmin()): ?>
<div id="admin-nav">
<ul>
    <?php foreach($links as $label => $route): ?>
        <li><?php echo link_to($label, $route) ?></li>
    <?php endforeach; ?>
</ul>
</div>
<?php endif; ?>