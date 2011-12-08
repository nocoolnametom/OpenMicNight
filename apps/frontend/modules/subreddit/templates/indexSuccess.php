<h1>Subreddits List</h1>

<table>
    <thead>
        <tr>
            <th>Id</th>
            <th>Name</th>
            <th>Domain</th>
            <th>Is active</th>
            <th>Create new episodes cron formatted</th>
            <th>Episode schedule cron formatted</th>
            <th>Creation interval</th>
            <th>Bucket name</th>
            <th>Created at</th>
            <th>Updated at</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($subreddits as $subreddit): ?>
            <tr>
                <td><a href="<?php echo url_for('subreddit/show?id=' . $subreddit->getId()) ?>"><?php echo $subreddit->getId() ?></a></td>
                <td><?php echo $subreddit->getName() ?></td>
                <td><?php echo $subreddit->getDomain() ?></td>
                <td><?php echo $subreddit->getIsActive() ?></td>
                <td><?php echo $subreddit->getCreateNewEpisodesCronFormatted() ?></td>
                <td><?php echo $subreddit->getEpisodeScheduleCronFormatted() ?></td>
                <td><?php echo $subreddit->getCreationInterval() ?></td>
                <td><?php echo $subreddit->getBucketName() ?></td>
                <td><?php echo $subreddit->getCreatedAt() ?></td>
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