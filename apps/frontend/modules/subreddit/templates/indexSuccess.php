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
      <td><a href="<?php echo url_for('subreddit/edit?id='.$subreddit->getId()) ?>"><?php echo $subreddit->getId() ?></a></td>
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

  <a href="<?php echo url_for('subreddit/new') ?>">New</a>
