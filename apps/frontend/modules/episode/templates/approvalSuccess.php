<?php $graphic_file_web_location = '/' . trim(str_replace(sfConfig::get('sf_web_dir'), '', ProjectConfiguration::getEpisodeGraphicFileLocalDirectory()), '/') . '/'; ?>
<h2 class="orangeredbar"><?php echo link_to($subreddit->getName(), '@subreddit_index?module=index&domain=' . $subreddit->getDomain()) ?></h2>
<?php $submitted_at = date("g:ia, D j M Y", strtotime($episode->getSubmittedAt())); ?>
<?php $approved_at = date("g:ia, D j M Y", strtotime($episode->getApprovedAt())); ?>
<?php echo "<div id=\"episode_edit_status\">Submitted For Approval @ $submitted_at</div>"; ?>
<?php echo '<div id="episode_edit_deadline">Deadline for Approval: ' . date("g:ia, D j M Y", $deadline) . '</div>'; ?>
<div id="approver_message">This episode is waiting for your approval.  Please
     make sure that it conforms to any expected rules for the Subreddit and
     that it also obeys copyright laws through the correct attribution of any
     Creative Commons materials and that otherwise the submitter is the creator
     or owns the rights to all materials within.  Fair use of copyrighted
     materials is a legally tricky thing, so try to avoid depending on it.  If
     you are not going to approve it, please get contact the submitter and
     explain why.  If the episode is not approved before the submitter's
     deadline the episode will be assigned to any waiting users.</div>
<table id="episode_edit">
    <tfoot>
        <tr>
            <td>
                <a href="<?php echo url_for('profile/episodes') ?>">Back to Episodes</a>
                &nbsp;<?php echo button_to('Approve Episode', 'episode/approve?id=' . $episode->getId(), array('confirm' => 'Are you sure?  There is no going back!')) ?>
            </td>
            <td class="right">
                <?php echo link_to('Contact Submitter', 'message/send?id=' . $episode->getSfGuardUserId()) ?>
            </td>
        </tr>
    </tfoot>
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
                <?php include_partial('episode/html5_audio_player', array(
                    'episode' => $episode,
                    'class' => 'not_full',
                )); ?>
            </td>
        </tr>
        <tr>
            <td colspan="2"><?php echo html_entity_decode($sf_user->formatMarkdown($episode->getDescription())); ?></td>
        </tr>
    </tbody>
</table>
