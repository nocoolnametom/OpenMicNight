<h2 class="orangeredbar"><?php echo $subreddit->getName() ?></h2>
<?php echo link_to('Back to Subreddit', 'subreddit/show?domain=' . $subreddit->getDomain()) ?>

<?php
include_partial('signup_grid', array(
    'episodes' => $episodes,
    'subreddit' => $subreddit,
    'deadlines' => $deadlines,
    'assigned_author_types' => $assigned_author_types,
    'assigned_episodes' => $assigned_episodes,
    'authortypes' => $authortypes,
    'assignments' => $assignments,
));
?>
