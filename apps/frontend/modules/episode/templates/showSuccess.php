<h3><?php echo link_to($subreddit->getName(), '@subreddit_index?module=index&domain=' . $subreddit->getDomain()) ?></h3>
<div id="release_date"><?php echo date('Y-m-d g:ia', strtotime($episode->getReleaseDate())); ?></div>
<?php if ($sf_user->getApiUserId() == $episode->getSfGuardUserId()): ?>
    <?php echo link_to('Edit Episode', 'episode/edit?id=' . $episode->getId()); ?>
<?php endif; ?>
<table>
    <?php if ($episode->getRedditPostUrl()): ?>
        <tfoot>
            <tr>
                <th colspan="2"><?php echo link_to("View on Reddit", $episode->getRedditPostUrl()); ?></th>
            </tr>
        </tfoot>
    <?php endif; ?>
    <thead>
        <?php if ($episode->getGraphicFile()): ?>
            <tr>
                <th colspan="2" class="graphic"><?php echo image_tag('/uploads/graphics/' . $episode->getGraphicFile()); ?></th>
            </tr>
        <?php endif; ?>
        <tr>
            <th colspan="2">
                <?php echo $episode->getTitle() ?>
                <?php if ($episode->getIsNsfw()): ?>
                    <span class="nsfw">nsfw</span>
                <?php endif; ?>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="2" class="audio">
                <?php if (strtotime($episode->getReleaseDate()) < time()): ?>
                    <audio id="episode_audio" src="<?php echo ($episode->getAudioFile() ? url_for('@episode_audio?id=' . $episode->getId() . '&format=' . substr($episode->getAudioFile(), -3, 3), true) : '') ?>" controls></audio>
                    <div style="font-size:xx-small;">
                        <a id="episode_audio_link" href="<?php echo ($episode->getAudioFile() ? url_for('@episode_audio?id=' . $episode->getId() . '&format=' . substr($episode->getAudioFile(), -3, 3), true) : '') ?>"><?php echo $episode->getNiceFilename(); ?></a>
                    </div>
                <?php else: ?>
                    <audio id="episode_audio" src="<?php echo $episode->getRemoteUrl(); ?>" controls></audio>
                    <div style="font-size:xx-small;">
                        <a id="episode_audio_link" href="<?php echo $episode->getRemoteUrl(); ?>"><?php echo $episode->getNiceFilename(); ?></a>
                    </div>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td colspan="2"><?php echo html_entity_decode($sf_user->formatMarkdown($episode->getDescription())); ?></td>
        </tr>
    </tbody>
</table>