<?php slot('atom_feed') ?>
<link href="<?php echo url_for('@feed_index_atom') ?>" type="application/atom+xml" rel="alternate" title="Main Feed" />
<?php end_slot() ?>
<h2>Subreddits</h2>

<ul style="list-style: none; margin: 0; padding: 0;">
    <?php foreach ($subreddits as $subreddit): ?>
        <li style="padding-bottom: 0.25em;">
            <?php echo link_to(image_tag('rss.svg',array('style' => 'height: 1em; width: auto;')), '@feed_subreddit_atom?domain=' . $subreddit->getDomain()) ?>
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