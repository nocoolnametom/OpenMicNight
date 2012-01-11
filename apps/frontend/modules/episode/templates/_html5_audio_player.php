<?php $audio_src = !$episode->getFileIsRemote() ?
        ( $episode->getAudioFile() ? 
            url_for('@episode_audio?id=' . $episode->getId() . '&format=' . substr($episode->getAudioFile(), -3, 3), true)
            : '' )
        : $episode->getRemoteUrl() ; ?>
<div id="episode_audio_div" <?php echo ($width ? 'style="width: ' . $width . ';"' : '') ?> >
    <audio id="episode_audio" src="<?php echo $audio_src; ?>" controls  preload="metadata" <?php echo ($width ? 'style="width: ' . $width . ';"' : '') ?>></audio>
    <div style="font-size:xx-small; text-align: center;">
        <a id="episode_audio_link" href="<?php echo $audio_src; ?>"><?php echo $episode->getNiceFilename(); ?></a>
    </div>
</div>
<script type="text/javascript">
    var audioTag = document.createElement('audio');
    if (!(!!(audioTag.canPlayType) && ("no" != audioTag.canPlayType("audio/mpeg")) && ("" != audioTag.canPlayType("audio/mpeg")))) {
        AudioPlayer.embed("episode_audio", {width:"100%",transparentpagebg: "yes",soundFile:"<?php echo $audio_src; ?>"});
    }
</script>