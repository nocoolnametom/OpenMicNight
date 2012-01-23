<?php slot('atom_feed') ?>
<link href="<?php echo url_for('@feed_subreddit_atom?domain=' . $subreddit->getDomain()) ?>" type="application/atom+xml" rel="alternate" title="<?php $subreddit->getName() ?> Atom" />
<link href="<?php echo url_for('@feed_subreddit_rss?domain=' . $subreddit->getDomain()) ?>" type="application/rss+xml" rel="alternate" title="<?php $subreddit->getName() ?> RSS" />
<?php end_slot() ?><?php
$graphic_file_web_location = '/'
        . trim(str_replace(sfConfig::get('sf_web_dir'), '',
                                         ProjectConfiguration::getEpisodeGraphicFileLocalDirectory()),
                                         '/') . '/';

?>
<h4 id="subreddit_name"><?php
echo link_to($subreddit->getName(),
             '@subreddit_index?module=index&domain=' . $subreddit->getDomain())

?></h4>

<div id="episode_content">
    <?php if ($sf_user->getApiUserId() == $assignment->getSfGuardUserId()): ?>
        <div id="edit_link">
            <?php
            echo link_to('Edit Episode', 'episode/edit?id=' . $episode->getId());

            ?>
        </div>
    <?php endif; ?>
    <div id="release_date">
        <?php
        echo date('Y-m-d g:ia', strtotime($episode->getReleaseDate()));
        ?>, by <?php echo $user->getUsername(); ?>
    </div>
    <div id="episode_title">
        <h2>
            <?php echo $episode->getTitle(); ?>
            <?php if ($episode->getIsNsfw()): ?>
                <span class="nsfw"> (nsfw)</span>
            <?php endif; ?>
        </h2>
    </div>
    <div id="content_columns">
        <div id="media_column">
            <table>
                <?php if ($episode->getGraphicFile()): ?>
                <tr>
                    <td>
                        <?php echo image_tag($graphic_file_web_location . $episode->getGraphicFile()); ?>
                    </td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td class="episode_audio_player">
                        <?php
                        include_partial('episode/html5_audio_player',
                                        array(
                            'episode' => $episode,
                            'class' => 'full',
                        ));

                        ?>
                    </td>
                </tr>
            </table>
        </div>
        <div id="episode_description">
            <?php echo html_entity_decode($sf_user->formatMarkdown($episode->getDescription())); ?>
        </div>
    </div>
    <div id="reddit_post"><?php echo '';//link_to('View this on Reddit', 'http://www.reddit.com/'); ?></div>
</div>