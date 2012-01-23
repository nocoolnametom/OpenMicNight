<div id="slideshow">
    <?php
    include_partial('build_slideshow',
                    array(
        'episodes' => $episodes,
        'subreddits' => $subreddits,
    ));

    ?>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $('#slideshow').coinslider({spw: 5, sph: 3, width: 570, height: 230, opacity: 0.9, hoverPause: true, navigation: false, delay: 5000 });
    });
</script>