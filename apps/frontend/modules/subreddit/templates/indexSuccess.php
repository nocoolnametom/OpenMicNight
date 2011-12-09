<h1>Subreddits List</h1>

<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Updated at</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($subreddits as $subreddit): ?>
            <tr>
                <td><a href="<?php echo url_for('subreddit/show?domain=' . $subreddit->getDomain()) ?>"><?php echo $subreddit->getName() ?></a></td>
                <td><?php echo $subreddit->getUpdatedAt() ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php if (!(($page == 1 || $page == 0) && count($subreddits) == 0)): ?>
    <div class="navigation"> view more: 
        <?php if ($page > 1): ?>
            <a href="<?php echo url_for('subreddit/index?page=' . ($page - 1)) ?>">prev</a>
        <?php endif; ?>
        <?php echo (($page > 1 && count($subreddits) > 0) ? ' | ' : ''); ?>
        <?php if (count($subreddits) > 0): ?>
            <a href="<?php echo url_for('subreddit/index?page=' . ($page + 1)) ?>">next</a>
        <?php endif; ?>
    </div>
<?php endif; ?>