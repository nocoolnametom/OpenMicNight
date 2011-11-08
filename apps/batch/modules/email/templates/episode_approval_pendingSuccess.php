<?php /* @var $user sfGuardUser */ /* @var $episode Episode */  ?>
<p> Dear <?php echo $name ?>,</p>

<p>There is an episode that has been submitted for approval in the
<?php $episode->getSubreddit()->getName() ?> subreddit.  Please take some time
to listen to this episode and determine if you feel you can approve it. 
Remember that all episodes must be submitted by users who own or adhere to rules
governing the rights to all content contained within.  Creative Commons content
should be correctly attributed, and it's safer to avoid relying on "fair use"
defenses by avoiding the use of copyrighted materials altogether, though it's up
to you as an approver to decide that.  Remember that it your approval of this
episode means that it complies with the aims of your subreddit as well as
copyright laws.</p>

<p>Please take the time needed to approve this episode or to send it back to the
    submitter for further work.  Be honest and be fair.   There's a lot of
    responsibility resting upon you, but you know that you're up to it; you
    wouldn't be a moderator otherwise.  Have fun, and help someone else give
    their voice to the world!</p>

<p>Sincerely,<br/> The <?php echo $app_name; ?> Team</p>