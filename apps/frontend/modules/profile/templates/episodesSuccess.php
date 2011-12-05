<h1>Released Episodes</h1>
<?php foreach($released as $assignment): ?>
<?php echo $assignment->getEpisode()->getReleaseDate(); ?>
<?php endforeach; ?>

<h1>Upcoming Episodes</h1>
<?php foreach($future as $assignment): ?>
<?php echo $assignment->getEpisode()->getReleaseDate(); ?>
<?php endforeach; ?>