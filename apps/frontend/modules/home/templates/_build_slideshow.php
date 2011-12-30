<?php if (!count($episodes)): ?>
    <?php echo image_tag('gradient.jpg'); ?>
<?php else: ?>
    <?php foreach ($episodes as $episode): ?>
        <a href="<?php echo url_for('@episode_show?id=' . $episode->getIncremented()) ?>" target="_blank">
            <?php
            $image = ($episode->getGraphicFile() && !$episode->getIsNsfw() ? '/'
                            . trim(str_replace(sfConfig::get('sf_web_dir'), '',
                                                             ProjectConfiguration::getEpisodeGraphicFileLocalDirectory()),
                                                             '/') . '/'
                            . $episode->getGraphicFile() : 'gradient.jpg');

            ?>
            <?php echo image_tag($image) ?>
            <span>
                <strong>
                    /r/<?php echo $subreddits[$episode->getSubredditId()]->getDomain() ?>
        <?php
        echo ($episode->getIsNsfw() ? '<span style="color:red;">nsfw</span>' : '');

        ?>
                </strong>
                    <?php
                    echo ($episode->getDescription() ? '<br/>' . strip_tags(html_entity_decode($sf_user->formatMarkdown($episode->getDescription())))
                                : '');

                    ?>
            </span>
        </a>
            <?php endforeach; ?>
<?php endif; ?>