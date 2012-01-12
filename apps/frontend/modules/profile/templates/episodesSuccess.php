<?php $key = $user->getRedditValidationKey(); ?>
<?php slot('atom_feed') ?>
<link href="<?php echo url_for('@feed_user_atom?reddit_validation_key=' . $key); ?>" type="application/atom+xml" rel="alternate" title="<?php echo $user->getUsername(); ?>" />
<?php end_slot() ?>
<?php if (count($approvals)): ?>
    <h2>Episodes Awaiting Approval</h2>
    <?php foreach ($approvals as $episode): ?>
        <div class="subreddit_name">/r/<?php echo $subreddits[$episode->getSubredditId()]->getDomain(); ?></div>
        <div class=""><?php
        echo link_to(date("g:ia, D j M Y", strtotime($episode->getReleaseDate())), 'episode/approval?id=' . $episode->getIncremented());
        ?></div>
        <?php endforeach; ?>
    <?php endif; ?>

<?php if (count($current)): ?>
    <h2>Current Episodes</h2>
    <?php foreach ($current as $assignment): ?>
        <div class="episode">
            <span class="subreddit_name">/r/<?php echo $subreddits[$assignment->getEpisode()->getSubredditId()]->getDomain(); ?></span>
            <span class="release_date"><?php
        echo link_to(date("g:ia, D j M Y", strtotime($assignment->getEpisode()->getReleaseDate())), 'episode/edit?id=' . $assignment->getEpisode()->getIncremented());
        ?></span>
            <span class="deadline"><?php
        $release_date = strtotime($assignment->getEpisode()->getReleaseDate());
        $deadline_seconds = $deadlines[$assignment->getEpisode()->getSubredditId()][$assignment->getAuthorTypeId()];
        echo date("g:ia, D j M Y", $release_date - $deadline_seconds);
        ?></span>
        </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (count($future)): ?>
    <h2>Queued Episodes</h2>
    <?php foreach ($future as $assignment): ?>
        <div class="episode">
            <span class="subreddit_name">/r/<?php echo $subreddits[$assignment->getEpisode()->getSubredditId()]->getDomain(); ?></span>
            <span class=""><?php
        echo date("g:ia, D j M Y", strtotime($assignment->getEpisode()->getReleaseDate()));
        ?></span>
            <span class="deadline"><?php
        $release_date = strtotime($assignment->getEpisode()->getReleaseDate());
        die(var_dump($deadlines->toArray()));
        $deadline_seconds = $deadlines[$assignment->getEpisode()->getSubredditId()][$assignment->getAuthorTypeId()];
        echo date("g:ia, D j M Y", $release_date - $deadline_seconds);
        ?></span>
        </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (count($released)): ?>
    <h2>Released Episodes</h2>
    <?php foreach ($released as $assignment): ?>
        <div class="episode">
            <span class="subreddit_name">/r/<?php echo $subreddits[$assignment->getEpisode()->getSubredditId()]->getDomain(); ?></span>
            <span class=""><?php
        echo link_to(date("g:ia, D j M Y", strtotime($assignment->getEpisode()->getReleaseDate())), 'episode/show?id=' . $assignment->getEpisode()->getIncremented());
        ?></span>
        </div>
            <?php endforeach; ?>
            <?php if (!(($page == 1 || $page == 0) && count($released) == 0)): ?>
        <div class="navigation"> view more: 
                <?php if ($page > 1): ?>
                <a href="<?php echo url_for('profile/episodes?page=' . ($page - 1)) ?>">prev</a>
                <?php endif; ?>
                <?php
                echo (($page > 1 && count($released) > 0) ? ' | ' : '');
                ?>
        <?php if (count($released) > 0): ?>
                <a href="<?php echo url_for('subreddit/episodes?page=' . ($page + 1)) ?>">next</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>