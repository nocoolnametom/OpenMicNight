<?php if (!count($episodes)): ?>
    <img src='http://openmicnight/images/gradient.jpg' />
<?php else: ?>
    <?php foreach ($episodes as $episode): ?>
        <a href="<?php echo url_for('@episode_show?id=' . $episode->getIncremented()) ?>" target="_blank">
            <img src='http://openmicnight/uploads/graphics/12d3c9a4ad810a2b3cd350191cbe467c3735193b.jpg' />
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