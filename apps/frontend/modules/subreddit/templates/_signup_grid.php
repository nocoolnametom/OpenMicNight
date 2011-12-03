<?php /* @var $subreddit Subreddit */ ?>
<?php
$assignments = $sf_data->getRaw('assignments');
$assigned_episodes = $sf_data->getRaw('assigned_episodes');
$assigned_author_types = $sf_data->getRaw('assigned_author_types');
?>
<table class="subreddit_grid">
    <thead>
        <tr>
            <td>Date</td>
            <?php $columns = 0; ?>
            <?php foreach ($deadlines as $deadline): ?>
                <?php /* @var $deadline Deadline */ ?>
                <td><?php
            $authortype = $authortypes[$deadline->getAuthorTypeId()];
            /* @var $authortype AuthorType */
            echo ucwords(str_replace('_', ' ', $authortype->getType()));
            $columns++;

                ?></td>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($episodes as $episode): ?>
            <?php /* @var $episode Episode */ ?>
            <tr>
                <td><?php
        $release_date = strtotime($episode->getReleaseDate());
        echo date('g:ia D j M, Y (T)', $release_date)

            ?></td>
                <?php $columns = 0; ?>
                <?php foreach ($deadlines as $deadline): ?>
                    <?php /* @var $deadline Deadline */ ?>
                    <?php
                    // Check to see if registration is even possible, then check to see if registration has occured.
                    if ($release_date < time() + $deadline->getSeconds()) {
                        echo '<td class="unregisterable">&nbsp;';
                    } else {
                        // Registration is possible, has it occured?
                        if (array_key_exists($deadline->getAuthorTypeId(),
                                             $assignments[$episode->getIncremented()])) {
                            $assignment = $assignments[$episode->getIncremented()][$deadline->getAuthorTypeId()];
                        } else {
                            $assignment = null;
                        }
                        if (!is_null($assignment)) {
                            if ($assignment->getSfGuardUserId() == $sf_user->getApiUserId())
                                echo '<td class="self">You';
                            else
                                echo "<td class=\"registered\">Registered";
                        } else {
                            if (in_array($episode->getIncremented(),
                                         $assigned_episodes) || in_array($deadline->getAuthorTypeId(),
                                                                                            $assigned_author_types))
                                echo '<td class="unregisterable">&nbsp;';
                            else if ($sf_user->isAuthenticated())
                                echo "<td>" . link_to('Register',
                                                      'episode/assign?episode_id=' . $episode->getIncremented() . '&author_type_id=' . $deadline->getAuthorTypeId(),
                                                      array(
                                    'confirm' => "Are you sure? You cannot unregister, and the dealine to submit this Episode is by"
                                    . date('g:ia D j M, Y (T)',
                                           strtotime($episode->getReleaseDate()) - $deadline->getSeconds()) . ".",
                                ));
                            else
                                echo "<td>Open";
                        }
                    }

                    ?>
                    </td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>