<div id="episode_<?php echo $type ?>_div" class="<?php echo $class ?>" >
    <audio id="subreddit_<?php echo $type ?>" src="<?php echo $audio_src; ?>" controls  preload="metadata" class="<?php echo $class ?>"></audio>
    <div id="<?php echo $type ?>_link">
        <a id="subreddit<?php echo $type ?>_link" href="<?php echo $audio_src; ?>"><?php echo $filename; ?></a>
    </div>
</div>
<script type="text/javascript">
    var audioTag = document.createElement('audio');
    if (!(!!(audioTag.canPlayType) && ("no" != audioTag.canPlayType("audio/mpeg")) && ("" != audioTag.canPlayType("audio/mpeg")))) {
        AudioPlayer.embed("subreddit_<?php echo $type ?>", {width:"100%",transparentpagebg: "yes",soundFile:"<?php echo $audio_src; ?>"});
    }
</script>