<?php

include_partial('signup_grid', array(
    'episodes' => $episodes,
    'subreddit' => $subreddit,
    'deadlines' => $deadlines,
    'assigned_author_types' => $assigned_author_types,
    'assigned_episodes' => $assigned_episodes,
))
?>
