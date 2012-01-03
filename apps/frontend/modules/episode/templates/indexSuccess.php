<h1>Episodes List</h1>

<?php if (count($episodes)): ?>
<table>
  <thead>
    <tr>
      <th>Subreddit</th>
      <th>Episode</th>
      <th>Submitter</th>
      <th>Reddit Post</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($episodes as $episode): ?>
    <tr>
      <td><?php echo (array_key_exists($episode->getSubredditId(), $subreddits) ? $subreddits[$episode->getSubredditId()]->getName() : '')?></td>
      <td><a href="<?php echo url_for('episode/show?id='.$episode->getId()) ?>"><?php echo $episode->getTitle() ?></a><?php echo ($episode->getIsNsfw() ? '<span if="nsfw">NSFW</span>' : '') ?></td>
      <td><?php echo $users[$assignments[$episode->getEpisodeAssignmentId()]->getSfGuardUserId()]->getUsername() ?></td>
      <td><?php echo ($episode->getRedditPostUrl() ? link_to($episode->getRedditPostUrl(), $episode->getRedditPostUrl()) : '') ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php else: ?>
<div>No episodes found...</div>
<?php endif;?>
<?php if (!(($page == 1 || $page == 0) && count($episodes) == 0)): ?>
    <div class="navigation"> view more: 
        <?php if ($page > 1): ?>
            <a href="<?php echo url_for('episode/index?page=' . ($page - 1)) ?>">prev</a>
        <?php endif; ?>
        <?php echo (($page > 1 && count($episodes) > 0) ? ' | ' : ''); ?>
        <?php if (count($episodes) > 0): ?>
            <a href="<?php echo url_for('episode/index?page=' . ($page + 1)) ?>">next</a>
        <?php endif; ?>
    </div>
<?php endif; ?>