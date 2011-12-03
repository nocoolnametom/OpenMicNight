<h1><?php echo $subreddit->getName() ?></h1>
<?php echo link_to('Back to Subreddit', 'subreddit/show?id=' . $subreddit->getIncremented()) ?>

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
