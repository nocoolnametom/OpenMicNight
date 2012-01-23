<h2 class="orangeredbar">Subreddit Membership</h2>
<?php echo link_to('Back to Subreddit', 'subreddit/show?domain=' . $subreddit->getDomain()) ?>

<table>
    <thead>
        <tr>
            <th>Username</th>
            <th>Membership</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($members as $member): ?>
            <tr>
                <td><?php echo $member->getsfGuardUser()->getUsername(); ?></td>
                <td>
                    <?php
                    echo link_to($member->getMembership()->getDescription(),
                                 'subreddit/membership?id='
                            . $member->getIncremented());

                    ?>
                </td>
            </tr>
<?php endforeach; ?>
    </tbody>
</table>
