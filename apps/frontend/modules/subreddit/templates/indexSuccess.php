<?php slot('atom_feed') ?>
<link href="<?php echo url_for('@feed_index_atom') ?>" type="application/atom+xml" rel="alternate" title="Main Feed Atom" />
<link href="<?php echo url_for('@feed_index_rss') ?>" type="application/rss+xml" rel="alternate" title="Main Feed RSS" />
<?php end_slot() ?>
<h2 class="orangeredbar">Subreddits</h2>

<ul id ="subreddit_list">
    <?php foreach ($subreddits as $subreddit): ?>
        <li>
            <?php echo link_to(image_tag('rss.svg',array('class' => 'little_rss_icon')), '@feed_subreddit_rss?domain=' . $subreddit->getDomain()) ?>
            <?php echo link_to($subreddit->getName(), url_for('subreddit/show?domain=' . $subreddit->getDomain())) ?>
        </li>
        <?php endforeach; ?>
</ul>

<?php if (!(($page == 1 || $page == 0) && count($subreddits) == 0)): ?>
    <div class="navigation"> view more: 
        <?php if ($page > 1): ?>
            <a href="<?php echo url_for('subreddit/index?page=' . ($page - 1)) ?>">prev</a>
    <?php endif; ?>
    <?php echo (($page > 1 && count($subreddits) > 0)
                ? ' | ' : ''); ?>
    <?php if (count($subreddits) > 0): ?>
            <a href="<?php echo url_for('subreddit/index?page=' . ($page + 1)) ?>">next</a>
    <?php endif; ?>
    </div>
<?php endif; ?>