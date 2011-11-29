<h1>Messages List</h1>

<table>
  <thead>
    <tr>
      <th>Id</th>
      <th>Recipient</th>
      <th>Sender</th>
      <th>Previous message</th>
      <th>Text</th>
      <th>Created at</th>
      <th>Updated at</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($messages as $message): ?>
    <tr>
      <td><a href="<?php echo url_for('message/edit?id='.$message->getId()) ?>"><?php echo $message->getId() ?></a></td>
      <td><?php echo $message->getRecipientId() ?></td>
      <td><?php echo $message->getSenderId() ?></td>
      <td><?php echo $message->getPreviousMessageId() ?></td>
      <td><?php echo $message->getText() ?></td>
      <td><?php echo $message->getCreatedAt() ?></td>
      <td><?php echo $message->getUpdatedAt() ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

  <a href="<?php echo url_for('message/new') ?>">New</a>
