<div id="footer">
    (c) <?php echo date('Y') ?> Tom Doggett - 
    <ul>
        <li><?php echo link_to('About Us', '@about_us') ?></li>
        <li>| <?php echo link_to('API', '@api') ?></li>
        <li>| <?php echo link_to('How to Help', '@how_to_help') ?></li>
        <li>| <?php echo link_to('How to Use ' . ProjectConfiguration::getApplicationName(), '@how_to_use') ?></li>
        <li>| <?php include_partial('global/feedback_link', array('feedback_text' => 'Offer Feedback')) ?></li>
        <li>| <?php echo link_to('Roadmap', '@roadmap') ?></li>
        <li>| <?php echo link_to('Blog', 'http://herddit.blogspot.com/') ?></li>
    </ul>
</div>
