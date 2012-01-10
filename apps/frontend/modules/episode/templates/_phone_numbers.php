<div id="phone_number_info">You can record your Episode using your phone in this subreddit!  Dial any of the following numbers:
    <ul>
        <?php foreach ($phone_numbers as $number): ?>
            <li><?php echo $number->getNumber() ?></li>
        <?php endforeach; ?>
    </ul>
    And enter the following ID hash that will link your recording to this episode:
    <blockquote><?php echo $form->getObject()->getEpisodeAssignment()->getIdHash() ?></blockquote>
</div>