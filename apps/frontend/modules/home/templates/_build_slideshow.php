<?php if (!count($episodes)): ?>
    <?php echo image_tag('gradient.jpg'); ?>
<?php else: ?>
    <?php foreach ($episodes as $episode): ?>
        <a href="<?php echo url_for('@episode_show?id=' . $episode->getIncremented()) ?>" target="_blank">
            <?php
            $image = ($episode->getGraphicFile() && !$episode->getIsNsfw() ? $episode->getGraphicUrl() : 'gradient.jpg');

            ?>
            <?php echo image_tag($image) ?>
            <span>
                <strong>
                    /r/<?php echo $subreddits[$episode->getSubredditId()]->getDomain() ?>
        <?php
        echo ($episode->getIsNsfw() ? '<span class="nsfw">nsfw</span>' : '');

        ?>
                </strong>
                    <?php
                    echo ($episode->getTitle() ? '<br/>' . strip_tags($episode->getTitle())
                                : '');

                    ?>
            </span>
        </a>
            <?php endforeach; ?>
<?php endif; ?>