<h1>Episodes List</h1>

<table>
  <thead>
    <tr>
      <th>Id</th>
      <th>Sf guard user</th>
      <th>Subreddit</th>
      <th>Approved by</th>
      <th>Release date</th>
      <th>Audio file</th>
      <th>Nice filename</th>
      <th>Graphic file</th>
      <th>Is nsfw</th>
      <th>Title</th>
      <th>Description</th>
      <th>Is submitted</th>
      <th>Submitted at</th>
      <th>Is approved</th>
      <th>Approved at</th>
      <th>File is remote</th>
      <th>Remote url</th>
      <th>Reddit post url</th>
      <th>Created at</th>
      <th>Updated at</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($episodes as $episode): ?>
    <tr>
      <td><a href="<?php echo url_for('episode/edit?id='.$episode->getId()) ?>"><?php echo $episode->getId() ?></a></td>
      <td><?php echo $episode->getSfGuardUserId() ?></td>
      <td><?php echo $episode->getSubredditId() ?></td>
      <td><?php echo $episode->getApprovedBy() ?></td>
      <td><?php echo $episode->getReleaseDate() ?></td>
      <td><?php echo $episode->getAudioFile() ?></td>
      <td><?php echo $episode->getNiceFilename() ?></td>
      <td><?php echo $episode->getGraphicFile() ?></td>
      <td><?php echo $episode->getIsNsfw() ?></td>
      <td><?php echo $episode->getTitle() ?></td>
      <td><?php echo $episode->getDescription() ?></td>
      <td><?php echo $episode->getIsSubmitted() ?></td>
      <td><?php echo $episode->getSubmittedAt() ?></td>
      <td><?php echo $episode->getIsApproved() ?></td>
      <td><?php echo $episode->getApprovedAt() ?></td>
      <td><?php echo $episode->getFileIsRemote() ?></td>
      <td><?php echo $episode->getRemoteUrl() ?></td>
      <td><?php echo $episode->getRedditPostUrl() ?></td>
      <td><?php echo $episode->getCreatedAt() ?></td>
      <td><?php echo $episode->getUpdatedAt() ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

  <a href="<?php echo url_for('episode/new') ?>">New</a>
