<h1>Subreddit Membership</h1>

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
