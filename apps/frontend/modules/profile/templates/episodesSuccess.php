<h1>Current Episodes</h1>
<?php foreach($current as $assignment): ?>
<div class="subreddit_name">/r/<?php echo SubredditTable::getInstance()->find($assignment->getEpisode()->getSubredditId())->getDomain(); ?></div>
<div class=""><?php echo link_to($assignment->getEpisode()->getReleaseDate(), 'episode/edit?id=' . $assignment->getEpisode()->getIncremented()); ?></div>
<?php endforeach; ?>

<h1>Upcoming Episodes</h1>
<?php foreach($future as $assignment): ?>
<div class="subreddit_name">/r/<?php echo SubredditTable::getInstance()->find($assignment->getEpisode()->getSubredditId())->getDomain(); ?></div>
<div class=""><?php echo $assignment->getEpisode()->getReleaseDate(); ?></div>
<?php endforeach; ?>

<h1>Released Episodes</h1>
<?php foreach($released as $assignment): ?>
<div class="subreddit_name">/r/<?php echo SubredditTable::getInstance()->find($assignment->getEpisode()->getSubredditId())->getDomain(); ?></div>
<div class=""><?php echo link_to($assignment->getEpisode()->getReleaseDate(), 'episode/show?id=' . $assignment->getEpisode()->getIncremented()); ?></div>
<?php endforeach; ?>