<div id="footer" style="width:100%; max-width: 980px; text-align: center; margin: 0 auto; margin-top: 5px; font-size: smaller;">
    (c) <?php echo date('Y') ?> Tom Doggett - 
    <ul style="display: inline; margin: 0; padding: 0;">
        <li style="display: inline;"><?php echo link_to('About Us', '@about_us') ?></li>
        <li style="display: inline;">| <?php echo link_to('API', '@api') ?></li>
        <li style="display: inline;">| <?php echo link_to('How to Help', '@how_to_help') ?></li>
        <li style="display: inline;">| <?php echo link_to('How to Use ' . ProjectConfiguration::getApplicationName(), '@how_to_use') ?></li>
        <li style="display: inline;">| <?php include_partial('global/feedback_link', array('feedback_text' => 'Offer Feedback')) ?></li>
        <li style="display: inline;">| <?php echo link_to('Roadmap', '@roadmap') ?></li>
        <li style="display: inline;">| <?php echo link_to('Blog', '@blog') ?></li>
    </ul>
</div>
