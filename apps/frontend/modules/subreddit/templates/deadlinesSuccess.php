<h2 class="orangeredbar">Deadlines for <?php echo $subreddit->getName() ?></h2>

<?php echo link_to('Back to Subreddit',
                    'subreddit/show?domain=' . $subreddit->getDomain());
?>
<?php if ($editable): ?>
    &nbsp;<?php
    echo link_to('Add New Deadline',
                 'subreddit/add_deadline?id=' . $subreddit->getId());
    ?>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>Author Type</th>
            <th>Sign-up Before Previous Deadline?</th>
            <th>Deadline before Release</th>
        </tr>
    </thead>
    <tbody>
<?php foreach ($deadlines as $deadline): ?>
            <tr>
                <td><?php echo ucwords(str_replace('_', ' ', $deadline->getAuthorType()->getType())); ?></td>
                <td><?php
                echo ((bool) $deadline->getRestrictedUntilPreviousMissesDeadline()
                            ? 'No' : 'Yes');

    ?></td>
                <td>
                    <?php $display = $deadline_display[$deadline->getIncremented()]; ?>
                    <?php if ($editable): ?>
                        <?php
                        echo link_to($display,
                                     'subreddit/edit_deadline?id=' . $deadline->getId());

                        ?>
            <?php else: ?>
                <?php echo $display ?>
    <?php endif; ?>
                </td>
            </tr>
<?php endforeach; ?>
    </tbody>
</table>