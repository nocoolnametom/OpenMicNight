<h2 class="orangeredbar">My Messages</h2>

<?php if (count($received_messages)): ?>
<h2>Received</h2>
<table>
  <thead>
    <tr>
      <th>Sender</th>
      <th>Text</th>
      <th colspan="2">Sent</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($received_messages as $message): ?>
    <tr>
      <td><?php echo $users[$message->getSenderId()]->getUsername() ?></td>
      <td><?php echo $message->getText() ?></td>
      <td><?php echo $message->getCreatedAt() ?></td>
      <td><?php echo link_to('Reply', '@message_send_previous?id=' . $message->getSenderId() . '&previous=' . $message->getIncremented()) ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>

<?php if (count($sent_messages)): ?>
<h2>Sent</h2>
<table>
  <thead>
    <tr>
      <th>Recipient</th>
      <th>Text</th>
      <th>Sent</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($sent_messages as $message): ?>
    <tr>
      <td><?php echo $users[$message->getRecipientId()]->getUsername() ?></td>
      <td><?php echo $message->getText() ?></td>
      <td><?php echo $message->getCreatedAt() ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>