<div id="slideshow" style="border:1px solid black; width: 100%; max-width: 570px; height: 230px;">
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