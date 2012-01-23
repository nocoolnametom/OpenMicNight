<?php slot('atom_feed') ?>
<link href="<?php echo url_for('@feed_subreddit_atom?domain=' . $subreddit->getDomain()) ?>" type="application/atom+xml" rel="alternate" title="<?php echo $subreddit ?> Atom" />
<link href="<?php echo url_for('@feed_subreddit_rss?domain=' . $subreddit->getDomain()) ?>" type="application/rss+xml" rel="alternate" title="<?php echo $subreddit ?> RSS" />
<?php end_slot() ?>
<div id="feed_link"><?php echo link_to(image_tag('rss.svg'), '@feed_subreddit_rss?domain=' . $subreddit->getDomain()) ?></div>
<h2 class="orangeredbar"><?php echo $subreddit ?></h2>
<?php if ($sf_user->isAuthenticated()): ?>
    <?php if ($membership && in_array($membership->getMembership()->getType(), array('admin'))): ?>
        <div class="subreddit_edit_link"><?php echo link_to('Edit', 'subreddit/edit?domain=' . $subreddit->getDomain()); ?></div>
        <div class="subreddit_users_link"><?php echo link_to('User Memberships', 'subreddit/users?domain=' . $subreddit->getDomain()); ?></div>
        <div class="subreddit_phone_link"><?php echo link_to('Tropo Telephone Integration', 'subreddit/phone?domain=' . $subreddit->getDomain()); ?></div>
    <?php endif; ?>
    <div class="membership">
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
        <div class="subreddit_episode_signup_link">
            <?php echo link_to('Deadlines', 'subreddit/deadlines?domain=' . $subreddit->getDomain()); ?>
        </div>

<?php if (count($episodes)): ?>
<h3>Released Episodes</h3>
<ul>
    <?php foreach ($episodes as $episode): ?>
        <li><a href="<?php echo url_for('episode/show?id='.$episode->getId()) ?>"><?php echo $episode->getTitle() ?></a><?php echo ($episode->getIsNsfw() ? '<span if="nsfw">NSFW</span> ' : ' ') ?>(<?php echo date('Y-m-d', strtotime($episode->getReleaseDate())) ?>)</li>
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
<?php endif; ?>